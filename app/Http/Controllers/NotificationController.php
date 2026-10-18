<?php

namespace App\Http\Controllers;

use App\NotificationCategory;
use App\UserRole;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Kotak notifikasi milik pengguna yang sedang login (semua role).
 *
 * Setiap aksi dibatasi pada notifikasi milik pengguna sendiri melalui relasi `notifications()`.
 */
class NotificationController extends Controller
{
    /**
     * Jumlah notifikasi per halaman.
     */
    private const PER_PAGE = 15;

    /**
     * Daftar notifikasi dengan filter kategori, status baca, dan tanggal.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'kategori' => ['nullable', Rule::enum(NotificationCategory::class)],
            'status' => ['nullable', Rule::in(['belum', 'sudah'])],
            'dari' => ['nullable', 'date_format:Y-m-d'],
            'sampai' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:dari'],
        ], [
            'sampai.after_or_equal' => 'Tanggal akhir tidak boleh lebih awal daripada tanggal awal.',
        ]);

        $user = $request->user();

        $notifications = $user->notifications()
            ->when($filters['kategori'] ?? null, fn ($query, string $category) => $query->where('data->category', $category))
            ->when(($filters['status'] ?? null) === 'belum', fn ($query) => $query->whereNull('read_at'))
            ->when(($filters['status'] ?? null) === 'sudah', fn ($query) => $query->whereNotNull('read_at'))
            ->when($filters['dari'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['sampai'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '<=', $date))
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('notifications.index', [
            'layout' => $this->layoutFor($request),
            'notifications' => $notifications,
            'filters' => $filters,
            'categories' => NotificationCategory::cases(),
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }

    /**
     * Menandai notifikasi sudah dibaca lalu membuka tautan tujuannya (hanya tautan internal aplikasi).
     */
    public function open(Request $request, string $notification): RedirectResponse
    {
        $record = $request->user()->notifications()->findOrFail($notification);
        $record->markAsRead();

        $url = $record->data['url'] ?? null;

        if (is_string($url) && str_starts_with($url, url('/'))) {
            return redirect()->to($url);
        }

        return redirect()->route('notifications.index');
    }

    /**
     * Menandai satu notifikasi sudah dibaca.
     */
    public function markAsRead(Request $request, string $notification): RedirectResponse
    {
        $request->user()->notifications()->findOrFail($notification)->markAsRead();

        return back()->with('success', 'Notifikasi ditandai sudah dibaca.');
    }

    /**
     * Menandai seluruh notifikasi sudah dibaca.
     */
    public function markAllAsRead(Request $request): RedirectResponse
    {
        $count = $request->user()->unreadNotifications()->count();
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', "{$count} notifikasi ditandai sudah dibaca.");
    }

    /**
     * Menghapus notifikasi milik sendiri yang sudah dibaca (notifikasi belum dibaca tidak dihapus).
     */
    public function destroy(Request $request, string $notification): RedirectResponse
    {
        $record = $request->user()->notifications()->findOrFail($notification);

        if ($record->read_at === null) {
            return back()->with('error', 'Tandai notifikasi sebagai sudah dibaca sebelum menghapusnya.');
        }

        $record->delete();

        return back()->with('success', 'Notifikasi berhasil dihapus.');
    }

    /**
     * Layout sesuai role: panel admin untuk admin, portal untuk guru/murid.
     */
    private function layoutFor(Request $request): string
    {
        return $request->user()->hasRole(UserRole::Admin) ? 'layouts.admin' : 'layouts.portal';
    }
}
