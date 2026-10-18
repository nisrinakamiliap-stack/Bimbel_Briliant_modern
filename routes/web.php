<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\ClassroomController;
use App\Http\Controllers\Admin\EnrollmentController;
use App\Http\Controllers\Admin\Learning\AssignmentController as LearningAssignmentController;
use App\Http\Controllers\Admin\Learning\GradeController as LearningGradeController;
use App\Http\Controllers\Admin\Learning\MaterialController as LearningMaterialController;
use App\Http\Controllers\Admin\Learning\SubmissionController as LearningSubmissionController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ProgramController as AdminProgramController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StudentAttendanceController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TeacherAttendanceController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\LearningFileController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\Student\LearningController as StudentLearningController;
use App\Http\Controllers\Teacher\CheckInController;
use App\Http\Controllers\Teacher\GradeController as TeacherGradeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/program', [ProgramController::class, 'index'])->name('program.index');
Route::get('/program/{slug}', [ProgramController::class, 'show'])->name('program.show');

Route::get('/jadwal', [JadwalController::class, 'index'])->name('jadwal.index');

Route::get('/tentang-kami', [AboutController::class, 'index'])->name('tentang.index');

Route::get('/kontak', [ContactController::class, 'index'])->name('kontak.index');
Route::post('/kontak', [ContactController::class, 'store'])->name('kontak.store');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])
        ->middleware('role:admin')
        ->name('admin.dashboard');

    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::resource('murid', StudentController::class)
            ->parameters(['murid' => 'student'])
            ->names('students');

        Route::resource('guru', TeacherController::class)
            ->parameters(['guru' => 'teacher'])
            ->names('teachers');

        Route::patch('program/{program}/status', [AdminProgramController::class, 'toggleStatus'])
            ->name('programs.toggle-status');
        Route::resource('program', AdminProgramController::class)
            ->parameters(['program' => 'program'])
            ->names('programs');

        Route::patch('kelas/{classroom}/status', [ClassroomController::class, 'toggleStatus'])
            ->name('classes.toggle-status');
        Route::resource('kelas', ClassroomController::class)
            ->parameters(['kelas' => 'classroom'])
            ->names('classes');

        Route::patch('jadwal/{schedule}/status', [ScheduleController::class, 'toggleStatus'])
            ->name('schedules.toggle-status');
        Route::resource('jadwal', ScheduleController::class)
            ->parameters(['jadwal' => 'schedule'])
            ->names('schedules');

        Route::patch('pendaftaran/{enrollment}/status', [EnrollmentController::class, 'updateStatus'])
            ->name('enrollments.update-status');
        Route::resource('pendaftaran', EnrollmentController::class)
            ->parameters(['pendaftaran' => 'enrollment'])
            ->except(['destroy'])
            ->names('enrollments');

        Route::get('pembayaran/{payment}/bukti', [PaymentController::class, 'proof'])->name('payments.proof');
        Route::patch('pembayaran/{payment}/verifikasi', [PaymentController::class, 'verify'])->name('payments.verify');
        Route::patch('pembayaran/{payment}/tolak', [PaymentController::class, 'reject'])->name('payments.reject');
        Route::resource('pembayaran', PaymentController::class)
            ->parameters(['pembayaran' => 'payment'])
            ->only(['index', 'create', 'store', 'show'])
            ->names('payments');

        Route::prefix('absensi')->name('attendances.')->group(function () {
            Route::get('murid', [StudentAttendanceController::class, 'index'])->name('students.index');
            Route::get('murid/catat', [StudentAttendanceController::class, 'create'])->name('students.create');
            Route::post('murid', [StudentAttendanceController::class, 'store'])->name('students.store');
            Route::get('murid/{studentAttendance}/koreksi', [StudentAttendanceController::class, 'edit'])->name('students.edit');
            Route::put('murid/{studentAttendance}', [StudentAttendanceController::class, 'update'])->name('students.update');

            Route::get('guru', [TeacherAttendanceController::class, 'index'])->name('teachers.index');
            Route::get('guru/{teacherAttendance}/koreksi', [TeacherAttendanceController::class, 'edit'])->name('teachers.edit');
            Route::put('guru/{teacherAttendance}', [TeacherAttendanceController::class, 'update'])->name('teachers.update');
        });

        Route::prefix('pembelajaran')->name('learning.')->group(function () {
            Route::resource('materi', LearningMaterialController::class)
                ->parameters(['materi' => 'material'])
                ->except(['destroy'])
                ->names('materials');
            Route::resource('tugas', LearningAssignmentController::class)
                ->parameters(['tugas' => 'assignment'])
                ->names('assignments');
            Route::get('pengumpulan', [LearningSubmissionController::class, 'index'])->name('submissions.index');
            Route::get('pengumpulan/{submission}', [LearningSubmissionController::class, 'show'])->name('submissions.show');
            Route::get('penilaian', [LearningSubmissionController::class, 'grading'])->name('grades.index');
            Route::post('pengumpulan/{submission}/nilai', [LearningGradeController::class, 'store'])->name('grades.store');
        });

        Route::get('pengumuman/buat', [AnnouncementController::class, 'create'])->name('announcements.create');
        Route::post('pengumuman', [AnnouncementController::class, 'store'])->name('announcements.store');

        Route::prefix('laporan')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'students'])->name('students');
            Route::get('pembayaran', [ReportController::class, 'payments'])->name('payments');
            Route::get('absensi', [ReportController::class, 'attendance'])->name('attendance');
            Route::get('pembelajaran', [ReportController::class, 'learning'])->name('learning');
        });

        Route::get('pengaturan', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('pengaturan', [SettingController::class, 'update'])->name('settings.update');
    });

    Route::prefix('notifikasi')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('baca-semua', [NotificationController::class, 'markAllAsRead'])->name('read-all');
        Route::get('{notification}/buka', [NotificationController::class, 'open'])->name('open');
        Route::patch('{notification}/baca', [NotificationController::class, 'markAsRead'])->name('read');
        Route::delete('{notification}', [NotificationController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('pesan')->name('messages.')->group(function () {
        Route::get('/', [MessageController::class, 'index'])->name('index');
        Route::get('baru', [MessageController::class, 'create'])->name('create');
        Route::post('/', [MessageController::class, 'store'])->name('store');
        Route::get('{conversation}', [MessageController::class, 'show'])->name('show');
        Route::post('{conversation}', [MessageController::class, 'reply'])->name('reply');
    });

    Route::prefix('pembelajaran/berkas')->name('learning.files.')->group(function () {
        Route::get('materi/{material}', [LearningFileController::class, 'material'])->name('material');
        Route::get('tugas/{assignment}', [LearningFileController::class, 'assignment'])->name('assignment');
        Route::get('pengumpulan/{submission}', [LearningFileController::class, 'submission'])->name('submission');
    });

    Route::get('/guru/dashboard', [DashboardController::class, 'teacher'])
        ->middleware('role:teacher')
        ->name('teacher.dashboard');

    Route::prefix('guru')->name('teacher.')->middleware('role:teacher')->group(function () {
        Route::post('absensi/{schedule}/check-in', [CheckInController::class, 'store'])->name('attendance.check-in');
        Route::patch('absensi/{schedule}/check-out', [CheckInController::class, 'update'])->name('attendance.check-out');
        Route::post('pembelajaran/pengumpulan/{submission}/nilai', [TeacherGradeController::class, 'store'])->name('learning.grades.store');
    });

    Route::get('/murid/dashboard', [DashboardController::class, 'student'])
        ->middleware('role:student')
        ->name('student.dashboard');

    Route::prefix('murid/pembelajaran')->name('student.learning.')->middleware('role:student')->group(function () {
        Route::get('/', [StudentLearningController::class, 'index'])->name('index');
        Route::get('materi/{material}', [StudentLearningController::class, 'material'])->name('materials.show');
        Route::get('tugas/{assignment}', [StudentLearningController::class, 'assignment'])->name('assignments.show');
        Route::post('tugas/{assignment}', [StudentLearningController::class, 'submit'])->name('assignments.submit');
    });
});
