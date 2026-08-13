<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmployeeQuestion;
use App\Models\EmployeeQuestionFile;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class EmployeeQuestionController extends Controller
{
    /**
     * Simpan pertanyaan kepegawaian baru.
     */
    public function store(Request $request): JsonResponse
    {
        // =====================================================
        // VALIDASI KATEGORI PENGGUNA
        // =====================================================

        $categoryValidator = Validator::make(
            $request->all(),
            [
                'user_category' => [
                    'required',
                    'string',
                    Rule::in([
                        'dosen',
                        'tenaga_kependidikan',
                        'unit_kerja',
                    ]),
                ],
            ],
            [
                'user_category.required' =>
                    'Kategori pengguna tidak valid',

                'user_category.string' =>
                    'Kategori pengguna tidak valid',

                'user_category.in' =>
                    'Kategori pengguna tidak valid',
            ]
        );

        if ($categoryValidator->fails()) {
            return $this->responseJson(
                false,
                $categoryValidator->errors()->first(),
                [],
                422
            );
        }

        $userCategory =
            $categoryValidator
                ->validated()['user_category'];


        // =====================================================
        // VALIDASI DATA
        // =====================================================

        $validator = Validator::make(
            $request->all(),
            [
                'unit_name' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'full_name' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'nip' => [
                    'required',
                    'string',
                    'max:30',
                ],

                'email' => [
                    'required',
                    'email',
                    'max:150',
                ],

                'phone' => [
                    'required',
                    'string',
                    'max:20',
                ],

                'question_title' => [
                    'required',
                    'string',
                    'max:200',
                ],

                'question_content' => [
                    'required',
                    'string',
                ],

                'support_file' => [
                    'nullable',
                    'file',
                    'max:5120',
                    'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx',
                    'extensions:jpg,jpeg,png,pdf,doc,docx,xls,xlsx',
                ],
            ],
            [
                'unit_name.required' =>
                    'Unit kerja wajib dipilih',

                'full_name.required' =>
                    'Nama lengkap wajib diisi',

                'nip.required' =>
                    'NIP wajib diisi',

                'email.required' =>
                    'Email wajib diisi',

                'email.email' =>
                    'Format email tidak valid',

                'phone.required' =>
                    'Nomor telepon wajib diisi',

                'question_title.required' =>
                    'Judul pertanyaan wajib diisi',

                'question_content.required' =>
                    'Isi pertanyaan wajib diisi',

                'support_file.file' =>
                    'Terjadi kesalahan saat upload file',

                'support_file.uploaded' =>
                    'Terjadi kesalahan saat upload file',

                'support_file.max' =>
                    'Ukuran file maksimal 5 MB',

                'support_file.mimes' =>
                    'Format file tidak diperbolehkan',

                'support_file.extensions' =>
                    'Format file tidak diperbolehkan',
            ]
        );

        if ($validator->fails()) {
            return $this->responseJson(
                false,
                $validator->errors()->first(),
                [],
                422
            );
        }

        $validated =
            $validator->validated();


        // =====================================================
        // CARI UNIT KERJA
        // =====================================================

        $unitName =
            trim($validated['unit_name']);

        $unit =
            Unit::query()
                ->where('name', $unitName)
                ->where('is_active', 1)
                ->first();

        if (!$unit) {
            return $this->responseJson(
                false,
                'Unit kerja tidak ditemukan',
                [],
                422
            );
        }


        // =====================================================
        // NILAI DEFAULT
        // =====================================================

        $status =
            'menunggu_verifikasi';

        $estimatedResponse =
            '1-2 Hari Kerja';


        // =====================================================
        // FILE YANG SUDAH TERSIMPAN
        // =====================================================

        $storedPaths = [];


        // =====================================================
        // MULAI TRANSAKSI
        // =====================================================

        DB::beginTransaction();

        try {

            // =================================================
            // SIMPAN PERTANYAAN
            // =================================================

            $question =
                EmployeeQuestion::create([
                    'question_number' =>
                        null,

                    'user_category' =>
                        $userCategory,

                    'unit_id' =>
                        $unit->id,

                    'full_name' =>
                        trim(
                            $validated['full_name']
                        ),

                    'nip' =>
                        trim(
                            $validated['nip']
                        ),

                    'email' =>
                        trim(
                            $validated['email']
                        ),

                    'phone' =>
                        trim(
                            $validated['phone']
                        ),

                    'question_title' =>
                        trim(
                            $validated[
                                'question_title'
                            ]
                        ),

                    'question_content' =>
                        trim(
                            $validated[
                                'question_content'
                            ]
                        ),

                    'status' =>
                        $status,

                    'answer' =>
                        null,

                    'estimated_response' =>
                        $estimatedResponse,

                    'answered_at' =>
                        null,
                ]);


            // =================================================
            // BUAT NOMOR PERTANYAAN
            // =================================================

            $year =
                Carbon::now(
                    'Asia/Jakarta'
                )->format('Y');

            $runningNumber =
                str_pad(
                    (string) $question->id,
                    6,
                    '0',
                    STR_PAD_LEFT
                );

            $questionNumber =
                'KPG-'
                . $year
                . '-'
                . $runningNumber;


            // =================================================
            // UPDATE NOMOR PERTANYAAN
            // =================================================

            $question->update([
                'question_number' =>
                    $questionNumber,
            ]);


            // =================================================
            // UPLOAD FILE PENDUKUNG
            // =================================================

            $this->saveQuestionFile(
                $request,
                $question->id,
                $storedPaths
            );


            // =================================================
            // COMMIT
            // =================================================

            DB::commit();


            // =================================================
            // RESPONSE BERHASIL
            // =================================================

            return $this->responseJson(
                true,
                'Pertanyaan berhasil dikirim',
                [
                    'id' =>
                        $question->id,

                    'question_number' =>
                        $questionNumber,

                    'user_category' =>
                        $userCategory,

                    'unit_name' =>
                        $unitName,

                    'full_name' =>
                        $question->full_name,

                    'nip' =>
                        $question->nip,

                    'status' =>
                        $status,

                    'estimated_response' =>
                        $estimatedResponse,
                ],
                201
            );

        } catch (Throwable $e) {

            // =================================================
            // ROLLBACK DATABASE
            // =================================================

            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }


            // =================================================
            // HAPUS FILE JIKA TRANSAKSI GAGAL
            // =================================================

            foreach ($storedPaths as $path) {

                if (
                    Storage::disk('public')
                        ->exists($path)
                ) {
                    Storage::disk('public')
                        ->delete($path);
                }
            }


            // =================================================
            // SIMPAN ERROR KE LOG
            // =================================================

            Log::error(
                'KEPEGAWAIAN API ERROR',
                [
                    'message' =>
                        $e->getMessage(),
                ]
            );


            // =================================================
            // RESPONSE GAGAL
            // =================================================

            return $this->responseJson(
                false,
                'Pertanyaan gagal disimpan',
                [],
                500
            );
        }
    }


    /**
     * Simpan file pendukung pertanyaan.
     */
    private function saveQuestionFile(
        Request $request,
        int $questionId,
        array &$storedPaths
    ): void {

        if (!$request->hasFile('support_file')) {
            return;
        }

        $file =
            $request->file('support_file');

        if (
            !$file
            || !$file->isValid()
        ) {
            throw new \RuntimeException(
                'Terjadi kesalahan saat upload file'
            );
        }


        // =====================================================
        // NAMA FILE ASLI
        // =====================================================

        $originalName =
            $file->getClientOriginalName();


        // =====================================================
        // EXTENSION
        // =====================================================

        $extension =
            strtolower(
                $file->getClientOriginalExtension()
            );


        // =====================================================
        // NAMA FILE BARU
        // =====================================================

        $storedName =
            'kepegawaian_'
            . Carbon::now(
                'Asia/Jakarta'
            )->format('Ymd_His')
            . '_'
            . bin2hex(
                random_bytes(5)
            )
            . '.'
            . $extension;


        // =====================================================
        // SIMPAN KE STORAGE
        // =====================================================

        $path =
            Storage::disk('public')
                ->putFileAs(
                    'uploads/kepegawaian',
                    $file,
                    $storedName
                );

        if ($path === false) {
            throw new \RuntimeException(
                'File gagal disimpan'
            );
        }

        $storedPaths[] =
            $path;


        // =====================================================
        // SIMPAN INFORMASI FILE KE DATABASE
        // =====================================================

        EmployeeQuestionFile::create([
            'question_id' =>
                $questionId,

            'original_name' =>
                $originalName,

            'stored_name' =>
                $storedName,

            'file_path' =>
                $path,

            'mime_type' =>
                $file->getMimeType(),

            'file_size' =>
                $file->getSize(),
        ]);
    }


    /**
     * Response JSON.
     */
    private function responseJson(
        bool $success,
        string $message,
        array $data = [],
        int $statusCode = 200
    ): JsonResponse {

        return response()->json(
            [
                'success' =>
                    $success,

                'message' =>
                    $message,

                'data' =>
                    $data,
            ],
            $statusCode,
            [],
            JSON_PRETTY_PRINT
        );
    }
}