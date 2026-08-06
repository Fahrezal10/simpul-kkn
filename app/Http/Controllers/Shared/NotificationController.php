<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * SYS-01 — Halaman semua notifikasi user login (in-app).
     */
    public function index(): View
    {
        $notifications = Auth::user()
            ->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('shared.notifications.index', ['notifications' => $notifications]);
    }

    /**
     * Tandai satu notifikasi sebagai dibaca.
     */
    public function markAsRead(string $id): RedirectResponse
    {
        $notification = Auth::user()->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();
        }

        return back();
    }

    /**
     * SYS-01 — Tandai satu notifikasi dibaca via AJAX (dropdown popup).
     * Mengembalikan sisa jumlah belum dibaca agar badge ter-update.
     */
    public function markAsReadAjax(string $id): JsonResponse
    {
        $notification = Auth::user()->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json([
            'ok'          => true,
            'unreadCount' => Auth::user()->unreadNotifications->count(),
        ]);
    }

    /**
     * Tandai seluruh notifikasi sebagai dibaca.
     */
    public function markAllAsRead(): RedirectResponse
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Semua notifikasi telah ditandai dibaca.');
    }
}