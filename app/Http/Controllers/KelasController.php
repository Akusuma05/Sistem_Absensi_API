<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use Illuminate\Http\Request;
use Validator;

class KelasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kelas = Kelas::all();
        return response()->json($kelas);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $kelas = new Kelas();
        $kelas->Kelas_Nama = $request->Kelas_Nama;
        $kelas->Kelas_Lokasi = $request->Kelas_Lokasi;
        $kelas->save();

        return response()->json([$kelas], 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(Kelas $kelas)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kelas $kelas)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'Kelas_Nama' => 'required',
            'Kelas_Lokasi' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => $validator->messages(),
            ], 422);
        } else {
            $kelas = Kelas::find($id);

            if ($kelas) {
                $kelas->update([
                    'Kelas_Nama' => $request->Kelas_Nama,
                    'Kelas_Lokasi' => $request->Kelas_Lokasi,
                ]);

                return response()->json([
                    'status' => 200,
                    'message' => 'Kelas record updated.'
                ], 200);
            } else {
                return response()->json([
                    'status' => 404,
                    'message' => "Kelas record with ID $id not found."
                ], 404);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (Kelas::where('Kelas_Id', $id)->exists()) {
            $kelas = Kelas::find($id);
            $kelas->delete();

            return response()->json([
                "message" => "Kelas deleted."
            ], 202);
        } else {
            return response()->json([
                "message" => "Kelas not found."
            ], 404);
        }
    }
}
