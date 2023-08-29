<?php

namespace App\Http\Controllers\V1\Master;

use App\Http\Controllers\Controller;
use App\Http\Resources\KategoriBarangResource;
use App\Models\KategoriBarang;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class KategoriBarangController extends Controller
{

    public function list(Request $request)
    {
        // list all
        $search = $request->search;
        $status = $request->status;
        $kategori =  KategoriBarang::query();
        if ($search) {
            $kategori->where(function ($query) use ($search) {
                $query->where('nama_kategori', 'like', "%$search%");
            });
        }

        if ($status) {
            $kategori->where('status', $status);
        }

        $kategoris = $kategori->paginate($request->perpage ?? 10);
        return response()->json([
            'status' => 'success',
            'data' => $kategoris,
            'message' => 'List Kategori Barang'
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $kategories = KategoriBarang::all();
        return response()->json([
            'message' => 'Category Barang Data',
            'data' => KategoriBarangResource::collection($kategories),
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
        $kategori = KategoriBarang::create([
            'uuid' => Uuid::uuid4(),
            'nama_kategori' => $request->nama_kategori,
        ]);

        return response()->json([
            'message' => 'Create Category Barang Success',
            'data' => new KategoriBarangResource($kategori),
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
        $kategori = KategoriBarang::whereUuid($id)->first();
        return response()->json([
            'message' => 'Category Barang Data',
            'data' => new KategoriBarangResource($kategori),
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
        $kategori = KategoriBarang::whereUuid($id)->first();
        $kategori->update([
            'nama_kategori' => $request->nama_kategori,
        ]);

        return response()->json([
            'message' => 'Update Category Barang Success',
            'data' => new KategoriBarangResource($kategori),
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
        $kategori = KategoriBarang::whereUuid($id)->first();
        $kategori->delete();

        return response()->json([
            'message' => 'Delete Category Barang Success',
        ]);
    }
}
