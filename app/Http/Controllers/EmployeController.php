<?php

namespace App\Http\Controllers;

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
            });
        }

        // // Paginate results
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

            $employe = Employe::create([
                'foto' => $request->foto,
                'nama' => $request->nama,
                'nip' => $request->nip,
                'instansi' => $request->instansi,
                'jabatan' => $request->jabatan
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Create employe Success',
                'data' => new EmployeResource($employe),
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Create employe Error',
                'data' => $th,
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
            'message' => 'Employe Data',
            'data' => new EmployeResource($employe),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            DB::beginTransaction();
            $employe = Employe::where('id', $id)->first();

            $employe->update([
                'nama' => $request->nama,
                'nip' => $request->nip,
                'instansi' => $request->instansi,
                'jabatan' => $request->jabatan
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Update employe Success',
                'data' => new EmployeResource($employe)
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Update employe Error',
                'data' => $th,
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
    
            $employe = Employe::where('id', $id)->first();
            
            if ($employe == null) {
                return response()->json([
                    'message' => 'Karyawan tidak ditemukan',
                ], 400);
            }
            
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
