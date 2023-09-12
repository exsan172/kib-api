<?php

namespace App\Http\Controllers;

use App\Http\Resources\JadwalPengecekanResource;
use App\Models\JadwalPengecekan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class JadwalPengecekanController extends Controller
{
    public function list(Request $request)
    {
        // list all
        $search = $request->search;
        $status = $request->status;
        $jadwal =  JadwalPengecekan::query();
        if ($search) {
            $jadwal->where(function ($query) use ($search) {
                $query->where('judul', 'like', "%$search%");
                $query->orWhere('keterangan', 'like', "%$search%");
            });
        }

        if ($status) {
            $jadwal->where('status', $status);
        }

        $jadwals = $jadwal->paginate($request->perpage ?? 10);
        return response()->json([
            'status' => 'success',
            'data' => $jadwals,
            'message' => 'List jadwal Barang'
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $jadwales = JadwalPengecekan::all();
        return response()->json([
            'message' => 'Jadwal pengecekan Data',
            'data' => JadwalPengecekanResource::collection($jadwales),
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
        $jadwal = JadwalPengecekan::create([
            'uuid' => Uuid::uuid4(),
            'judul' => $request->judul,
            'keterangan' => $request->keterangan,
            'tanggal' => $request->tanggal,
            'barang_id' => $request->barang_id,
            'user_created' => auth()->user()->id,
        ]);

        return response()->json([
            'message' => 'Create Jadwal pengecekan Success',
            'data' => new JadwalPengecekanResource($jadwal),
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
        $jadwal = JadwalPengecekan::whereUuid($id)->first();
        return response()->json([
            'message' => 'Jadwal pengecekan Data',
            'data' => new JadwalPengecekanResource($jadwal),
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
        $jadwal = JadwalPengecekan::whereUuid($id)->first();
        $jadwal->update([
            'judul' => $request->judul,
            'keterangan' => $request->keterangan,
            'tanggal' => $request->tanggal,
            'barang_id' => $request->barang_id,
        ]);

        return response()->json([
            'message' => 'Update Jadwal pengecekan Success',
            'data' => new JadwalPengecekanResource($jadwal),
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
            $jadwal = JadwalPengecekan::whereUuid($id)->first();
            $jadwal->delete();
            DB::commit();
            return response()->json([
                'message' => 'Delete Jadwal pengecekan Success',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Delete Jadwal pengecekan Error',
            ], 400);
        }
    }
}
