<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DataRequest;
use App\Models\RequestCategory;
use App\Models\RequestFile;
use App\Models\RequestStatusHistory;
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

class DataRequestController extends Controller
{
    /**
     * Simpan permintaan data baru.
     */
    public function store(Request $request): JsonResponse
    {
        // =====================================================
        // VALIDASI JENIS PEMOHON
        // =====================================================

        $requesterValidator = Validator::make(
            $request->all(),
            [
                'requester_type' => [
                    'required',
                    'string',
                    Rule::in([
                        'unit_kerja',
                        'dosen',
                        'mahasiswa',
                    ]),
                ],
            ],
            [
                'requester_type.required' =>
                    'Jenis pemohon tidak valid',

                'requester_type.string' =>
                    'Jenis pemohon tidak valid',

                'requester_type.in' =>
                    'Jenis pemohon tidak valid',
            ]
        );

        if ($requesterValidator->fails()) {
            return $this->responseJson(
                false,
                $requesterValidator->errors()->first(),
                [],
                422
            );
        }

        $requesterType =
            $requesterValidator
                ->validated()['requester_type'];


        // =====================================================
        // TENTUKAN NIP / NIM
        // =====================================================

        $identifierType =
            $requesterType === 'mahasiswa'
                ? 'NIM'
                : 'NIP';


        // =====================================================
        // VALIDASI FORM
        // =====================================================

        $validator = Validator::make(
            $request->all(),
            [
                'full_name' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'identifier_value' => [
                    'required',
                    'string',
                    'max:30',
                ],

                'unit_name' => [
                    $requesterType === 'unit_kerja'
                        ? 'required'
                        : 'nullable',
                    'string',
                    'max:150',
                ],

                'phone' => [
                    'required',
                    'string',
                    'max:20',
                ],

                'request_category' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'information_needed' => [
                    'required',
                    'string',
                ],

                'request_reason' => [
                    'required',
                    'string',
                ],

                'priority' => [
                    'nullable',
                    'string',
                    'max:30',
                ],

                'identity_file' => [
                    'nullable',
                    'file',
                    'max:5120',
                    'mimes:jpg,jpeg,png,pdf',
                    'extensions:jpg,jpeg,png,pdf',
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
                'full_name.required' =>
                    'Nama lengkap wajib diisi',

                'identifier_value.required' =>
                    $identifierType . ' wajib diisi',

                'unit_name.required' =>
                    'Nama unit kerja wajib dipilih',

                'phone.required' =>
                    'Nomor telepon wajib diisi',

                'request_category.required' =>
                    'Kategori permintaan wajib dipilih',

                'information_needed.required' =>
                    'Informasi yang dibutuhkan wajib diisi',

                'request_reason.required' =>
                    'Alasan permintaan wajib diisi',

                'priority.string' =>
                    'Prioritas tidak valid',

                'identity_file.file' =>
                    'Terjadi kesalahan saat upload file',

                'identity_file.uploaded' =>
                    'Terjadi kesalahan saat upload file',

                'identity_file.max' =>
                    'Ukuran file maksimal 5 MB',

                'identity_file.mimes' =>
                    'Format file tidak diperbolehkan',

                'identity_file.extensions' =>
                    'Format file tidak diperbolehkan',

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

        $validated = $validator->validated();


        // =====================================================
        // PRIORITAS
        // =====================================================

        $priorityInput =
            trim(
                $validated['priority']
                    ?? 'Tidak Mendesak'
            );

        $priorityKey =
            strtolower(
                str_replace(
                    ' ',
                    '_',
                    $priorityInput
                )
            );

        $priorityMap = [
            'tidak_mendesak' => 'tidak_mendesak',
            'normal' => 'normal',
            'mendesak' => 'mendesak',
        ];

        if (!isset($priorityMap[$priorityKey])) {
            return $this->responseJson(
                false,
                'Prioritas tidak valid',
                [],
                422
            );
        }

        $priority =
            $priorityMap[$priorityKey];


        // =====================================================
        // CARI KATEGORI
        // =====================================================

        $requestCategory =
            trim($validated['request_category']);

        $category =
            RequestCategory::query()
                ->where(
                    'name',
                    $requestCategory
                )
                ->where(
                    'is_active',
                    1
                )
                ->first();

        if (!$category) {
            return $this->responseJson(
                false,
                'Kategori permintaan tidak ditemukan',
                [],
                422
            );
        }


        // =====================================================
        // CARI UNIT
        // =====================================================

        $unitId = null;
        $unitName = null;

        if ($requesterType === 'unit_kerja') {

            $unitName =
                trim($validated['unit_name']);

            $unit =
                Unit::query()
                    ->where(
                        'name',
                        $unitName
                    )
                    ->where(
                        'is_active',
                        1
                    )
                    ->first();

            if (!$unit) {
                return $this->responseJson(
                    false,
                    'Unit kerja tidak ditemukan',
                    [],
                    422
                );
            }

            $unitId =
                $unit->id;
        }


        // =====================================================
        // NILAI DEFAULT
        // =====================================================

        $status =
            'menunggu_verifikasi';

        $estimatedResponse =
            '1-2 Hari Kerja';


        // =====================================================
        // ARRAY FILE
        // =====================================================

        $storedPaths = [];


        // =====================================================
        // TRANSAKSI DATABASE
        // =====================================================

        DB::beginTransaction();

        try {

            // =================================================
            // SIMPAN PERMINTAAN
            // =================================================

            $dataRequest =
                DataRequest::create([
                    'request_number' => null,

                    'requester_type' =>
                        $requesterType,

                    'full_name' =>
                        trim($validated['full_name']),

                    'identifier_type' =>
                        $identifierType,

                    'identifier_value' =>
                        trim(
                            $validated[
                                'identifier_value'
                            ]
                        ),

                    'unit_id' =>
                        $unitId,

                    'phone' =>
                        trim($validated['phone']),

                    'category_id' =>
                        $category->id,

                    'information_needed' =>
                        trim(
                            $validated[
                                'information_needed'
                            ]
                        ),

                    'request_reason' =>
                        trim(
                            $validated[
                                'request_reason'
                            ]
                        ),

                    'priority' =>
                        $priority,

                    'status' =>
                        $status,

                    'estimated_response' =>
                        $estimatedResponse,
                ]);


            // =================================================
            // BUAT NOMOR PERMINTAAN
            // =================================================

            $year =
                Carbon::now(
                    'Asia/Jakarta'
                )->format('Y');

            $runningNumber =
                str_pad(
                    (string) $dataRequest->id,
                    6,
                    '0',
                    STR_PAD_LEFT
                );

            $requestNumber =
                'PD-'
                . $year
                . '-'
                . $runningNumber;


            // =================================================
            // UPDATE NOMOR PERMINTAAN
            // =================================================

            $dataRequest->update([
                'request_number' =>
                    $requestNumber,
            ]);


            // =================================================
            // SIMPAN RIWAYAT STATUS
            // =================================================

            RequestStatusHistory::create([
                'request_id' =>
                    $dataRequest->id,

                'status' =>
                    $status,

                'note' =>
                    'Permintaan berhasil dikirim oleh pengguna',
            ]);


            // =================================================
            // UPLOAD IDENTITAS
            // =================================================

            $this->saveUploadedFile(
                $request,
                'identity_file',
                'identity',
                $dataRequest->id,
                $storedPaths
            );


            // =================================================
            // UPLOAD FILE PENDUKUNG
            // =================================================

            $this->saveUploadedFile(
                $request,
                'support_file',
                'support',
                $dataRequest->id,
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
                'Permintaan data berhasil dikirim',
                [
                    'id' =>
                        $dataRequest->id,

                    'request_number' =>
                        $requestNumber,

                    'requester_type' =>
                        $requesterType,

                    'identifier_type' =>
                        $identifierType,

                    'identifier_value' =>
                        $dataRequest->identifier_value,

                    'unit_name' =>
                        $requesterType === 'unit_kerja'
                            ? $unitName
                            : null,

                    'request_category' =>
                        $requestCategory,

                    'priority' =>
                        $priority,

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
            // LOG ERROR
            // =================================================

            Log::error(
                'DATA REQUEST API ERROR',
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
                'Permintaan data gagal disimpan',
                [],
                500
            );
        }
    }


    /**
     * Simpan file upload.
     */
    private function saveUploadedFile(
        Request $request,
        string $fieldName,
        string $fileType,
        int $requestId,
        array &$storedPaths
    ): void {

        if (!$request->hasFile($fieldName)) {
            return;
        }

        $file =
            $request->file($fieldName);

        if (
            !$file
            || !$file->isValid()
        ) {
            throw new \RuntimeException(
                'Terjadi kesalahan saat upload file'
            );
        }


        // =====================================================
        // FOLDER
        // =====================================================

        $subFolder =
            $fileType === 'identity'
                ? 'identity'
                : 'support';


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
            $fileType
            . '_'
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
        // SIMPAN FILE
        // =====================================================

        $path =
            Storage::disk('public')
                ->putFileAs(
                    'uploads/' . $subFolder,
                    $file,
                    $storedName
                );

        if ($path === false) {
            throw new \RuntimeException(
                'File gagal disimpan ke server'
            );
        }

        $storedPaths[] =
            $path;


        // =====================================================
        // SIMPAN INFORMASI FILE KE DATABASE
        // =====================================================

        RequestFile::create([
            'request_id' =>
                $requestId,

            'file_type' =>
                $fileType,

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
     * Format response JSON agar sama dengan API lama.
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