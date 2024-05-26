<?php

namespace App\Http\Controllers;

use App\Http\Resources\LokasiResource;
use App\Models\Barang;
use App\Models\Lokasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LokasiController extends Controller
{
    public function list(Request $request)
    {
        // list all
        $search = $request->search;

        $lokasi =  Lokasi::query()->with('children');
        if ($search) {
            $lokasi->where(function ($query) use ($search) {
                $query->where('nama_lokasi', 'like', "%$search%");
            });
        }

        $lokasis = $lokasi->whereNull('parent_id')->paginate($request->perpage ?? 10);
        return response()->json([
            'status' => 'success',
            'data' => $lokasis,
            'message' => 'List lokasi'
        ]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $lokasis = Lokasi::all();
        return response()->json([
            'message' => 'Lokasi Data',
            'data' => $lokasis,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $existingLocation = Lokasi::where('kode_lokasi', $request->kode_lokasi)->first();
            if ($existingLocation) {
                throw ValidationException::withMessages(['kode_lokasi' => ['Kode lokasi sudah digunakan']]);
            }

            $lokasi = Lokasi::create([
                'uuid' => Uuid::uuid4(),
                'nama_lokasi' => $request->nama_lokasi,
                'kode_lokasi' => $request->kode_lokasi,
                'parent_id' => $request->parent_id,
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Create lokasi Success',
                'data' => new LokasiResource($lokasi),
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Create lokasi Error',
                'data' => $th,
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $lokasi = Lokasi::whereUuid($id)->first();
        return response()->json([
            'message' => 'Lokasi Data',
            'data' => new LokasiResource($lokasi),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            DB::beginTransaction();
            $lokasi = Lokasi::whereUuid($id)->first();

            $request->validate([
                'kode_lokasi' => [
                    'required',
                    'string',
                    Rule::unique('lokasi', 'kode_lokasi')->ignore($lokasi->id),
                ]
            ]);

            $lokasi->update([
                'uuid' => Uuid::uuid4(),
                'nama_lokasi' => $request->nama_lokasi,
                'kode_lokasi' => $request->kode_lokasi,
                'parent_id' => $request->parent_id,

            ]);

            DB::commit();
            return response()->json([
                'message' => 'Create lokasi Success',
                'data' => new LokasiResource($lokasi),
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Create lokasi Error',
                'data' => '',
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();
            $lokasi = Lokasi::where('uuid', $id)->first();
            if($lokasi == null) {
                return response()->json([
                    'message' => 'Lokasi tidak di temukan',
                ], 400);
            }

            $barang = Barang::where('lokasi_id', $lokasi->id)->first();
            if ($barang) {
                return response()->json([
                    'message' => 'Lokasi Gagal Dihapus, karna masih memiliki barang',
                ], 400);
            } else {
                $lokasi->delete();
            }

            DB::commit();
            return response()->json([
                'message' => 'Delete lokasi Success',
            ], 200);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Delete lokasi Error',
                'error' => $th->getMessage()
            ], 400);
        }
    }
}
