<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Teacher\Concerns\ListsTeachingSessions;
use App\Models\Assignment;
use App\Models\Schedule;
use App\Models\Submission;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Beranda Portal Guru: sesi mengajar hari ini, pekerjaan yang menunggu, dan notifikasi terbaru.
 */
class HomeController extends Controller
{
    use ListsTeachingSessions;

    /**
     * Jumlah baris pada setiap daftar aktivitas (tugas perlu diperiksa, pengumpulan terbaru, notifikasi).
     */
    private const ACTIVITY_LIMIT = 5;

    /**
     * Halaman Beranda.
     */
    public function __invoke(Request $request): View
    {
        $teacher = $request->user();
        $now = CarbonImmutable::now();

        $ownClasses = fn (Builder $query) => $query->where('teacher_id', $teacher->id);

        return view('teacher.home', [
            'teacher' => $teacher,
            'now' => $now,
            'greeting' => $this->greeting($now),
            'todaySessions' => $this->teachingSessions($teacher, $now, $now),
            'nextSession' => $this->nextSession($teacher->id, $now->addDay()->startOfDay()),
            'ungradedCount' => Submission::query()->whereHas('assignment.classroom', $ownClasses)->doesntHave('grade')->count(),
            'assignmentsToReview' => Assignment::query()
                ->whereHas('classroom', $ownClasses)
                ->whereHas('submissions', fn (Builder $query) => $query->doesntHave('grade'))
                ->withCount(['submissions as ungraded_count' => fn (Builder $query) => $query->doesntHave('grade')])
                ->with('classroom')
                ->orderBy('due_at')
                ->take(self::ACTIVITY_LIMIT)
                ->get(),
            'recentSubmissions' => Submission::query()
                ->whereHas('assignment.classroom', $ownClasses)
                ->with(['student', 'assignment', 'grade'])
                ->latest('submitted_at')
                ->take(self::ACTIVITY_LIMIT)
                ->get(),
            'recentNotifications' => $teacher->notifications()
                ->reorder()
                ->orderByRaw('read_at is null desc')
                ->latest()
                ->take(self::ACTIVITY_LIMIT)
                ->get(),
        ]);
    }

    /**
     * Sapaan sesuai jam di zona waktu aplikasi.
     */
    private function greeting(CarbonImmutable $now): string
    {
        return match (true) {
            $now->hour < 11 => 'Selamat pagi',
            $now->hour < 15 => 'Selamat siang',
            $now->hour < 18 => 'Selamat sore',
            default => 'Selamat malam',
        };
    }

    /**
     * Pertemuan terdekat setelah hari ini, untuk ditampilkan saat hari ini tidak ada jadwal.
     *
     * @return array{schedule: Schedule, starts_at: CarbonImmutable}|null
     */
    private function nextSession(int $teacherId, CarbonImmutable $from): ?array
    {
        return Schedule::query()
            ->where('is_active', true)
            ->whereHas('classroom', fn (Builder $query) => $query->where('teacher_id', $teacherId)->where('is_active', true))
            ->with('classroom')
            ->get()
            ->map(fn (Schedule $schedule) => ($startsAt = $schedule->nextOccurrence($from)) === null ? null : ['schedule' => $schedule, 'starts_at' => $startsAt])
            ->filter()
            ->sortBy('starts_at')
            ->first();
    }
}
