<?php

namespace App\Http\Controllers\V1\Master;

use App\Http\Controllers\Controller;
use App\Http\Resources\MetodePenyusutanResource;
use App\Models\MetodePenyusutan;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class MetodePenyusutanController extends Controller
{
    public function list(Request $request)
    {
        // list all
        $search = $request->search;
        $status = $request->status;
        $metodePenyusutan =  MetodePenyusutan::query();
        if ($search) {
            $metodePenyusutan->where(function ($query) use ($search) {
                $query->where('nama_penyusutan', 'like', "%$search%");
            });
        }

        if ($status) {
            $metodePenyusutan->where('status', $status);
        }

        $metodePenyusutans = $metodePenyusutan->paginate($request->perpage ?? 10);
        return response()->json([
            'status' => 'success',
            'data' => $metodePenyusutans,
            'message' => 'List Metode Penyusutan'
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = MetodePenyusutan::all();
        return response()->json([
            'message' => 'Metode Penyusutan Data',
            'data' => MetodePenyusutanResource::collection($items),
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
        $item = MetodePenyusutan::create([
            'uuid' => Uuid::uuid4(),
            'nama_penyusutan' => $request->nama_penyusutan,
        ]);

        return response()->json([
            'message' => 'Create Metode Penyusutan Success',
            'data' => new MetodePenyusutanResource($item),
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
        $item = MetodePenyusutan::whereUuid($id)->first();
        return response()->json([
            'message' => 'Metode Penyusutan Data',
            'data' => new MetodePenyusutanResource($item),
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
        $item = MetodePenyusutan::whereUuid($id)->first();
        $item->update([
            'nama_penyusutan' => $request->nama_penyusutan,
        ]);

        return response()->json([
            'message' => 'Update Metode Penyusutan Success',
            'data' => new MetodePenyusutanResource($item),
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
        $item = MetodePenyusutan::whereUuid($id)->first();
        $item->delete();

        return response()->json([
            'message' => 'Delete Metode Penyusutan Success',
        ]);
    }
}
