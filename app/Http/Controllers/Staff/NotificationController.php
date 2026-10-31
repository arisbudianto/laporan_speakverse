<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Saran perbaikan Modul 6 — notifikasi otomatis ke guru.
 *
 * Murni menampilkan & menandai-baca notifikasi database bawaan
 * Laravel (lihat App\Notifications\StudentNeedsInterventionNotification
 * dan command learning:notify-teachers yang mengirimnya).
 */
class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = Auth::user()
            ->notifications()
            ->paginate(20);

        return view('staff.notifications.index', [
            'notifications' => $notifications,
        ]);
    }

    public function markRead(DatabaseNotification $notification): RedirectResponse
    {
        abort_unless($notification->notifiable_id === Auth::id(), 403);

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        return back();
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }
}
