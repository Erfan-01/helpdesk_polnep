<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\TicketCreatedMail;
use App\Models\FacilityRequest;
use App\Models\FacilityRequestFile;
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

class FacilityRequestController extends Controller
{
    /**
     * Simpan laporan fasilitas ruangan.
     */
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

                'building_name' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'floor' => [
                    'required',
                    'string',
                    'max:50',
                ],

                'room_name' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'facility_type' => [
                    'required',
                    'string',

                    Rule::in([
                        'AC',
                        'Kursi / Meja',
                        'Lampu',
                        'Proyektor',
                        'Stop Kontak',
                        'Pintu / Jendela',
                        'Lainnya',
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
                    'Nama wajib diisi',

                'identifier_value.required' =>
                    'NIM/NIP wajib diisi',

                'email.required' =>
                    'Email wajib diisi',

                'email.email' =>
                    'Format email tidak valid',

                'building_name.required' =>
                    'Nama gedung wajib dipilih',

                'floor.required' =>
                    'Lantai wajib diisi',

                'room_name.required' =>
                    'Nama/nomor ruangan wajib diisi',

                'facility_type.required' =>
                    'Jenis fasilitas wajib dipilih',

                'facility_type.in' =>
                    'Jenis fasilitas tidak valid',

                'description.required' =>
                    'Keterangan wajib diisi',

                'support_file.file' =>
                    'File pendukung tidak valid',

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
                $validator
                    ->errors()
                    ->first(),
                [],
                422
            );
        }


        $validated =
            $validator->validated();


        // =====================================================
        // EMAIL
        // =====================================================

        $recipientEmail =
            strtolower(
                trim(
                    $validated['email']
                )
            );


        // =====================================================
        // DEFAULT
        // =====================================================

        $status =
            'menunggu_verifikasi';

        $estimatedResponse =
            '1-2 Hari Kerja';

        $storedPaths = [];


        // =====================================================
        // TRANSAKSI
        // =====================================================

        DB::beginTransaction();


        try {

            // =================================================
            // SIMPAN LAPORAN
            // =================================================

            $facilityRequest =
                FacilityRequest::create([
                    'request_number' =>
                        null,

                    'full_name' =>
                        trim(
                            $validated[
                                'full_name'
                            ]
                        ),

                    'identifier_value' =>
                        trim(
                            $validated[
                                'identifier_value'
                            ]
                        ),

                    'email' =>
                        $recipientEmail,

                    'building_name' =>
                        trim(
                            $validated[
                                'building_name'
                            ]
                        ),

                    'floor' =>
                        trim(
                            $validated[
                                'floor'
                            ]
                        ),

                    'room_name' =>
                        trim(
                            $validated[
                                'room_name'
                            ]
                        ),

                    'facility_type' =>
                        trim(
                            $validated[
                                'facility_type'
                            ]
                        ),

                    'description' =>
                        trim(
                            $validated[
                                'description'
                            ]
                        ),

                    'status' =>
                        $status,

                    'answer' =>
                        null,

                    'estimated_response' =>
                        $estimatedResponse,

                    'resolved_at' =>
                        null,
                ]);


            // =================================================
            // GENERATE NOMOR TIKET
            //
            // FAS-2026-000001
            // =================================================

            $year =
                Carbon::now(
                    'Asia/Jakarta'
                )->format('Y');


            $runningNumber =
                str_pad(
                    (string)
                    $facilityRequest->id,
                    6,
                    '0',
                    STR_PAD_LEFT
                );


            $requestNumber =
                'FAS-'
                . $year
                . '-'
                . $runningNumber;


            // =================================================
            // UPDATE NOMOR TIKET
            // =================================================

            $facilityRequest->update([
                'request_number' =>
                    $requestNumber,
            ]);


            // =================================================
            // UPLOAD FILE
            // =================================================

            if (
                $request->hasFile(
                    'support_file'
                )
            ) {

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


                $originalName =
                    $file
                        ->getClientOriginalName();


                $extension =
                    strtolower(
                        $file
                            ->getClientOriginalExtension()
                    );


                $storedName =
                    'fasilitas_'
                    . Carbon::now(
                        'Asia/Jakarta'
                    )->format(
                        'Ymd_His'
                    )
                    . '_'
                    . bin2hex(
                        random_bytes(5)
                    )
                    . '.'
                    . $extension;


                $path =
                    Storage::disk(
                        'public'
                    )->putFileAs(
                        'uploads/fasilitas-ruangan',
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


                FacilityRequestFile::create([
                    'facility_request_id' =>
                        $facilityRequest->id,

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


            // =================================================
            // COMMIT DATABASE
            // =================================================

            DB::commit();


            // =================================================
            // KIRIM EMAIL TIKET
            // =================================================

            $emailSent = false;

            try {

                Mail::to(
                    $recipientEmail
                )->send(
                    new TicketCreatedMail(
                        fullName:
                            $facilityRequest
                                ->full_name,

                        serviceName:
                            'Fasilitas Ruangan',

                        requestNumber:
                            $requestNumber,

                        status:
                            'Menunggu Verifikasi',

                        estimatedResponse:
                            $estimatedResponse
                    )
                );


                $emailSent = true;


                Log::info(
                    'EMAIL TIKET FASILITAS RUANGAN BERHASIL',
                    [
                        'request_number' =>
                            $requestNumber,

                        'email' =>
                            $recipientEmail,
                    ]
                );

            } catch (
                Throwable $mailException
            ) {

                // Email gagal TIDAK membatalkan laporan.

                Log::warning(
                    'EMAIL TIKET FASILITAS RUANGAN GAGAL',
                    [
                        'request_number' =>
                            $requestNumber,

                        'email' =>
                            $recipientEmail,

                        'message' =>
                            $mailException
                                ->getMessage(),
                    ]
                );
            }


            // =================================================
            // RESPONSE
            // =================================================

            return $this->responseJson(
                true,
                'Laporan fasilitas ruangan berhasil dikirim',
                [
                    'id' =>
                        $facilityRequest->id,

                    'request_number' =>
                        $requestNumber,

                    'full_name' =>
                        $facilityRequest
                            ->full_name,

                    'identifier_value' =>
                        $facilityRequest
                            ->identifier_value,

                    'building_name' =>
                        $facilityRequest
                            ->building_name,

                    'floor' =>
                        $facilityRequest
                            ->floor,

                    'room_name' =>
                        $facilityRequest
                            ->room_name,

                    'facility_type' =>
                        $facilityRequest
                            ->facility_type,

                    'status' =>
                        $status,

                    'estimated_response' =>
                        $estimatedResponse,

                    'submitted_at' =>
                        $facilityRequest
                            ->created_at
                            ?->toIso8601String(),

                    'email_sent' =>
                        $emailSent,
                ],
                201
            );

        } catch (Throwable $e) {

            // =================================================
            // ROLLBACK
            // =================================================

            if (
                DB::transactionLevel() > 0
            ) {
                DB::rollBack();
            }


            // =================================================
            // HAPUS FILE JIKA TRANSAKSI GAGAL
            // =================================================

            foreach (
                $storedPaths
                as $path
            ) {

                if (
                    Storage::disk(
                        'public'
                    )->exists(
                        $path
                    )
                ) {

                    Storage::disk(
                        'public'
                    )->delete(
                        $path
                    );
                }
            }


            // =================================================
            // LOG
            // =================================================

            Log::error(
                'FACILITY REQUEST API ERROR',
                [
                    'message' =>
                        $e->getMessage(),

                    'file' =>
                        $e->getFile(),

                    'line' =>
                        $e->getLine(),
                ]
            );


            return $this->responseJson(
                false,
                'Laporan fasilitas ruangan gagal disimpan',
                [],
                500
            );
        }
    }


    /**
     * Format response JSON.
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