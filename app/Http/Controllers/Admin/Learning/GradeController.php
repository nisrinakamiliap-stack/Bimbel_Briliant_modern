<?php

namespace App\Http\Controllers\Admin\Learning;

use App\Http\Controllers\Controller;
use App\Http\Requests\GradeRequest;
use App\Models\AssignmentGrade;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

/**
 * Penilaian pengumpulan oleh admin. Guru memakai {@see \App\Http\Controllers\Teacher\GradeController}.
 */
class GradeController extends Controller
{
    /**
     * Menyimpan atau memperbarui nilai dan umpan balik sebuah pengumpulan.
     */
    public function store(GradeRequest $request, Submission $submission): RedirectResponse
    {
        $isUpdate = $submission->grade()->exists();

        $grade = DB::transaction(fn () => AssignmentGrade::record(
            $submission,
            (string) $request->validated('score'),
            $request->validated('feedback'),
            $request->user(),
        ));

        return redirect()
            ->route('admin.learning.submissions.show', $submission)
            ->with('success', ($isUpdate ? 'Nilai berhasil diperbarui menjadi ' : 'Nilai berhasil disimpan: ').$grade->formattedScore().'.');
    }
}
