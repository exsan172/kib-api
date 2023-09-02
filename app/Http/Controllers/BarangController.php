<?php

namespace App\Http\Controllers;

use App\Http\Resources\BarangResource;
use App\Models\Barang;
use App\Models\FotoBarang;
use App\Models\LogHistoryBarang;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;

class BarangController extends Controller
{
    public function list(Request $request)
    {
        // list all
        $search = $request->search;
        $status = $request->status;
        $kategori_barang_id = $request->kategori_barang_id;
        $lokasi_id = $request->lokasi_id;
        $metode_penyusutan_id = $request->metode_penyusutan_id;
        $barang =  Barang::query();
        if ($search) {
            $barang->where(function ($query) use ($search) {
                $query->where('nama_barang', 'like', "%$search%");
                $query->orWhere('kode_barang', 'like', "%$search%");
                $query->orWhere('tahun_perolehan', 'like', "%$search%");
                $query->orWhere('kondisi', 'like', "%$search%");
                $query->orWhere('nilai_perolehan', 'like', "%$search%");
                $query->orWhere('tahun_pembelian', 'like', "%$search%");
                $query->orWhere('masa_manfaat', 'like', "%$search%");
                $query->orWhere('keterangan', 'like', "%$search%");
                $query->orWhereHas('kategori', function ($query) use ($search) {
                    return  $query->where('nama_kategori', 'like', "%$search%");
                });
            });
        }

        if ($status) {
            $barang->where('status', $status);
        }

        if ($kategori_barang_id) {
            $barang->where('kategori_barang_id', $kategori_barang_id);
        }

        if ($lokasi_id) {
            $barang->where('lokasi_id', $lokasi_id);
        }

        if ($metode_penyusutan_id) {
            $barang->where('metode_penyusutan_id', $metode_penyusutan_id);
        }

        $barangs = $barang->paginate($request->perpage ?? 10);
        return response()->json([
            'status' => 'success',
            'data' => $barangs,
            'message' => 'List barang Barang'
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $barangs = Barang::all();
        return response()->json([
            'message' => 'Barang Data',
            'data' => BarangResource::collection($barangs),
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
        try {
            DB::beginTransaction();
            $barang = Barang::create([
                'uuid' => Uuid::uuid4(),
                'nama_barang' => $request->nama_barang,
                'kode_barang' => $request->kode_barang,
                'tahun_perolehan' => $request->tahun_perolehan,
                'kondisi' => $request->kondisi,
                'nilai_perolehan' => $request->nilai_perolehan,
                'tahun_pembelian' => $request->tahun_pembelian,
                'masa_manfaat' => $request->masa_manfaat,
                'keterangan' => $request->keterangan,
                'status' => $request->status,
                'penyusutan_barang' => $request->penyusutan_barang ?? 0,
                'kategori_barang_id' => $request->kategori_barang_id,
                'lokasi_id' => $request->lokasi_id,
                'metode_penyusutan_id' => $request->metode_penyusutan_id,
                'user_created' => auth()->user()->id,
            ]);

            if (isset($request->images) && is_array($request->images)) {
                $images  = [];
                foreach ($request->images as $image) {
                    $file = Storage::disk('public')->put('barang', $image);
                    $images[] = [
                        'uuid' => Uuid::uuid4(),
                        'barang_id' => $barang->id,
                        'foto_barang' => asset('storage/' . $file),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }

                FotoBarang::insert($images);
            }
            DB::commit();
            return response()->json([
                'message' => 'Create Barang Success',
                'data' => new BarangResource($barang),
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Create Barang Error',
                'data' => '',
                'error' => $th->getMessage()
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $kategori = Barang::with(['fotoBarang'])->whereUuid($id)->first();
        return response()->json([
            'message' => 'Barang Data',
            'data' => new BarangResource($kategori),
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
        try {
            DB::beginTransaction();
            $barang = Barang::whereUuid($id)->first();
            $barang->update([
                'nama_barang' => $request->nama_barang,
                'kode_barang' => $request->kode_barang,
                'tahun_perolehan' => $request->tahun_perolehan,
                'kondisi' => $request->kondisi,
                'nilai_perolehan' => $request->nilai_perolehan,
                'tahun_pembelian' => $request->tahun_pembelian,
                'masa_manfaat' => $request->masa_manfaat,
                'keterangan' => $request->keterangan,
                'status' => $request->status,
                'penyusutan_barang' => $request->penyusutan_barang,
                'kategori_barang_id' => $request->kategori_barang_id,
                'lokasi_id' => $request->lokasi_id,
                'metode_penyusutan_id' => $request->metode_penyusutan_id,
            ]);

            $barang->fotoBarang()->delete();

            if (isset($request->images) && is_array($request->images)) {
                $images  = [];
                foreach ($request->images as $image) {
                    $file = Storage::disk('public')->put('barang', $image);
                    $images[] = [
                        'uuid' => Uuid::uuid4(),
                        'barang_id' => $barang->id,
                        'foto_barang' => asset('storage/' . $file),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }
            }

            FotoBarang::insert($images);

            LogHistoryBarang::create([
                'user_updated' => auth()->user()->id,
                'barang_id' => $barang->id,
                'keterangan' => 'Update Data Barang'
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Update Barang Success',
                'data' => new BarangResource($barang),
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Update Barang Error',
                'data' => '',
                'error' => $th->getMessage(),
                'request' => $request->all()
            ], 400);
        }
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
            $barang = Barang::whereUuid($id)->first();
            $barang->logHistory()->delete();
            $barang->fotoBarang()->delete();
            $barang->delete();
            DB::commit();
            return response()->json([
                'message' => 'Delete Barang Success',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Delete Barang Error',
            ]);
        }
    }
}
