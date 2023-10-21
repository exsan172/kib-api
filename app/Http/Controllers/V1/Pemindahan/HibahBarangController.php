<?php

namespace App\Http\Controllers\V1\Pemindahan;

use App\Http\Controllers\Controller;
use App\Http\Resources\PemindahanBarangResource;
use App\Models\PemindahanBarang;
use App\Models\Barang;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class HibahBarangController extends Controller
{
    public function list(Request $request)
    {
        // list all
        $search = $request->search;
        $barangHibah =  PemindahanBarang::query();
        if ($search) {
            $barangHibah->where(function ($query) use ($search) {
                $query->where('nama_penerima', 'like', "%$search%");
            });
        }

        $barangHibahs = $barangHibah->paginate($request->perpage ?? 10);
        return response()->json([
            'status' => 'success',
            'data' => $barangHibahs,
            'message' => 'List Hibah Barang'
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $barangHibahes = PemindahanBarang::all();
        return response()->json([
            'message' => 'Hibah Barang Data',
            'data' => PemindahanBarangResource::collection($barangHibahes),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // check barang
        $cekBarang = Barang::find($request->barang_id);
        if($cekBarang == null) {
            return response()->json([
                'message' => 'Barang tidak ditemukan',
            ], 400);
        }

        // store role
        $barangHibah = PemindahanBarang::create([
            'uuid' => Uuid::uuid4(),
            'barang_id' => $request->barang_id,
            'user_created' => auth()->user()->id,
            'nama_penerima' => $request->nama_penerima,
            'tanggal_diterima' => $request->tanggal_diterima
        ]);

        $updateStatusBarang = $cekBarang->status = 0;
        
        return response()->json([
            'message' => 'Create Hibah Barang Success',
            'data' => new PemindahanBarangResource($barangHibah),
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $barangHibah = PemindahanBarang::whereUuid($id)->first();
        return response()->json([
            'message' => 'Hibah Barang Data',
            'data' => new PemindahanBarangResource($barangHibah),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $barangHibah = PemindahanBarang::whereUuid($id)->first();
        $barangHibah->update([
            'barang_id' => $request->barang_id,
            'nama_penerima' => $request->nama_penerima,
            'tanggal_diterima' => $request->tanggal_diterima,
        ]);

        return response()->json([
            'message' => 'Update Hibah Barang Success',
            'data' => new PemindahanBarangResource($barangHibah),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $kategori = PemindahanBarang::whereUuid($id)->first();
            $kategori->delete();

            DB::commit();
            return response()->json([
                'message' => 'Delete Hibah Barang Success',
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'message' => 'Delete Hibah Barang Error',
            ], 400);
        }
    }
}
