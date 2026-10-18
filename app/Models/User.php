<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\EnrollmentStatus;
use App\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Kolom `role` sengaja tidak dimasukkan ke Fillable agar tidak dapat diisi
 * melalui mass assignment dari input pengguna.
 */
#[Fillable(['name', 'email', 'phone', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Pola nomor HP Indonesia yang diterima (setelah spasi dan tanda hubung dibersihkan).
     */
    public const PHONE_PATTERN = '/^(\+62|62|0)8[0-9]{7,12}$/';

    /**
     * Nilai default atribut model, selaras dengan default kolom di database.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'role' => 'student',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    /**
     * Menentukan apakah pengguna memiliki salah satu role yang diberikan.
     */
    public function hasRole(UserRole ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    /**
     * Nama route dashboard sesuai role pengguna.
     */
    public function dashboardRoute(): string
    {
        return $this->role->dashboardRoute();
    }

    /**
     * Profil murid milik pengguna.
     *
     * @return HasOne<StudentProfile, $this>
     */
    public function studentProfile(): HasOne
    {
        return $this->hasOne(StudentProfile::class);
    }

    /**
     * Membatasi query hanya pada pengguna dengan role murid.
     *
     * @param  Builder<User>  $query
     */
    #[Scope]
    protected function students(Builder $query): void
    {
        $query->where('role', UserRole::Student);
    }

    /**
     * Profil guru milik pengguna.
     *
     * @return HasOne<TeacherProfile, $this>
     */
    public function teacherProfile(): HasOne
    {
        return $this->hasOne(TeacherProfile::class);
    }

    /**
     * Kelas yang diampu oleh pengguna sebagai guru.
     *
     * @return HasMany<Classroom, $this>
     */
    public function taughtClasses(): HasMany
    {
        return $this->hasMany(Classroom::class, 'teacher_id');
    }

    /**
     * Pendaftaran program milik pengguna sebagai murid.
     *
     * @return HasMany<Enrollment, $this>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'student_id');
    }

    /**
     * Pendaftaran berstatus Diterima milik murid ini di kelas tertentu, atau null jika bukan peserta kelas.
     *
     * Dipakai sebagai dasar hak akses murid terhadap materi, tugas, dan pengumpulan kelas.
     */
    public function approvedEnrollmentIn(int $classroomId): ?Enrollment
    {
        if (! $this->hasRole(UserRole::Student)) {
            return null;
        }

        return $this->enrollments()
            ->where('classroom_id', $classroomId)
            ->where('status', EnrollmentStatus::Approved->value)
            ->first();
    }

    /**
     * Menentukan apakah pengguna boleh berkirim pesan dengan pengguna lain.
     *
     * Admin dengan siapa pun; guru dengan admin dan murid di kelas yang diampunya; murid dengan admin
     * dan guru pengampu kelasnya. Akun yang sudah dihapus tidak dapat dihubungi.
     */
    public function canMessage(User $other): bool
    {
        if ($other->is($this) || $other->trashed() || $this->trashed()) {
            return false;
        }

        if ($this->hasRole(UserRole::Admin) || $other->hasRole(UserRole::Admin)) {
            return true;
        }

        if ($this->hasRole(UserRole::Teacher) && $other->hasRole(UserRole::Student)) {
            return $this->taughtClasses()->whereIn('id', $other->approvedClassroomIds())->exists();
        }

        if ($this->hasRole(UserRole::Student) && $other->hasRole(UserRole::Teacher)) {
            return $other->canMessage($this);
        }

        return false;
    }

    /**
     * Daftar pengguna yang boleh dihubungi melalui pesan, sesuai aturan {@see canMessage()}.
     *
     * @return Collection<int, User>
     */
    public function messageableContacts(): Collection
    {
        $query = static::query()->whereKeyNot($this->id)->orderBy('role')->orderBy('name');

        return match ($this->role) {
            UserRole::Admin => $query->get(),
            UserRole::Teacher => $query->where(fn (Builder $query) => $query
                ->where('role', UserRole::Admin->value)
                ->orWhereIn('id', Enrollment::query()
                    ->whereIn('classroom_id', $this->taughtClasses()->select('id'))
                    ->where('status', EnrollmentStatus::Approved->value)
                    ->select('student_id')))
                ->get(),
            UserRole::Student => $query->where(fn (Builder $query) => $query
                ->where('role', UserRole::Admin->value)
                ->orWhereIn('id', Classroom::query()->whereIn('id', $this->approvedClassroomIds())->whereNotNull('teacher_id')->select('teacher_id')))
                ->get(),
        };
    }

    /**
     * Jumlah percakapan yang memiliki pesan belum dibaca.
     */
    public function unreadConversationsCount(): int
    {
        return Conversation::query()
            ->forUser($this)
            ->whereHas('messages', fn (Builder $query) => $query
                ->where('sender_id', '!=', $this->id)
                ->whereRaw('messages.id > coalesce((select last_read_message_id from conversation_participants cp where cp.conversation_id = messages.conversation_id and cp.user_id = ?), 0)', [$this->id]))
            ->count();
    }

    /**
     * ID kelas tempat murid ini terdaftar dengan status Diterima.
     *
     * @return list<int>
     */
    public function approvedClassroomIds(): array
    {
        return $this->enrollments()
            ->whereNotNull('classroom_id')
            ->where('status', EnrollmentStatus::Approved->value)
            ->pluck('classroom_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Membatasi query hanya pada pengguna dengan role guru.
     *
     * @param  Builder<User>  $query
     */
    #[Scope]
    protected function teachers(Builder $query): void
    {
        $query->where('role', UserRole::Teacher);
    }
}
