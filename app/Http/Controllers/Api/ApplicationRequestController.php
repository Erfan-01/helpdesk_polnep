<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\TicketCreatedMail;
use App\Models\ApplicationRequest;
use App\Models\ApplicationRequestFile;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class ApplicationRequestController extends Controller
{
    public function store(
        Request $request
    ): JsonResponse {

        // =====================================================
        // VALIDASI
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

                'email' => [
                    'required',
                    'email:rfc',
                    'max:150',
                ],

                'application_name' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'issue_type' => [
                    'required',
                    Rule::in([
                        'tidak_bisa_login',
                        'data_tidak_sesuai',
                        'error_sistem',
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
                    'extensions:jpg,jpeg,png,pdf,doc,docx,xls,xlsx',
                ],
            ],
            [
                'full_name.required' =>
                    'Nama lengkap wajib diisi',

                'identifier_value.required' =>
                    'NIM/NIP wajib diisi',

                'email.required' =>
                    'Email wajib diisi',

                'email.email' =>
                    'Format email tidak valid',

                'application_name.required' =>
                    'Aplikasi wajib dipilih',

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

        $status =
            'menunggu_verifikasi';

        $estimatedResponse =
            '1-2 Hari Kerja';

        $storedPaths = [];

        DB::beginTransaction();


        try {

            // =================================================
            // SIMPAN
            // =================================================

            $applicationRequest =
                ApplicationRequest::create([
                    'request_number' =>
                        null,

                    'full_name' =>
                        trim(
                            $validated['full_name']
                        ),

                    'identifier_value' =>
                        trim(
                            $validated['identifier_value']
                        ),

                    'email' =>
                        strtolower(
                            trim(
                                $validated['email']
                            )
                        ),

                    'application_name' =>
                        trim(
                            $validated['application_name']
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


            // =================================================
            // NOMOR TIKET
            // =================================================

            $year =
                Carbon::now(
                    'Asia/Jakarta'
                )->format('Y');

            $runningNumber =
                str_pad(
                    (string)
                    $applicationRequest->id,
                    6,
                    '0',
                    STR_PAD_LEFT
                );

            $requestNumber =
                'APP-'
                . $year
                . '-'
                . $runningNumber;


            $applicationRequest->update([
                'request_number' =>
                    $requestNumber,
            ]);


            // =================================================
            // FILE
            // =================================================

            if ($request->hasFile('support_file')) {

                $file =
                    $request->file(
                        'support_file'
                    );

                if (
                    !$file
                    || !$file->isValid()
                ) {
                    throw new \RuntimeException(
                        'Terjadi kesalahan saat upload file'
                    );
                }


                $extension =
                    strtolower(
                        $file
                            ->getClientOriginalExtension()
                    );

                $storedName =
                    'aplikasi_'
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
                            'uploads/aplikasi',
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


                ApplicationRequestFile::create([
                    'application_request_id' =>
                        $applicationRequest->id,

                    'original_name' =>
                        $file
                            ->getClientOriginalName(),

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


            DB::commit();


            // =================================================
            // EMAIL
            // =================================================

            $emailSent = false;

            try {

                Mail::to(
                    $applicationRequest->email
                )->send(
                    new TicketCreatedMail(
                        fullName:
                            $applicationRequest->full_name,

                        serviceName:
                            'Layanan Aplikasi',

                        requestNumber:
                            $requestNumber,

                        status:
                            'Menunggu Verifikasi',

                        estimatedResponse:
                            $estimatedResponse
                    )
                );

                $emailSent = true;

            } catch (Throwable $mailException) {

                Log::warning(
                    'EMAIL TIKET APLIKASI GAGAL',
                    [
                        'request_number' =>
                            $requestNumber,

                        'email' =>
                            $applicationRequest->email,

                        'message' =>
                            $mailException
                                ->getMessage(),
                    ]
                );
            }


            return $this->responseJson(
                true,
                'Permintaan aplikasi berhasil dikirim',
                [
                    'id' =>
                        $applicationRequest->id,

                    'request_number' =>
                        $requestNumber,

                    'full_name' =>
                        $applicationRequest->full_name,

                    'identifier_value' =>
                        $applicationRequest
                            ->identifier_value,

                    'application_name' =>
                        $applicationRequest
                            ->application_name,

                    'issue_type' =>
                        $applicationRequest
                            ->issue_type,

                    'status' =>
                        $status,

                    'estimated_response' =>
                        $estimatedResponse,

                    'submitted_at' =>
                        $applicationRequest
                            ->created_at
                            ?->toIso8601String(),

                    'email_sent' =>
                        $emailSent,
                ],
                201
            );

        } catch (Throwable $e) {

            if (
                DB::transactionLevel() > 0
            ) {
                DB::rollBack();
            }


            foreach ($storedPaths as $path) {

                if (
                    Storage::disk('public')
                        ->exists($path)
                ) {
                    Storage::disk('public')
                        ->delete($path);
                }
            }


            Log::error(
                'APPLICATION REQUEST API ERROR',
                [
                    'message' =>
                        $e->getMessage(),
                ]
            );


            return $this->responseJson(
                false,
                'Permintaan aplikasi gagal disimpan',
                [],
                500
            );
        }
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