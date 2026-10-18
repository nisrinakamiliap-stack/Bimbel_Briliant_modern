<?php

namespace App\Http\Controllers\Student;

use App\EducationLevel;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\UserRole;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $student = $request->user();
        $profile = $student->studentProfile;

        return view('student.profile.edit', [
            'student' => $student,
            'profile' => $profile,
            'educationLevels' => EducationLevel::cases(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $student = $request->user();

        $phone = blank($request->input('phone')) ? null : preg_replace('/[\s\-().]/', '', (string) $request->input('phone'));

        $validated = validator(
            array_merge($request->all(), ['phone' => $phone]),
            [
                'phone'          => ['nullable', 'string', 'regex:' . User::PHONE_PATTERN],
                'address'        => ['nullable', 'string', 'max:500'],
                'education_level'=> ['nullable', Rule::enum(EducationLevel::class)],
                'school_name'    => ['nullable', 'string', 'max:200'],
                'birth_date'     => ['nullable', 'date', 'before:today'],
                'parent_name'    => ['nullable', 'string', 'max:200'],
                'parent_phone'   => ['nullable', 'string', 'regex:' . User::PHONE_PATTERN],
            ],
            [
                'phone.regex' => 'Nomor telepon tidak valid. Contoh: 081234567890.',
                'parent_phone.regex' => 'Nomor telepon orang tua tidak valid.',
            ]
        )->validate();

        $student->phone = $validated['phone'] ?? $student->phone;
        $student->save();

        $profileData = array_filter([
            'education_level' => $validated['education_level'] ?? null,
            'school_name'     => $validated['school_name'] ?? null,
            'birth_date'      => $validated['birth_date'] ?? null,
            'address'         => $validated['address'] ?? null,
            'parent_name'     => $validated['parent_name'] ?? null,
            'parent_phone'    => $validated['parent_phone'] ?? null,
        ], fn ($v) => $v !== null);

        if ($student->studentProfile === null) {
            $student->studentProfile()->create($profileData);
        } else {
            $student->studentProfile()->update($profileData);
        }

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $request->user()->update(['password' => bcrypt($validated['password'])]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
