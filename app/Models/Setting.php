<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Pengaturan aplikasi berbentuk kunci-nilai yang aman diubah admin.
 *
 * Hanya kunci dalam {@see EDITABLE_KEYS} yang dapat disimpan. Nilai default diambil dari
 * config/bimbel.php agar tidak ada nilai statis yang diduplikasi.
 */
class Setting extends Model
{
    /**
     * Kunci yang boleh disimpan melalui halaman Pengaturan.
     *
     * @var list<string>
     */
    public const EDITABLE_KEYS = [
        'institution_name',
        'institution_tagline',
        'institution_description',
        'institution_address',
        'institution_phone',
        'institution_whatsapp',
        'institution_email',
        'institution_hours',
        'institution_logo_path',
        'app_timezone',
    ];

    /**
     * Zona waktu Indonesia yang dapat dipilih.
     *
     * @var array<string, string>
     */
    public const TIMEZONES = [
        'Asia/Jakarta' => 'WIB — Waktu Indonesia Barat (Asia/Jakarta)',
        'Asia/Makassar' => 'WITA — Waktu Indonesia Tengah (Asia/Makassar)',
        'Asia/Jayapura' => 'WIT — Waktu Indonesia Timur (Asia/Jayapura)',
    ];

    /**
     * Kunci cache seluruh pengaturan.
     */
    private const CACHE_KEY = 'settings.all';

    /**
     * Disk publik untuk logo (logo memang ditampilkan di halaman publik).
     */
    public const LOGO_DISK = 'public';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['key', 'value'];

    /**
     * Seluruh pengaturan tersimpan (dari cache), atau array kosong jika tabel belum tersedia.
     *
     * @return array<string, string|null>
     */
    public static function allValues(): array
    {
        try {
            return Cache::rememberForever(self::CACHE_KEY, fn () => static::query()->pluck('value', 'key')->all());
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * Nilai sebuah pengaturan, atau default jika belum disimpan.
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        $value = static::allValues()[$key] ?? null;

        return filled($value) ? $value : $default;
    }

    /**
     * Menyimpan beberapa pengaturan sekaligus. Kunci di luar daftar yang diizinkan diabaikan.
     *
     * @param  array<string, string|null>  $values
     */
    public static function putMany(array $values): void
    {
        foreach ($values as $key => $value) {
            if (! in_array($key, self::EDITABLE_KEYS, true)) {
                continue;
            }

            static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Profil lembaga untuk ditampilkan di aplikasi, dengan default dari config/bimbel.php.
     *
     * @return array{name: string, tagline: string|null, description: string|null, address: string|null, phone: string|null, whatsapp: string|null, email: string|null, hours: string|null, logo_url: string}
     */
    public static function institution(): array
    {
        $contact = config('bimbel.kontak', []);
        $logoPath = static::get('institution_logo_path');

        return [
            'name' => static::get('institution_name', 'Bimbel Sentra Edukasi'),
            'tagline' => static::get('institution_tagline'),
            'description' => static::get('institution_description'),
            'address' => static::get('institution_address', $contact['alamat'] ?? null),
            'phone' => static::get('institution_phone', $contact['telepon'] ?? null),
            'whatsapp' => static::get('institution_whatsapp', $contact['whatsapp'] ?? null),
            'email' => static::get('institution_email', $contact['email'] ?? null),
            'hours' => static::get('institution_hours', $contact['jam_operasional'] ?? null),
            'logo_url' => $logoPath !== null ? Storage::disk(self::LOGO_DISK)->url($logoPath) : asset('logo.png'),
        ];
    }

    /**
     * Zona waktu aplikasi yang dipilih admin, atau null jika belum diatur/tidak valid.
     */
    public static function timezone(): ?string
    {
        $timezone = static::get('app_timezone');

        return $timezone !== null && array_key_exists($timezone, self::TIMEZONES) ? $timezone : null;
    }
}
