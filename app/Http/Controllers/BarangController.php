<?php

namespace App\Http\Controllers;

use App\Http\Resources\BarangBarcodeResource;
use App\Http\Resources\BarangResource;
use App\Models\Barang;
use App\Models\KategoriBarang;
use App\Models\MetodePenyusutan;
use App\Models\Lokasi;
use App\Models\FotoBarang;
use App\Models\LogHistoryBarang;
use App\Exports\DataBarang;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BarangController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function importDataBarang(Request $request)
    {
        try {
            if ($request->hasFile('document_barang')) {
                
                $file      = $request->file('document_barang');
                $extension = $file->getClientOriginalExtension();

                if ($extension != 'csv' && $extension != 'xlsx') {
                    return response()->json([
                        'message' => 'File harus berupa CSV atau Excel.'
                    ], 400);
                }

                DB::beginTransaction();

                $data    = Excel::toCollection(null, $file);
                $rowData = [];

                for ($i = 1; $i < count($data[0]); $i++) {

                    $row            = $data[0][$i];
                    $barang         = Barang::firstOrNew(['kode_barang' => $row[1]]);
                    $kategori       = KategoriBarang::where('nama_kategori', strtolower($row[8]))->first();
                    $lokasi         = Lokasi::where('nama_lokasi', strtolower($row[9]))->first();
                    $metode         = MetodePenyusutan::where('nama_penyusutan', strtolower($row[10]))->first();
                    $nilai_pertahun = strtolower($row[11]) != "garis lurus" ? (intval($row[4])/2)/intval($row[6]) : (intval($row[4])/intval($row[6]));

                    if (!$barang->exists) {
                        Barang::create([
                            'uuid'                   => Uuid::uuid4(),
                            'nama_barang'            => $row[0],
                            'kode_barang'            => $row[1],
                            'tahun_perolehan'        => $row[2],
                            'kondisi'                => $row[3],
                            'nilai_perolehan'        => $row[4],
                            'nilai_pertahun'         => $nilai_pertahun,
                            'tahun_pembelian'        => $row[5],
                            'masa_manfaat'           => $row[6],
                            'keterangan'             => $row[7],
                            'kategori_barang_id'     => $kategori ? $kategori->id : null,
                            'lokasi_id'              => $lokasi ? $lokasi->id : null,
                            'metode_penyusutan_id'   => $metode ? $metode->id : null,
                            'user_created'           => auth()->user()->id,
                        ]);
                    }
                }

                DB::commit();
                return response()->json([
                    'message' => 'Import Data Barang Success',
                ]);

            } else {

                return response()->json([
                    'message' => 'File tidak di temukan'
                ], 400);
            }

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Import Data Barang Error',
                'error' => $th->getMessage()
            ], 400);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function exportDataBarang(Request $request)
    {
        try {
            $export   = new DataBarang();
            $fileName = 'data_barang.xlsx';

            return Excel::download($export, $fileName);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Export Data Barang Error',
                'error' => $th->getMessage()
            ], 400);
        }
    }

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

    public function barcode()
    {
        $barangs = Barang::all();
        return response()->json([
            'message' => 'List Barang Data',
            'data' => BarangBarcodeResource::collection($barangs),
        ]);
    }

    public function barangByBarcode($barcode)
    {
        $barang = Barang::where('kode_barang', $barcode)->first();
        if ($barang) {
            return response()->json([
                'message' => 'Detail Barang',
                'data' => new BarangResource($barang),
            ]);
        }

        return response()->json([
            'message' => 'Barang Tidak Ditemukan',
            'data' => null,
        ], 400);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $barangs = Barang::query()->paginate(10);
        return response()->json([
            'message' => 'Barang Data',
            'data' => $barangs,
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
                'nilai_pertahun' => $request->nilai_pertahun,
                'tahun_pembelian' => $request->tahun_pembelian,
                'masa_manfaat' => $request->masa_manfaat,
                'keterangan' => $request->keterangan,
                'status' => $request->status,
                'penyusutan_barang' => $request->penyusutan_barang ?? 0,
                'kategori_barang_id' => $request->kategori_barang_id,
                'lokasi_id' => $request->lokasi_id,
                'metode_penyusutan_id' => $request->metode_penyusutan_id ?? 0,
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
        $kategori = Barang::with(['fotoBarang', 'jadwals'])->whereUuid($id)->first();
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
            $newBarang = $barang;
            $barang->update([
                'nama_barang' => $request->nama_barang,
                'kode_barang' => $request->kode_barang,
                'tahun_perolehan' => $request->tahun_perolehan,
                'kondisi' => $request->kondisi,
                'nilai_perolehan' => $request->nilai_perolehan,
                'nilai_pertahun' => $request->nilai_pertahun,
                'tahun_pembelian' => $request->tahun_pembelian,
                'masa_manfaat' => $request->masa_manfaat,
                'keterangan' => $request->keterangan,
                'status' => $request->status,
                'penyusutan_barang' => $request->penyusutan_barang,
                'kategori_barang_id' => $request->kategori_barang_id,
                'lokasi_id' => $request->lokasi_id,
                'metode_penyusutan_id' => $request->metode_penyusutan_id,
            ]);


            if (isset($request->images) && is_array($request->images)) {
                $barang->fotoBarang()->delete();
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

            LogHistoryBarang::create([
                'user_updated' => auth()->user()->id,
                'barang_id' => $barang->id,
                'keterangan' => 'Update Data Barang'
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Update Barang Success',
                'data' => new BarangResource($newBarang),
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
            ], 400);
        }
    }

    function deleteFoto($foto_id)
    {
        $foto = FotoBarang::whereUuid($foto_id);

        if ($foto) {
            $foto->delete();
            return response()->json([
                'message' => 'Delete Photo Success',
            ]);
        }

        return response()->json([
            'message' => 'Delete Photo Error',
        ], 400);
    }
}
