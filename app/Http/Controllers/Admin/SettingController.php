<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Pengaturan sistem yang aman diubah admin: profil lembaga dan zona waktu.
 *
 * Halaman ini tidak pernah membaca atau menampilkan konfigurasi sensitif (APP_KEY, kredensial
 * database, dan sejenisnya).
 */
class SettingController extends Controller
{
    /**
     * Form pengaturan.
     */
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'institution' => Setting::institution(),
            'values' => Setting::allValues(),
            'timezones' => Setting::TIMEZONES,
            'currentTimezone' => config('app.timezone'),
        ]);
    }

    /**
     * Menyimpan pengaturan. Kunci di luar daftar yang diizinkan diabaikan oleh model.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'institution_name' => ['required', 'string', 'max:120'],
            'institution_tagline' => ['nullable', 'string', 'max:160'],
            'institution_description' => ['nullable', 'string', 'max:1000'],
            'institution_address' => ['nullable', 'string', 'max:300'],
            'institution_phone' => ['nullable', 'string', 'max:40', 'regex:/^[0-9+()\-\s\/]+$/'],
            'institution_whatsapp' => ['nullable', 'string', 'max:40', 'regex:/^[0-9+()\-\s]+$/'],
            'institution_email' => ['nullable', 'email', 'max:150'],
            'institution_hours' => ['nullable', 'string', 'max:120'],
            'app_timezone' => ['required', Rule::in(array_keys(Setting::TIMEZONES))],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
            'remove_logo' => ['nullable', 'boolean'],
        ], [
            'institution_name.required' => 'Nama lembaga wajib diisi.',
            'institution_phone.regex' => 'Nomor telepon hanya boleh berisi angka, spasi, tanda +, -, /, dan tanda kurung.',
            'institution_whatsapp.regex' => 'Nomor WhatsApp hanya boleh berisi angka, spasi, tanda +, -, dan tanda kurung.',
            'institution_email.email' => 'Format email tidak valid.',
            'app_timezone.in' => 'Zona waktu tidak valid.',
            'logo.image' => 'Logo harus berupa gambar.',
            'logo.mimes' => 'Logo harus berformat PNG, JPG, atau WebP.',
            'logo.max' => 'Ukuran logo maksimal 1 MB.',
        ]);

        $values = collect($validated)->except(['logo', 'remove_logo'])->all();
        $oldLogo = Setting::get('institution_logo_path');

        if ($request->hasFile('logo')) {
            $values['institution_logo_path'] = $request->file('logo')->store('settings', Setting::LOGO_DISK);
        } elseif ($request->boolean('remove_logo')) {
            $values['institution_logo_path'] = null;
        }

        Setting::putMany($values);

        if (array_key_exists('institution_logo_path', $values) && $oldLogo !== null && $oldLogo !== $values['institution_logo_path']) {
            Storage::disk(Setting::LOGO_DISK)->delete($oldLogo);
        }

        return redirect()->route('admin.settings.edit')->with('success', 'Pengaturan berhasil disimpan.');
    }
}
