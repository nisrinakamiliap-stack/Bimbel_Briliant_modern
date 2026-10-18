<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\Learning\MaterialController;
use App\Models\Assignment;
use App\Models\Material;
use App\Models\Submission;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Unduhan berkas pembelajaran dari disk privat untuk semua role.
 *
 * Setiap unduhan diotorisasi melalui policy: admin, guru pengampu kelas, atau murid peserta kelas
 * (materi/tugas terbit) dan pemilik pengumpulan.
 */
class LearningFileController extends Controller
{
    /**
     * Berkas materi.
     */
    public function material(Material $material): StreamedResponse
    {
        Gate::authorize('view', $material);

        return $this->stream($material->file_path, $material->file_original_name);
    }

    /**
     * Lampiran tugas.
     */
    public function assignment(Assignment $assignment): StreamedResponse
    {
        Gate::authorize('view', $assignment);

        return $this->stream($assignment->attachment_path, $assignment->attachment_original_name);
    }

    /**
     * Berkas jawaban pengumpulan.
     */
    public function submission(Submission $submission): StreamedResponse
    {
        Gate::authorize('view', $submission);

        return $this->stream($submission->file_path, $submission->file_original_name);
    }

    /**
     * Mengirim berkas privat sebagai unduhan, atau 404 jika tidak ada.
     */
    private function stream(?string $path, ?string $name): StreamedResponse
    {
        $disk = Storage::disk(MaterialController::DISK);

        abort_if($path === null || ! $disk->exists($path), 404);

        return $disk->download($path, $name ?? basename($path), [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
