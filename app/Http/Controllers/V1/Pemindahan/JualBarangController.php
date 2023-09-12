<?php

namespace App\Http\Controllers\V1\Pemindahan;

use App\Http\Controllers\Controller;
use App\Http\Resources\PemindahanBarangResource;
use App\Models\PemindahanBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class JualBarangController extends Controller
{
    public function list(Request $request)
    {
        // list all
        $search = $request->search;
        $barangJual =  PemindahanBarang::query();
        if ($search) {
            $barangJual->where(function ($query) use ($search) {
                $query->where('nama_pembeli', 'like', "%$search%");
                $query->orWhere('harga_jual', 'like', "%$search%");
                $query->orWhere('tanggal_jual', 'like', "%$search%");
            });
        }

        $barangJuals = $barangJual->paginate($request->perpage ?? 10);
        return response()->json([
            'status' => 'success',
            'data' => $barangJuals,
            'message' => 'List Jual Barang'
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
            'message' => 'Jual Barang Data',
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
        // store role
        $barangJual = PemindahanBarang::create([
            'uuid' => Uuid::uuid4(),
            'barang_id' => $request->barang_id,
            'user_created' => auth()->user()->id,
            'nama_pembeli' => $request->nama_pembeli,
            'harga_jual' => $request->harga_jual,
            'tanggal_jual' => $request->tanggal_jual,
        ]);

        return response()->json([
            'message' => 'Create Jual Barang Success',
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
            'message' => 'Jual Barang Data',
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
            'nama_pembeli' => $request->nama_pembeli,
            'harga_jual' => $request->harga_jual,
            'tanggal_jual' => $request->tanggal_jual,
        ]);

        return response()->json([
            'message' => 'Update Jual Barang Success',
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
            $jual = PemindahanBarang::whereUuid($id)->first();
            $jual->delete();

            DB::commit();
            return response()->json([
                'message' => 'Delete Jual Barang Success',
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'message' => 'Delete Jual Barang Error',
            ], 400);
        }
    }
}
