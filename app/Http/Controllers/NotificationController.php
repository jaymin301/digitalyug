<?php

namespace App\Http\Controllers;

use App\Models\PanelNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->panelNotifications()->latest()->paginate(20);
        return view('notifications.index', compact('notifications'));
    }

    public function unreadCount()
    {
        return response()->json(['unread' => auth()->user()->unreadNotificationsCount()]);
    }

    public function recent()
    {
        $notifications = auth()->user()->panelNotifications()->latest()->limit(5)->get()->map(function ($n) {
            return [
            'id' => $n->id,
            'type' => $n->type,
            'title' => $n->title,
            'message' => $n->message,
            'link' => $n->link,
            'is_read' => $n->is_read,
            'time' => $n->created_at->diffForHumans(),
            'triggered_by' => $n->triggeredBy->name ?? 'System'
            ];
        });

        return response()->json([
            'notifications' => $notifications,
            'unread' => auth()->user()->unreadNotificationsCount()
        ]);
    }

    public function markRead($id)
    {
        $notification = auth()->user()->panelNotifications()->findOrFail($id);
        $notification->update(['is_read' => true]);
        return response()->json(['success' => true]);
    }

    public function markAllRead()
    {
        auth()->user()->panelNotifications()->where('is_read', false)->update(['is_read' => true]);
        return response()->json(['success' => true, 'message' => 'All marked as read']);
    }
}
