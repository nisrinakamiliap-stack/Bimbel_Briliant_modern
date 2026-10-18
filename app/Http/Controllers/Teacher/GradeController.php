<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\GradeRequest;
use App\Models\AssignmentGrade;
use App\Models\Submission;
use App\Policies\SubmissionPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

/**
 * Endpoint penilaian untuk Dashboard Guru. Otorisasi (guru pengampu kelas tugas) diperiksa oleh
 * {@see GradeRequest} melalui {@see SubmissionPolicy::grade()}.
 */
class GradeController extends Controller
{
    /**
     * Menyimpan atau memperbarui nilai dan umpan balik sebuah pengumpulan.
     */
    public function store(GradeRequest $request, Submission $submission): RedirectResponse
    {
        $grade = DB::transaction(fn () => AssignmentGrade::record(
            $submission,
            (string) $request->validated('score'),
            $request->validated('feedback'),
            $request->user(),
        ));

        return back()->with('success', 'Nilai '.$grade->formattedScore().' berhasil disimpan.');
    }
}
