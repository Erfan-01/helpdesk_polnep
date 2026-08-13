<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DataRequest;
use App\Models\EmployeeQuestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StatusController extends Controller
{
    /**
     * Cek status Permintaan Data.
     */
    public function requestStatus(Request $request): JsonResponse
    {
        $validator = Validator::make(
            $request->all(),
            [
                'request_number' => [
                    'required',
                    'string',
                    'max:30',
                ],

                'identifier_value' => [
                    'required',
                    'string',
                    'max:30',
                ],
            ],
            [
                'request_number.required' =>
                    'Nomor permintaan wajib diisi',

                'identifier_value.required' =>
                    'NIP/NIM wajib diisi',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' =>
                    $validator->errors()->first(),
                'data' => [],
            ], 422);
        }

        $validated = $validator->validated();


        // =====================================================
        // CARI PERMINTAAN
        // =====================================================

        $dataRequest = DataRequest::query()
            ->with([
                'unit:id,name',
                'category:id,name',
                'statusHistories' => function ($query) {
                    $query->orderBy('created_at');
                },
            ])
            ->where(
                'request_number',
                trim($validated['request_number'])
            )
            ->where(
                'identifier_value',
                trim($validated['identifier_value'])
            )
            ->first();


        if (!$dataRequest) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Permintaan tidak ditemukan atau NIP/NIM tidak sesuai',
                'data' => [],
            ], 404);
        }


        // =====================================================
        // RIWAYAT STATUS
        // =====================================================

        $history = $dataRequest
            ->statusHistories
            ->map(function ($item) {

                return [
                    'status' =>
                        $item->status,

                    'note' =>
                        $item->note,

                    'created_at' =>
                        $item->created_at
                            ? $item->created_at
                                ->format('Y-m-d H:i:s')
                            : null,
                ];
            })
            ->values();


        // =====================================================
        // RESPONSE
        // =====================================================

        return response()->json([
            'success' => true,
            'message' =>
                'Status permintaan berhasil ditemukan',

            'data' => [
                'id' =>
                    $dataRequest->id,

                'request_number' =>
                    $dataRequest->request_number,

                'requester_type' =>
                    $dataRequest->requester_type,

                'full_name' =>
                    $dataRequest->full_name,

                'identifier_type' =>
                    $dataRequest->identifier_type,

                'identifier_value' =>
                    $dataRequest->identifier_value,

                'unit_name' =>
                    $dataRequest->unit?->name,

                'request_category' =>
                    $dataRequest->category?->name,

                'information_needed' =>
                    $dataRequest->information_needed,

                'request_reason' =>
                    $dataRequest->request_reason,

                'priority' =>
                    $dataRequest->priority,

                'status' =>
                    $dataRequest->status,

                'estimated_response' =>
                    $dataRequest->estimated_response,

                'created_at' =>
                    $dataRequest->created_at
                        ? $dataRequest->created_at
                            ->format('Y-m-d H:i:s')
                        : null,

                'updated_at' =>
                    $dataRequest->updated_at
                        ? $dataRequest->updated_at
                            ->format('Y-m-d H:i:s')
                        : null,

                'history' =>
                    $history,
            ],
        ]);
    }


    /**
     * Cek status Pertanyaan Kepegawaian.
     */
    public function employeeQuestionStatus(
        Request $request
    ): JsonResponse {

        $validator = Validator::make(
            $request->all(),
            [
                'question_number' => [
                    'required',
                    'string',
                    'max:30',
                ],

                'nip' => [
                    'required',
                    'string',
                    'max:30',
                ],
            ],
            [
                'question_number.required' =>
                    'Nomor pertanyaan wajib diisi',

                'nip.required' =>
                    'NIP wajib diisi',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' =>
                    $validator->errors()->first(),
                'data' => [],
            ], 422);
        }

        $validated = $validator->validated();


        // =====================================================
        // CARI PERTANYAAN
        // =====================================================

        $question = EmployeeQuestion::query()
            ->with([
                'unit:id,name',
            ])
            ->where(
                'question_number',
                trim($validated['question_number'])
            )
            ->where(
                'nip',
                trim($validated['nip'])
            )
            ->first();


        if (!$question) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Pertanyaan tidak ditemukan atau NIP tidak sesuai',
                'data' => [],
            ], 404);
        }


        // =====================================================
        // RESPONSE
        // =====================================================

        return response()->json([
            'success' => true,
            'message' =>
                'Status pertanyaan berhasil ditemukan',

            'data' => [
                'id' =>
                    $question->id,

                'question_number' =>
                    $question->question_number,

                'user_category' =>
                    $question->user_category,

                'unit_name' =>
                    $question->unit?->name,

                'full_name' =>
                    $question->full_name,

                'nip' =>
                    $question->nip,

                'email' =>
                    $question->email,

                'phone' =>
                    $question->phone,

                'question_title' =>
                    $question->question_title,

                'question_content' =>
                    $question->question_content,

                'status' =>
                    $question->status,

                'answer' =>
                    $question->answer,

                'estimated_response' =>
                    $question->estimated_response,

                'answered_at' =>
                    $question->answered_at
                        ? $question->answered_at
                            ->format('Y-m-d H:i:s')
                        : null,

                'created_at' =>
                    $question->created_at
                        ? $question->created_at
                            ->format('Y-m-d H:i:s')
                        : null,

                'updated_at' =>
                    $question->updated_at
                        ? $question->updated_at
                            ->format('Y-m-d H:i:s')
                        : null,
            ],
        ]);
    }
}