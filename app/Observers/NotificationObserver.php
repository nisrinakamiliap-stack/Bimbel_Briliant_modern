<?php

namespace App\Observers;

use App\LearningContentStatus;
use App\Models\Assignment;
use App\Models\AssignmentGrade;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Schedule;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\AssignmentPublished;
use App\Notifications\EnrollmentCreated;
use App\Notifications\NewStudentRegistered;
use App\Notifications\NotificationSender;
use App\Notifications\PaymentAwaitingVerification;
use App\Notifications\ScheduleAssigned;
use App\Notifications\SubmissionGraded;
use App\Notifications\SubmissionReceived;
use App\Providers\AppServiceProvider;
use App\UserRole;

/**
 * Mengubah peristiwa nyata aplikasi menjadi notifikasi.
 *
 * Didaftarkan di {@see AppServiceProvider} dan dijalankan melalui `DB::afterCommit()`,
 * sehingga notifikasi hanya terkirim setelah transaksi database berhasil. Duplikasi dicegah oleh
 * {@see NotificationSender} melalui kunci peristiwa.
 */
class NotificationObserver
{
    /**
     * Akun dibuat: murid baru dilaporkan ke admin.
     */
    public function userCreated(User $user): void
    {
        if ($user->hasRole(UserRole::Student)) {
            NotificationSender::send(NotificationSender::admins(), new NewStudentRegistered($user));
        }
    }

    /**
     * Pendaftaran dibuat: admin diberi tahu untuk verifikasi.
     */
    public function enrollmentCreated(Enrollment $enrollment): void
    {
        NotificationSender::send(NotificationSender::admins(), new EnrollmentCreated($enrollment));
    }

    /**
     * Pembayaran dicatat: admin diberi tahu untuk verifikasi.
     */
    public function paymentCreated(Payment $payment): void
    {
        NotificationSender::send(NotificationSender::admins(), new PaymentAwaitingVerification($payment));
    }

    /**
     * Tugas disimpan: saat berstatus Terbit, murid peserta kelas diberi tahu (sekali per tugas).
     */
    public function assignmentSaved(Assignment $assignment): void
    {
        if ($assignment->status === LearningContentStatus::Published) {
            NotificationSender::send($assignment->classroom->approvedStudents(), new AssignmentPublished($assignment));
        }
    }

    /**
     * Jadwal dibuat: guru pengampu dan murid peserta kelas diberi tahu.
     */
    public function scheduleCreated(Schedule $schedule): void
    {
        $classroom = $schedule->classroom;
        $recipients = $classroom->approvedStudents();

        if ($classroom->teacher !== null && ! $classroom->teacher->trashed()) {
            $recipients->push($classroom->teacher);
        }

        NotificationSender::send($recipients, new ScheduleAssigned($schedule));
    }

    /**
     * Pengumpulan pertama: guru pengampu kelas diberi tahu.
     */
    public function submissionCreated(Submission $submission): void
    {
        $teacher = $submission->assignment->classroom->teacher;

        if ($teacher !== null && ! $teacher->trashed()) {
            NotificationSender::send($teacher, new SubmissionReceived($submission));
        }
    }

    /**
     * Nilai disimpan/diubah: murid pemilik pengumpulan diberi tahu (sekali per revisi nilai).
     */
    public function gradeSaved(AssignmentGrade $grade): void
    {
        NotificationSender::send($grade->submission->student, new SubmissionGraded($grade));
    }
}
