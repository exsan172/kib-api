<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Barang;
use App\Models\KategoriBarang;

class DashboardController extends Controller
{
    public function dashboardData(Request $request)
    {
        try {
            $dataTotalBarang        = Barang::count();
            $totalBarangRusakRingan = Barang::where('kondisi', 'rusak ringan')->count();
            $totalBarangRusakBerat  = Barang::where('kondisi', 'rusak berat')->count();
            $totalPenguna           = User::count();
            $totalKategori          = KategoriBarang::count();
            
            $dataResponse     = [
                'total_barang_rusak_berat'  =>  $totalBarangRusakBerat,
                'total_barang_rusak_ringan' =>  $totalBarangRusakRingan,
                'total_barang'              =>  $dataTotalBarang,
                'total_penguna'             =>  $totalPenguna,
                'total_kategori'            =>  $totalKategori,
            ];

            return response()->json([
                'message' => 'Data Dashboard berhasil di hitung !',
                'data'    => $dataResponse
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Gagal menghitung data !',
                'error' => $th->getMessage()
            ], 400);
        }
    }
}
