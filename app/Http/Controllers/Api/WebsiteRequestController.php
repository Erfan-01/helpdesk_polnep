<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebsiteRequest;
use App\Models\WebsiteRequestFile;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class WebsiteRequestController extends Controller
{
    public function store(
        Request $request
    ): JsonResponse {

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

                'website_name' => [
                    'required',
                    'string',
                    'max:200',
                ],

                'issue_type' => [
                    'required',
                    'string',

                    Rule::in([
                        'lainnya',
                        'tidak_bisa_login',
                        'error_sistem',
                        'data_tidak_sesuai',
                        'permintaan_akses',
                    ]),
                ],

                'description' => [
                    'required',
                    'string',
                ],

                'support_file' => [
                    'nullable',
                    'file',
                    'max:5120',
                    'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx',
                ],
            ],
            [
                'full_name.required' =>
                    'Nama lengkap wajib diisi',

                'identifier_value.required' =>
                    'NIM/NIP wajib diisi',

                'website_name.required' =>
                    'Pilihan website wajib diisi',

                'issue_type.required' =>
                    'Jenis kendala wajib dipilih',

                'issue_type.in' =>
                    'Jenis kendala tidak valid',

                'description.required' =>
                    'Deskripsi permasalahan wajib diisi',

                'support_file.max' =>
                    'Ukuran file maksimal 5 MB',

                'support_file.mimes' =>
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

        $status =
            'menunggu_verifikasi';

        $estimatedResponse =
            '1-2 Hari Kerja';

        $storedPaths = [];

        DB::beginTransaction();

        try {
            $websiteRequest =
                WebsiteRequest::create([
                    'request_number' => null,

                    'full_name' =>
                        trim(
                            $validated['full_name']
                        ),

                    'identifier_value' =>
                        trim(
                            $validated[
                                'identifier_value'
                            ]
                        ),

                    'website_name' =>
                        trim(
                            $validated[
                                'website_name'
                            ]
                        ),

                    'issue_type' =>
                        $validated['issue_type'],

                    'description' =>
                        trim(
                            $validated['description']
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

            // ===============================================
            // NOMOR TIKET
            // ===============================================

            $year =
                Carbon::now(
                    'Asia/Jakarta'
                )->format('Y');

            $runningNumber =
                str_pad(
                    (string) $websiteRequest->id,
                    6,
                    '0',
                    STR_PAD_LEFT
                );

            $requestNumber =
                'WEB-'
                . $year
                . '-'
                . $runningNumber;

            $websiteRequest->update([
                'request_number' =>
                    $requestNumber,
            ]);

            // ===============================================
            // FILE
            // ===============================================

            $this->saveSupportFile(
                $request,
                $websiteRequest->id,
                $storedPaths
            );

            DB::commit();

            return $this->responseJson(
                true,
                'Permintaan layanan website berhasil dikirim',
                [
                    'id' =>
                        $websiteRequest->id,

                    'request_number' =>
                        $requestNumber,

                    'full_name' =>
                        $websiteRequest->full_name,

                    'identifier_value' =>
                        $websiteRequest
                            ->identifier_value,

                    'website_name' =>
                        $websiteRequest
                            ->website_name,

                    'issue_type' =>
                        $websiteRequest
                            ->issue_type,

                    'status' =>
                        $status,

                    'estimated_response' =>
                        $estimatedResponse,

                    'submitted_at' =>
                        $websiteRequest
                            ->created_at
                            ?->toIso8601String(),
                ],
                201
            );

        } catch (Throwable $e) {
            if (
                DB::transactionLevel() > 0
            ) {
                DB::rollBack();
            }

            foreach (
                $storedPaths as $path
            ) {
                if (
                    Storage::disk('public')
                        ->exists($path)
                ) {
                    Storage::disk('public')
                        ->delete($path);
                }
            }

            Log::error(
                'WEBSITE REQUEST API ERROR',
                [
                    'message' =>
                        $e->getMessage(),
                ]
            );

            return $this->responseJson(
                false,
                'Permintaan layanan website gagal disimpan',
                [],
                500
            );
        }
    }

    private function saveSupportFile(
        Request $request,
        int $requestId,
        array &$storedPaths
    ): void {

        if (
            !$request->hasFile(
                'support_file'
            )
        ) {
            return;
        }

        $file =
            $request->file(
                'support_file'
            );

        if (
            !$file ||
            !$file->isValid()
        ) {
            throw new \RuntimeException(
                'File upload tidak valid'
            );
        }

        $originalName =
            $file->getClientOriginalName();

        $extension =
            strtolower(
                $file->getClientOriginalExtension()
            );

        $storedName =
            'website_'
            . Carbon::now(
                'Asia/Jakarta'
            )->format('Ymd_His')
            . '_'
            . bin2hex(
                random_bytes(5)
            )
            . '.'
            . $extension;

        $path =
            Storage::disk('public')
                ->putFileAs(
                    'uploads/website',
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

        WebsiteRequestFile::create([
            'request_id' =>
                $requestId,

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

            'created_at' =>
                now(),
        ]);
    }

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