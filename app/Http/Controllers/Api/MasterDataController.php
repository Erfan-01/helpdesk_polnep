<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\RequestCategory;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class MasterDataController extends Controller
{
    /**
     * Daftar unit kerja aktif.
     */
    public function units(): JsonResponse
    {
        $units = Unit::query()
            ->where('is_active', 1)
            ->orderBy('name')
            ->get([
                'id',
                'code',
                'name',
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Daftar unit kerja berhasil diambil',
            'data' => $units,
        ]);
    }


    /**
     * Daftar kategori permintaan aktif.
     */
    public function requestCategories(): JsonResponse
    {
        $categories = RequestCategory::query()
            ->where('is_active', 1)
            ->orderBy('id')
            ->get([
                'id',
                'code',
                'name',
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Daftar kategori permintaan berhasil diambil',
            'data' => $categories,
        ]);
    }


    /**
     * Daftar pengumuman yang sudah dipublikasikan.
     */
    public function announcements(): JsonResponse
    {
        $now = Carbon::now('Asia/Jakarta')
            ->format('Y-m-d H:i:s');

        $announcements = Announcement::query()
            ->where('is_active', 1)
            ->where('published_at', '<=', $now)
            ->orderByDesc('published_at')
            ->get([
                'id',
                'title',
                'content',
                'published_at',
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Daftar pengumuman berhasil diambil',
            'data' => $announcements,
        ]);
    }
}