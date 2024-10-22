<?php

namespace App\Http\Controllers;

use App\Models\CheckHistoryModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckHistoryController extends Controller
{
    public function index(Request $request)
    {
        $pages     = $request->pages;
        $lokasi_id = $request->lokasi_id;

        $history = CheckHistoryModel::query()
            ->selectRaw('
                DATE(created_at) as tanggal,
                COUNT(*) as total_barang,
                SUM(CASE WHEN kondisi = "baik" THEN 1 ELSE 0 END) as total_barang_baik,
                SUM(CASE WHEN kondisi = "rusak ringan" THEN 1 ELSE 0 END) as total_barang_rusak_ringan,
                SUM(CASE WHEN kondisi = "rusak berat" THEN 1 ELSE 0 END) as total_barang_rusak_berat
            ')
            ->where('lokasi_id', $lokasi_id)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('tanggal', 'desc');

        $historyData = $history->paginate($request->perpage ?? 10, ['*'], 'pages', $pages);

        return response()->json([
            'message' => "history lokasi",
            'data' => $historyData,
        ]);
    }

    public function list(Request $request)
    {
        $tanggal   = $request->tanggal;
        $lokasi_id = $request->lokasi_id;

        $details = CheckHistoryModel::with(['barang'])
        ->whereDate('created_at', $tanggal)
        ->where('lokasi_id', $lokasi_id)
        ->orderBy('created_at', 'desc')
        ->get();

        return response()->json([
            'message' => "Detail history",
            'data' => $details,
        ]);
    }
}
