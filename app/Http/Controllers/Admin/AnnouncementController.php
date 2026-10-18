<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\User;
use App\Notifications\Announcement;
use App\Notifications\NotificationSender;
use App\UserRole;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Pengumuman admin yang dikirim sebagai notifikasi ke kelompok penerima.
 */
class AnnouncementController extends Controller
{
    /**
     * Pilihan kelompok penerima.
     *
     * @var array<string, string>
     */
    public const AUDIENCES = [
        'semua' => 'Semua pengguna',
        'murid' => 'Semua murid',
        'guru' => 'Semua guru',
        'admin' => 'Semua admin',
        'kelas' => 'Peserta & guru kelas tertentu',
    ];

    /**
     * Form pengumuman.
     */
    public function create(): View
    {
        return view('admin.announcements.create', [
            'audiences' => self::AUDIENCES,
            'classrooms' => Classroom::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Mengirim pengumuman ke penerima yang dipilih (satu notifikasi per penerima).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:2000'],
            'audience' => ['required', Rule::in(array_keys(self::AUDIENCES))],
            'classroom_id' => ['nullable', 'required_if:audience,kelas', 'integer', Rule::exists('classes', 'id')],
        ], [
            'title.required' => 'Judul pengumuman wajib diisi.',
            'message.required' => 'Isi pengumuman wajib diisi.',
            'audience.required' => 'Silakan pilih penerima.',
            'classroom_id.required_if' => 'Silakan pilih kelas penerima.',
        ]);

        $recipients = $this->recipients($validated['audience'], isset($validated['classroom_id']) ? (int) $validated['classroom_id'] : null);

        $sent = NotificationSender::send(
            $recipients,
            new Announcement((string) Str::uuid(), $validated['title'], $validated['message']),
        );

        return redirect()
            ->route('notifications.index')
            ->with('success', "Pengumuman \"{$validated['title']}\" dikirim ke {$sent} penerima.");
    }

    /**
     * Penerima pengumuman sesuai kelompok.
     *
     * @return Collection<int, User>
     */
    private function recipients(string $audience, ?int $classroomId): Collection
    {
        return match ($audience) {
            'semua' => User::query()->get(),
            'murid' => User::query()->where('role', UserRole::Student->value)->get(),
            'guru' => User::query()->where('role', UserRole::Teacher->value)->get(),
            'admin' => User::query()->where('role', UserRole::Admin->value)->get(),
            'kelas' => (function () use ($classroomId): Collection {
                $classroom = Classroom::with('teacher')->findOrFail($classroomId);
                $recipients = $classroom->approvedStudents();
                if ($classroom->teacher !== null && ! $classroom->teacher->trashed()) {
                    $recipients->push($classroom->teacher);
                }

                return $recipients;
            })(),
        };
    }
}
