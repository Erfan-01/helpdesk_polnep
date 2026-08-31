<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\TicketCreatedMail;
use App\Models\WebsiteRequest;
use App\Models\WebsiteRequestFile;
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

                'email' => [
                    'required',
                    'email:rfc',
                    'max:150',
                ],

                'website_name' => [
                    'required',
                    'string',
                    'max:200',
                ],

                'issue_type' => [
                    'required',
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

                'website_name.required' =>
                    'Website wajib dipilih',

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

            $websiteRequest =
                WebsiteRequest::create([
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

                    'website_name' =>
                        trim(
                            $validated['website_name']
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


            $year =
                Carbon::now(
                    'Asia/Jakarta'
                )->format('Y');

            $runningNumber =
                str_pad(
                    (string)
                    $websiteRequest->id,
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
                    'website_request_id' =>
                        $websiteRequest->id,

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
                    $websiteRequest->email
                )->send(
                    new TicketCreatedMail(
                        fullName:
                            $websiteRequest->full_name,

                        serviceName:
                            'Layanan Website',

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
                    'EMAIL TIKET WEBSITE GAGAL',
                    [
                        'request_number' =>
                            $requestNumber,

                        'email' =>
                            $websiteRequest->email,

                        'message' =>
                            $mailException
                                ->getMessage(),
                    ]
                );
            }


            return $this->responseJson(
                true,
                'Permintaan website berhasil dikirim',
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
                'WEBSITE REQUEST API ERROR',
                [
                    'message' =>
                        $e->getMessage(),
                ]
            );


            return $this->responseJson(
                false,
                'Permintaan website gagal disimpan',
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