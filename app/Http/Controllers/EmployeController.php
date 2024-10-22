<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Http\Resources\EmployeResource;
use App\Models\Employe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeController extends Controller
{
    public function list(Request $request)
    {
       // Validasi input
        $request->validate([
            'search' => 'nullable|string',
            'perpage' => 'nullable|integer|min:1',
        ]);

        // Ambil parameter dari request
        $search = $request->input('search');
        $perPage = $request->input('perpage', 10); // Default 10 jika perpage tidak ada

        // Query employes
        $employeQuery = Employe::query();

        if ($search) {
            $employeQuery->where(function ($query) use ($search) {
                $query->where('nama', 'like', "%$search%");
                $query->orWhere('nip', 'like', "%$search%");
                $query->orWhere('instansi', 'like', "%$search%");
                $query->orWhere('jabatan', 'like', "%$search%");
            });
        }

        // Urutkan berdasarkan created_at descending (terbaru terlebih dahulu)
        $employeQuery->orderBy('created_at', 'desc');

        // Paginate results
        $employes = $employeQuery->paginate($perPage);

        // Return response
        return response()->json([
            'status' => 'success',
            'data' => $employes,
            'message' => 'List employe'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'foto'     => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'nama'     => 'required|string|max:255',
                'nip'      => 'required|string|max:255',
                'instansi' => 'required|string|max:255',
                'jabatan'  => 'required|string|max:255',
            ]);
            $fotoPath = $request->file('foto')->store('photos', 'public');
            $employe  = Employe::create([
                'foto'      => asset('storage/' . $fotoPath),
                'path'      => $fotoPath,
                'nama'      => $request->nama,
                'nip'       => $request->nip,
                'instansi'  => $request->instansi,
                'jabatan'   => $request->jabatan
            ]);

            DB::commit();
            return response()->json([
                'message'   => 'Create employe Success',
                'data'      => new EmployeResource($employe),
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message'   => 'Create employe Error',
                'data'      => $th,
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $employe = Employe::where('id', $id)->first();
        return response()->json([
            'message'   => 'Employe Data',
            'data'      => new EmployeResource($employe),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            DB::beginTransaction();
            $employe = Employe::findOrFail($id);

            // Validasi data
            $request->validate([
                'nama'      => 'required|string|max:255',
                'nip'       => 'required|string|max:255',
                'instansi'  => 'required|string|max:255',
                'jabatan'   => 'required|string|max:255',
                'foto'      => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
            
            // Update foto jika ada
            if ($request->hasFile('foto')) {
                if ($employe->path && Storage::disk('public')->exists($employe->path)) {
                    Storage::disk('public')->delete($employe->path);
                }

                $fotoPath       = $request->file('foto')->store('photos', 'public');
                $employe->path  = $fotoPath;
                $employe->foto  = asset('storage/' . $fotoPath);
            }

            // Update data employe
            $employe->nama      = $request->nama;
            $employe->nip       = $request->nip;
            $employe->instansi  = $request->instansi;
            $employe->jabatan   = $request->jabatan;
            $employe->save();

            DB::commit();
            return response()->json([
                'message'   => 'Update employe Success',
                'data'      => new EmployeResource($employe)
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message'   => 'Update employe Error',
                'data'      => $th,
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
        
            // Cari employe berdasarkan ID
            $employe = Employe::find($id);
            
            if ($employe == null) {
                return response()->json([
                    'message' => 'Karyawan tidak ditemukan',
                ], 400);
            }

            // Hapus foto dari storage
            if ($employe->path && Storage::disk('public')->exists($employe->path)) {
                Storage::disk('public')->delete($employe->path);
            }
            
            // Hapus employe dari database
            $employe->delete();
            
            DB::commit();
            
            return response()->json([
                'message' => 'Delete Karyawan Success',
            ], 200);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Delete Karyawan Error',
                'error' => $th->getMessage()
            ], 400);
        }
    }
}
