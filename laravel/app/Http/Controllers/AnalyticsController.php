<?php

namespace App\Http\Controllers;

use App\Models\CitizenReport;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Analytics for the admin/office dashboards.
 *
 * Every endpoint is scoped to the caller (super admin → all reports; CEO /
 * CENRO → their office only, via CitizenReport::scopeVisibleTo) and computed
 * with grouped queries: one query per chart instead of one COUNT per bucket
 * (the old summary ran 8 queries, the trends 5-6, and full() ~24 plus a JSON
 * encode/decode of every sub-response). Response shapes are unchanged, except
 * that status_distribution now includes 'Approved' (it was silently missing).
 */
class AnalyticsController extends Controller
{
    // GET /api/analytics/summary
    public function summary(Request $request): JsonResponse
    {
        return $this->ok($this->summaryData($request));
    }

    // GET /api/analytics/status-distribution
    public function statusDistribution(Request $request): JsonResponse
    {
        return $this->ok($this->statusData($request));
    }

    // GET /api/analytics/by-category
    public function byCategory(Request $request): JsonResponse
    {
        return $this->ok($this->categoryData($request));
    }

    // GET /api/analytics/top-issues
    public function topIssues(Request $request): JsonResponse
    {
        return $this->ok($this->topData($request, 'concern'));
    }

    // GET /api/analytics/top-barangays
    public function topBarangays(Request $request): JsonResponse
    {
        return $this->ok($this->topData($request, 'barangay'));
    }

    // GET /api/analytics/weekly-trend  (last 5 weeks)
    public function weeklyTrend(Request $request): JsonResponse
    {
        return $this->ok($this->weeklyData($request));
    }

    // GET /api/analytics/monthly-trend (last 6 months)
    public function monthlyTrend(Request $request): JsonResponse
    {
        return $this->ok($this->monthlyData($request));
    }

    // GET /api/analytics — all chart data in one request (Analytics pages)
    public function full(Request $request): JsonResponse
    {
        return $this->ok([
            'summary'             => $this->summaryData($request),
            'status_distribution' => $this->statusData($request),
            'by_category'         => $this->categoryData($request),
            'top_issues'          => $this->topData($request, 'concern'),
            'top_barangays'       => $this->topData($request, 'barangay'),
            'weekly_trend'        => $this->weeklyData($request),
            'monthly_trend'       => $this->monthlyData($request),
        ]);
    }

    // ── Builders ────────────────────────────────────────────────────────

    private function summaryData(Request $request): array
    {
        $c = CitizenReport::statusCounts($request->user());

        return [
            'total_reports'      => $c['total'],
            // Kept as before: "pending" on the analytics cards = waiting for
            // the admin (Submitted + Pending Validation).
            'pending_validation' => $c['pending_validation'] + $c['submitted'],
            'approved'           => $c['approved'],
            'assigned'           => $c['assigned'],
            'in_progress'        => $c['in_progress'],
            'resolved'           => $c['resolved'],
            'rejected'           => $c['rejected'],
        ];
    }

    private function statusData(Request $request): array
    {
        $rows = $this->scoped($request)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $labels = CitizenReport::STATUS_ORDER; // includes Approved
        $data   = array_map(fn ($s) => (int) ($rows[$s] ?? 0), $labels);

        return ['labels' => $labels, 'data' => $data, 'percentages' => $this->percentages($data)];
    }

    private function categoryData(Request $request): array
    {
        $rows = $this->scoped($request)
            ->select('category', DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->pluck('count', 'category');

        $labels = [];
        $data   = [];
        foreach (['Infrastructure', 'Environment', 'Others'] as $c) {
            $n = (int) ($rows[$c] ?? 0);
            if ($n > 0) {
                $labels[] = $c;
                $data[]   = $n;
            }
        }

        return ['labels' => $labels, 'data' => $data, 'percentages' => $this->percentages($data)];
    }

    private function topData(Request $request, string $column): array
    {
        $rows = $this->scoped($request)
            ->select($column, DB::raw('COUNT(*) as count'))
            ->whereNotNull($column)
            ->groupBy($column)
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return [
            'labels' => $rows->pluck($column)->toArray(),
            'data'   => $rows->pluck('count')->map(fn ($n) => (int) $n)->toArray(),
        ];
    }

    private function weeklyData(Request $request): array
    {
        $weeks   = 5;
        $buckets = [];
        for ($i = $weeks - 1; $i >= 0; $i--) {
            $start = now()->startOfWeek()->subWeeks($i);
            $buckets[] = [$start, (clone $start)->endOfWeek()];
        }

        $counts = $this->bucketCounts($request, $buckets);

        return [
            'labels' => array_map(fn ($b) => $b[0]->format('M d') . '–' . $b[1]->format('M d'), $buckets),
            'data'   => $counts,
        ];
    }

    private function monthlyData(Request $request): array
    {
        $months  = 6;
        $buckets = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $start = now()->startOfMonth()->subMonths($i);
            $buckets[] = [$start, (clone $start)->endOfMonth()];
        }

        $counts = $this->bucketCounts($request, $buckets);

        return [
            'labels' => array_map(fn ($b) => $b[0]->format('M Y'), $buckets),
            'data'   => $counts,
        ];
    }

    /**
     * One range query (index-friendly: created_at BETWEEN, no YEAR()/MONTH()
     * wrappers), then counts per [start, end] bucket in PHP. Only the
     * created_at column of the window is fetched, so it stays light.
     *
     * @param  array<int, array{0: CarbonInterface, 1: CarbonInterface}>  $buckets
     * @return int[]
     */
    private function bucketCounts(Request $request, array $buckets): array
    {
        $from = $buckets[0][0];
        $to   = $buckets[count($buckets) - 1][1];

        $stamps = $this->scoped($request)
            ->whereBetween('created_at', [$from, $to])
            ->pluck('created_at');

        $counts = array_fill(0, count($buckets), 0);
        foreach ($stamps as $ts) {
            foreach ($buckets as $i => [$start, $end]) {
                if ($ts >= $start && $ts <= $end) {
                    $counts[$i]++;
                    break;
                }
            }
        }

        return $counts;
    }

    private function percentages(array $data): array
    {
        $total = array_sum($data);

        return array_map(fn ($n) => $total > 0 ? round(($n / $total) * 100, 1) . '%' : '0%', $data);
    }

    private function scoped(Request $request): Builder
    {
        return CitizenReport::query()->visibleTo($request->user());
    }

    private function ok(array $data): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $data]);
    }
}
