<?php

namespace App\Http\Controllers\V1\Pemindahan;

use App\Http\Controllers\Controller;
use App\Http\Resources\PemindahanBarangResource;
use App\Models\PemindahanBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class PemusnahanBarangController extends Controller
{
    public function list(Request $request)
    {
        // list all
        $search = $request->search;
        $barangPemusnahan =  PemindahanBarang::query();
        if ($search) {
            $barangPemusnahan->where(function ($query) use ($search) {
                $query->where('alasan_pemusnahan', 'like', "%$search%");
            });
        }

        $barangPemusnahans = $barangPemusnahan->paginate($request->perpage ?? 10);
        return response()->json([
            'status' => 'success',
            'data' => $barangPemusnahans,
            'message' => 'List Pemusnahan Barang'
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $barangPemusnahanes = PemindahanBarang::all();
        return response()->json([
            'message' => 'Pemusnahan Barang Data',
            'data' => PemindahanBarangResource::collection($barangPemusnahanes),
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
        $barangPemusnahan = PemindahanBarang::create([
            'uuid' => Uuid::uuid4(),
            'barang_id' => $request->barang_id,
            'user_created' => auth()->user()->id,
            'alasan_pemusnahan' => $request->alasan_pemusnahan,
            'tanggal_pemusnahan' => $request->tanggal_pemusnahan
        ]);

        return response()->json([
            'message' => 'Create Pemusnahan Barang Success',
            'data' => new PemindahanBarangResource($barangPemusnahan),
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
        $barangPemusnahan = PemindahanBarang::whereUuid($id)->first();
        return response()->json([
            'message' => 'Pemusnahan Barang Data',
            'data' => new PemindahanBarangResource($barangPemusnahan),
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
        $barangPemusnahan = PemindahanBarang::whereUuid($id)->first();
        $barangPemusnahan->update([
            'barang_id' => $request->barang_id,
            'alasan_pemusnahan' => $request->alasan_pemusnahan,
            'tanggal_pemusnahan' => $request->tanggal_pemusnahan
        ]);

        return response()->json([
            'message' => 'Update Pemusnahan Barang Success',
            'data' => new PemindahanBarangResource($barangPemusnahan),
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
            $pemusnahan = PemindahanBarang::whereUuid($id)->first();
            $pemusnahan->delete();

            DB::commit();
            return response()->json([
                'message' => 'Delete Pemusnahan Barang Success',
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'message' => 'Delete Pemusnahan Barang Error',
            ], 400);
        }
    }
}
