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
use Illuminate\Support\Facades\Storage;

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
        
        $lokasi->orderBy('created_at', 'desc');
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
    public function index(Request $request)
    {
        $kode_lokasi = $request->kode_lokasi;
        if($kode_lokasi) {

            $lokasi = Lokasi::where('kode_lokasi', $kode_lokasi)->first();
            return response()->json([
                'message' => "data lokasi",
                'data' => $lokasi,
            ]);

        } else {

            $lokasis = Lokasi::all();
            return response()->json([
                'message' => "data lokasi",
                'data' => $lokasis,
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $validatedData = $request->validate([
                'nama_lokasi' => 'required|string|max:255',
                'kode_lokasi' => 'required|string|max:255|unique:lokasi,kode_lokasi',
                'parent_id' => 'nullable|integer',
                'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
            ]);

            $existingLocation = Lokasi::where('kode_lokasi', $request->kode_lokasi)->first();
            if ($existingLocation) {
                throw ValidationException::withMessages(['kode_lokasi' => ['Kode lokasi sudah digunakan']]);
            }

            $fotoPath = $request->file('foto')->store('lokasi', 'public');
            $lokasi = Lokasi::create([
                'uuid' => Uuid::uuid4(),
                'nama_lokasi' => $request->nama_lokasi,
                'kode_lokasi' => $request->kode_lokasi,
                'parent_id' => $request->parent_id,
                'path' => $fotoPath,
                'foto' => asset('storage/' . $fotoPath)
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

            // Update foto jika ada
            if ($request->hasFile('foto')) {
                if ($lokasi->path && Storage::disk('public')->exists($lokasi->path)) {
                    Storage::disk('public')->delete($lokasi->path);
                }

                $fotoPath      = $request->file('foto')->store('lokasi', 'public');
                $lokasi->path  = $fotoPath;
                $lokasi->foto  = asset('storage/' . $fotoPath);
            }

            $lokasi->update([
                'uuid' => Uuid::uuid4(),
                'nama_lokasi' => $request->nama_lokasi,
                'kode_lokasi' => $request->kode_lokasi,
                'parent_id' => $request->parent_id,
            ]);

            DB::commit();
            return response()->json([
                'message' => 'update lokasi Success',
                'data' => new LokasiResource($lokasi),
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'update lokasi Error',
                'data' => $th->getMessage()
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
                if ($lokasi->path && Storage::disk('public')->exists($lokasi->path)) {
                    Storage::disk('public')->delete($lokasi->path);
                }
                
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
