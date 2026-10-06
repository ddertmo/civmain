<?php

namespace App\Http\Controllers;

use App\Models\CitizenNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Admin-panel notification feed.
 *
 * Backed by `citizen_notifications` (the shared organisation feed), not the
 * legacy `notifications` table, which no longer exists. The panel shows the
 * whole feed, so there is deliberately no per-user scoping here.
 *
 * Consumers: public/js/app.js (badge + dropdown) and the 30s unread-count poll.
 * Expected shape: {success, data:{notifications, unread_count}} and
 *                 {success, data:{unread_count}}.
 */
class NotificationController extends Controller
{
    // ──────────────────────────────────────────────────────────
    // GET /api/notifications
    // Returns the organisation-wide notification feed
    // ?unread_only=1  → filter to unread only
    // ──────────────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $query = CitizenNotification::orderByDesc('created_at');

        if ($request->boolean('unread_only')) {
            $query->where('is_read', false);
        }

        $notifications = $query->limit(50)->get();
        $unreadCount   = CitizenNotification::where('is_read', false)->count();

        return response()->json([
            'success' => true,
            'data'    => [
                'notifications' => $notifications,
                'unread_count'  => $unreadCount,
            ],
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // POST /api/notifications/{id}/read
    // Mark a single notification as read
    // ──────────────────────────────────────────────────────────
    public function markRead(Request $request, int $id): JsonResponse
    {
        $notification = CitizenNotification::findOrFail($id);

        $notification->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // POST /api/notifications/read-all
    // Mark ALL notifications as read
    // ──────────────────────────────────────────────────────────
    public function markAllRead(Request $request): JsonResponse
    {
        CitizenNotification::where('is_read', false)->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read.',
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // DELETE /api/notifications/{id}
    // Delete a single notification
    // ──────────────────────────────────────────────────────────
    public function destroy(Request $request, int $id): JsonResponse
    {
        CitizenNotification::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted.',
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // DELETE /api/notifications
    // Clear all notifications
    // ──────────────────────────────────────────────────────────
    public function destroyAll(Request $request): JsonResponse
    {
        CitizenNotification::query()->delete();

        return response()->json([
            'success' => true,
            'message' => 'All notifications cleared.',
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // GET /api/notifications/unread-count
    // Lightweight poll endpoint — returns just the badge count
    // The frontend calls this every 30s to update the bell badge
    // ──────────────────────────────────────────────────────────
    public function unreadCount(Request $request): JsonResponse
    {
        $count = CitizenNotification::where('is_read', false)->count();

        return response()->json([
            'success' => true,
            'data'    => ['unread_count' => $count],
        ]);
    }
}
