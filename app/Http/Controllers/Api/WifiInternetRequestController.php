<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WifiInternetRequest;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

class WifiInternetRequestController extends Controller
{
    /**
     * Simpan laporan kendala Wifi / Internet.
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

                'building_name.required' =>
                    'Nama gedung wajib dipilih',

                'room_name.required' =>
                    'Ruangan wajib diisi',

                'description.required' =>
                    'Deskripsi keluhan wajib diisi',
            ]
        );


        // =====================================================
        // VALIDASI GAGAL
        // =====================================================

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
        // DEFAULT
        // =====================================================

        $status =
            'menunggu_verifikasi';

        $estimatedResponse =
            '1-2 Hari Kerja';


        // =====================================================
        // TRANSAKSI
        // =====================================================

        DB::beginTransaction();

        try {

            // =================================================
            // SIMPAN LAPORAN
            // =================================================

            $wifiRequest =
                WifiInternetRequest::create([
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

                    'building_name' =>
                        trim(
                            $validated[
                                'building_name'
                            ]
                        ),

                    'room_name' =>
                        trim(
                            $validated[
                                'room_name'
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
            // NET-2026-000001
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


            // =================================================
            // UPDATE NOMOR TIKET
            // =================================================

            $wifiRequest->update([
                'request_number' =>
                    $requestNumber,
            ]);


            // =================================================
            // COMMIT
            // =================================================

            DB::commit();


            // =================================================
            // RESPONSE SUKSES
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
                        $wifiRequest
                            ->full_name,

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
            // LOG ERROR
            // =================================================

            Log::error(
                'WIFI INTERNET API ERROR',
                [
                    'message' =>
                        $e->getMessage(),
                ]
            );


            // =================================================
            // RESPONSE ERROR
            // =================================================

            return $this->responseJson(
                false,
                'Keluhan Wifi / Internet gagal disimpan',
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