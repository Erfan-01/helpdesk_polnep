<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\TicketCreatedMail;
use App\Models\WifiInternetRequest;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Throwable;

class WifiInternetRequestController extends Controller
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

                'building_name' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'room_name' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'description' => [
                    'required',
                    'string',
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

                'room_name.required' =>
                    'Ruangan wajib diisi',

                'description.required' =>
                    'Deskripsi keluhan wajib diisi',
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


        DB::beginTransaction();


        try {

            // =================================================
            // SIMPAN
            // =================================================

            $wifiRequest =
                WifiInternetRequest::create([
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

                    'building_name' =>
                        trim(
                            $validated['building_name']
                        ),

                    'room_name' =>
                        trim(
                            $validated['room_name']
                        ),

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

                    'resolved_at' =>
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
                    $wifiRequest->id,
                    6,
                    '0',
                    STR_PAD_LEFT
                );

            $requestNumber =
                'NET-'
                . $year
                . '-'
                . $runningNumber;


            $wifiRequest->update([
                'request_number' =>
                    $requestNumber,
            ]);


            DB::commit();


            // =================================================
            // KIRIM EMAIL
            // =================================================

            $emailSent = false;

            try {

                Mail::to(
                    $wifiRequest->email
                )->send(
                    new TicketCreatedMail(
                        fullName:
                            $wifiRequest->full_name,

                        serviceName:
                            'Wifi / Internet',

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
                    'EMAIL TIKET WIFI INTERNET GAGAL',
                    [
                        'request_number' =>
                            $requestNumber,

                        'email' =>
                            $wifiRequest->email,

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
                'Keluhan Wifi / Internet berhasil dikirim',
                [
                    'id' =>
                        $wifiRequest->id,

                    'request_number' =>
                        $requestNumber,

                    'full_name' =>
                        $wifiRequest->full_name,

                    'identifier_value' =>
                        $wifiRequest
                            ->identifier_value,

                    'building_name' =>
                        $wifiRequest
                            ->building_name,

                    'room_name' =>
                        $wifiRequest
                            ->room_name,

                    'description' =>
                        $wifiRequest
                            ->description,

                    'status' =>
                        $status,

                    'estimated_response' =>
                        $estimatedResponse,

                    'submitted_at' =>
                        $wifiRequest
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


            Log::error(
                'WIFI INTERNET API ERROR',
                [
                    'message' =>
                        $e->getMessage(),
                ]
            );


            return $this->responseJson(
                false,
                'Keluhan Wifi / Internet gagal disimpan',
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