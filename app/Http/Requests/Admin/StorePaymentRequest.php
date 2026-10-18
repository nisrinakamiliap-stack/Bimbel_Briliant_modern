<?php

namespace App\Http\Requests\Admin;

use App\EnrollmentStatus;
use App\Models\Enrollment;
use App\Models\Payment;
use App\PaymentMethod;
use App\UserRole;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validasi pencatatan pembayaran manual oleh admin.
 *
 * Nominal tidak boleh melebihi sisa tagihan yang masih dapat dibayar (tagihan dikurangi pembayaran
 * berhasil dan transaksi yang masih menunggu verifikasi). Pemeriksaan diulang di dalam transaksi
 * database oleh controller.
 */
class StorePaymentRequest extends FormRequest
{
    /**
     * Ukuran maksimal file bukti pembayaran dalam kilobyte.
     */
    public const MAX_PROOF_KILOBYTES = 2048;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::Admin) ?? false;
    }

    /**
     * Menormalkan nominal berformat rupiah dan catatan kosong.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'amount' => Payment::normalizeRupiahInput($this->input('amount')),
            'notes' => blank($this->input('notes')) ? null : trim((string) $this->input('notes')),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'enrollment_id' => [
                'required',
                'integer',
                Rule::exists('enrollments', 'id')->whereIn('status', array_map(
                    fn (EnrollmentStatus $status) => $status->value,
                    EnrollmentStatus::activeStatuses(),
                )),
            ],
            'amount' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:9999999999.99'],
            'method' => ['required', Rule::enum(PaymentMethod::class)],
            'paid_on' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:'.self::MAX_PROOF_KILOBYTES],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Memastikan nominal tidak melebihi sisa tagihan yang masih dapat dibayar.
     *
     * @return array<int, Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['enrollment_id', 'amount'])) {
                    return;
                }

                $enrollment = Enrollment::findOrFail($this->integer('enrollment_id'));
                $message = static::exceedsPayableMessage($enrollment, (string) $this->input('amount'));

                if ($message !== null) {
                    $validator->errors()->add('amount', $message);
                }
            },
        ];
    }

    /**
     * Pesan kesalahan jika nominal melebihi sisa tagihan yang dapat dibayar, atau null jika valid.
     */
    public static function exceedsPayableMessage(Enrollment $enrollment, string $amount): ?string
    {
        $payableCents = $enrollment->payableCents();

        if ($payableCents === 0) {
            return 'Tagihan pendaftaran ini sudah lunas atau seluruh sisanya sedang menunggu verifikasi.';
        }

        if (Payment::toCents($amount) > $payableCents) {
            return 'Nominal tidak boleh melebihi sisa tagihan yang dapat dibayar ('.Payment::rupiah($payableCents / 100).').';
        }

        return null;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'enrollment_id.required' => 'Silakan pilih pendaftaran yang dibayar.',
            'enrollment_id.integer' => 'Pendaftaran yang dipilih tidak valid.',
            'enrollment_id.exists' => 'Pendaftaran tidak ditemukan atau sudah tidak aktif (ditolak/dibatalkan).',
            'amount.required' => 'Nominal pembayaran wajib diisi.',
            'amount.numeric' => 'Nominal harus berupa angka, contoh: 350000.',
            'amount.decimal' => 'Nominal maksimal memiliki 2 angka di belakang koma.',
            'amount.gt' => 'Nominal pembayaran harus lebih besar dari nol.',
            'amount.max' => 'Nominal terlalu besar.',
            'method.required' => 'Silakan pilih metode pembayaran.',
            'method.enum' => 'Metode pembayaran tidak valid.',
            'paid_on.required' => 'Tanggal pembayaran wajib diisi.',
            'paid_on.date_format' => 'Tanggal pembayaran tidak valid.',
            'paid_on.before_or_equal' => 'Tanggal pembayaran tidak boleh melebihi hari ini.',
            'proof.file' => 'Bukti pembayaran harus berupa file.',
            'proof.mimes' => 'Bukti pembayaran harus berupa gambar JPG/PNG atau dokumen PDF.',
            'proof.max' => 'Ukuran bukti pembayaran maksimal 2 MB.',
            'proof.uploaded' => 'Bukti pembayaran gagal diunggah. Pastikan ukurannya tidak lebih dari 2 MB.',
            'notes.max' => 'Catatan maksimal 1000 karakter.',
        ];
    }
}
