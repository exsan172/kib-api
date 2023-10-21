<?php

namespace App\Http\Controllers\V1\Pemindahan;

use App\Http\Controllers\Controller;
use App\Http\Resources\PemindahanBarangResource;
use App\Models\PemindahanBarang;
use App\Models\Barang;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class TukarBarangController extends Controller
{
    public function list(Request $request)
    {
        // list all
        $search = $request->search;
        $barangJual =  PemindahanBarang::query();
        if ($search) {
            $barangJual->where(function ($query) use ($search) {
                $query->where('nama_penukar', 'like', "%$search%");
                $query->orWhere('barang_yang_ditukar', 'like', "%$search%");
                $query->orWhere('tanggal_tukar', 'like', "%$search%");
            });
        }

        $barangJuals = $barangJual->paginate($request->perpage ?? 10);
        return response()->json([
            'status' => 'success',
            'data' => $barangJuals,
            'message' => 'List Tukar Barang'
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $barangJuales = PemindahanBarang::all();
        return response()->json([
            'message' => 'Tukar Barang Data',
            'data' => PemindahanBarangResource::collection($barangJuales),
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
        $barangJual = PemindahanBarang::create([
            'uuid' => Uuid::uuid4(),
            'barang_id' => $request->barang_id,
            'user_created' => auth()->user()->id,
            'nama_penukar' => $request->nama_penukar,
            'barang_yang_ditukar' => $request->barang_yang_ditukar,
            'tanggal_tukar' => $request->tanggal_tukar,
        ]);

        $dataBarang = Barang::find($request->barang_id);
        $updateStatusBarang = $dataBarang->status = 0;

        return response()->json([
            'message' => 'Create Tukar Barang Success',
            'data' => new PemindahanBarangResource($barangJual),
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
        $barangJual = PemindahanBarang::whereUuid($id)->first();
        return response()->json([
            'message' => 'Tukar Barang Data',
            'data' => new PemindahanBarangResource($barangJual),
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
        $barangJual = PemindahanBarang::whereUuid($id)->first();
        $barangJual->update([
            'barang_id' => $request->barang_id,
            'nama_penukar' => $request->nama_penukar,
            'barang_yang_ditukar' => $request->barang_yang_ditukar,
            'tanggal_tukar' => $request->tanggal_tukar,
        ]);

        return response()->json([
            'message' => 'Update Tukar Barang Success',
            'data' => new PemindahanBarangResource($barangJual),
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
            $tukar = PemindahanBarang::whereUuid($id)->first();
            $tukar->delete();

            DB::commit();
            return response()->json([
                'message' => 'Delete Tukar Barang Success',
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'message' => 'Delete Tukar Barang Error',
            ], 400);
        }
    }
}
