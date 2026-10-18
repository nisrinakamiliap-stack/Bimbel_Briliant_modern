<?php

namespace App\Http\Requests\Admin\Learning;

use App\LearningContentStatus;
use App\Models\Classroom;
use App\Models\Material;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validasi form tambah dan edit materi pembelajaran.
 */
class MaterialRequest extends FormRequest
{
    /**
     * Jenis berkas materi yang diizinkan (tidak termasuk file yang dapat dieksekusi).
     */
    public const ALLOWED_MIMES = 'pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,zip';

    /**
     * Ukuran maksimal berkas dalam kilobyte (10 MB).
     */
    public const MAX_FILE_KILOBYTES = 10240;

    /**
     * Hanya pengguna yang berhak mengelola materi pada kelas tujuan yang dapat menyimpan materi.
     */
    public function authorize(): bool
    {
        $classroom = Classroom::find($this->integer('classroom_id'));

        if ($classroom === null) {
            return $this->user() !== null;
        }

        return $this->user()?->can('create', [Material::class, $classroom]) ?? false;
    }

    /**
     * Menormalkan isian kosong menjadi null.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'link_url' => blank($this->input('link_url')) ? null : trim((string) $this->input('link_url')),
            'description' => blank($this->input('description')) ? null : $this->input('description'),
            'content' => blank($this->input('content')) ? null : $this->input('content'),
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
            'classroom_id' => ['required', 'integer', Rule::exists('classes', 'id')],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:1000'],
            'content' => ['nullable', 'string', 'max:50000'],
            'link_url' => ['nullable', 'url:http,https', 'max:500'],
            'file' => ['nullable', 'file', 'mimes:'.self::ALLOWED_MIMES, 'max:'.self::MAX_FILE_KILOBYTES],
            'remove_file' => ['nullable', 'boolean'],
            'status' => ['required', Rule::enum(LearningContentStatus::class)],
        ];
    }

    /**
     * Materi minimal memiliki isi, tautan, atau berkas.
     *
     * @return array<int, Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                /** @var Material|null $material */
                $material = $this->route('material');
                $keepsExistingFile = $material?->hasFile() && ! $this->boolean('remove_file');

                if (blank($this->input('content')) && blank($this->input('link_url')) && ! $this->hasFile('file') && ! $keepsExistingFile) {
                    $validator->errors()->add('content', 'Isi materi, tautan, atau berkas wajib diisi salah satu.');
                }
            },
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'classroom_id.required' => 'Silakan pilih kelas tujuan.',
            'classroom_id.exists' => 'Kelas tidak ditemukan.',
            'title.required' => 'Judul materi wajib diisi.',
            'title.max' => 'Judul materi maksimal 200 karakter.',
            'description.max' => 'Deskripsi maksimal 1000 karakter.',
            'content.max' => 'Isi materi terlalu panjang.',
            'link_url.url' => 'Tautan harus berupa URL yang valid (diawali http:// atau https://).',
            'link_url.max' => 'Tautan maksimal 500 karakter.',
            'file.file' => 'Berkas materi tidak valid.',
            'file.mimes' => 'Berkas materi harus berupa PDF, Word, PowerPoint, Excel, gambar JPG/PNG, atau ZIP.',
            'file.max' => 'Ukuran berkas materi maksimal 10 MB.',
            'file.uploaded' => 'Berkas gagal diunggah. Pastikan ukurannya tidak lebih dari 10 MB.',
            'status.required' => 'Silakan pilih status materi.',
            'status.enum' => 'Status materi tidak valid.',
        ];
    }
}
