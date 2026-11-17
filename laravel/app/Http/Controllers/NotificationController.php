<?php

namespace App\Http\Controllers;

use App\Models\CitizenNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Admin-panel notification feed (the bell).
 *
 * The feed is the shared `citizen_notifications` table, shown READ-ONLY.
 * That table is also every citizen's own in-app inbox, so staff actions must
 * never write to it: the old "mark all read" flipped `is_read` for every
 * citizen, and "clear" deleted every citizen's notifications.
 *
 * Read state for staff is per user: `users.notifications_read_at` is the
 * "read up to" moment. A notification is unread for a staff member when it
 * was created after that moment. The `is_read` field in the JSON is that
 * per-staff value, not the citizen's flag.
 *
 * Consumers: public/js/app.js (badge, dropdown, "mark all read") and the
 * 30s unread-count poll (app.js, dashboard.html).
 */
class NotificationController extends Controller
{
    // ──────────────────────────────────────────────────────────
    // GET /api/notifications   (?unread_only=1)
    // ──────────────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $readAt = $request->user()->notifications_read_at;

        $query = CitizenNotification::orderByDesc('created_at');
        if ($request->boolean('unread_only')) {
            $this->scopeUnread($query, $readAt);
        }

        $notifications = $query->limit(50)->get()->map(function ($n) use ($readAt) {
            $row = $n->toArray();
            $row['is_read'] = $readAt !== null && $n->created_at !== null && $n->created_at->lte($readAt);

            return $row;
        })->values();

        return response()->json([
            'success' => true,
            'data'    => [
                'notifications' => $notifications,
                'unread_count'  => $this->unreadFor($readAt),
            ],
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // POST /api/notifications/read-all
    // Marks the feed read for THIS staff user only.
    // ──────────────────────────────────────────────────────────
    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->forceFill(['notifications_read_at' => now()])->save();

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read.',
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // GET /api/notifications/unread-count — 30s badge poll
    // ──────────────────────────────────────────────────────────
    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => ['unread_count' => $this->unreadFor($request->user()->notifications_read_at)],
        ]);
    }

    private function unreadFor($readAt): int
    {
        $query = CitizenNotification::query();
        $this->scopeUnread($query, $readAt);

        return $query->count();
    }

    private function scopeUnread($query, $readAt): void
    {
        if ($readAt !== null) {
            $query->where('created_at', '>', $readAt);
        }
    }
}
