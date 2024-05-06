<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;

class MahasiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $mahasiswa = Mahasiswa::all();
        return response()->json($mahasiswa);
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
        $mahasiswa = new Mahasiswa();
        $mahasiswa->Mahasiswa_Nama = $request->Mahasiswa_Nama;

        $validator = Validator::make($request->all(), [
            'Mahasiswa_Nama' => 'required',
            'Mahasiswa_Foto' => 'required|image|mimes:jpeg,png,jpg,JPG',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => $validator->messages(),
            ], 422);
        } else {
            $imageName = $request->Mahasiswa_Nama . '.' . $request->file('Mahasiswa_Foto')->getClientOriginalExtension();

            $request->file('Mahasiswa_Foto')->storeAs('public/faces', $imageName);

            $mahasiswa->Mahasiswa_Foto = $imageName;
            $mahasiswa->save();

            return response()->json([
                "message" => "Mahasiswa Added.",
                "image" => $imageName
            ], 201);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Mahasiswa $mahasiswa)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Mahasiswa $mahasiswa)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'Mahasiswa_Nama' => 'required',
            'Mahasiswa_Foto' => 'nullable|image|mimes:jpeg,png,jpg,JPG', // Allow optional image update with validation
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => $validator->messages(),
            ], 422);
        } else {
            $mahasiswa = Mahasiswa::find($id);

            if ($mahasiswa) {
                $mahasiswa->Mahasiswa_Nama = $request->Mahasiswa_Nama;

                // Handle image update if present in the request
                if ($request->hasFile('Mahasiswa_Foto')) {
                    // Delete the old image
                    Storage::disk('public')->delete('upload/'.$mahasiswa->Mahasiswa_Foto);

                    $imageName = $request->Mahasiswa_Nama . '.' . $request->file('Mahasiswa_Foto')->getClientOriginalExtension();

                    // Option 1: Store the image in the public disk (accessible from web)
                    $request->file('Mahasiswa_Foto')->storeAs('public/upload', $imageName);

                    $mahasiswa->Mahasiswa_Foto = $imageName;
                }

                $mahasiswa->save();

                return response()->json([
                    'status' => 200,
                    'message' => 'Mahasiswa record updated.'
                ], 200);
            } else {
                return response()->json([
                    'status' => 404,
                    'message' => "Mahasiswa record with ID $id not found."
                ], 404);
            }
        }
    }




    /**
     * Remove the specified resource from storage.
     */
    public function destroy(String $id)
    {
        if (Mahasiswa::where('Mahasiswa_Id', $id)->exists()) {
            $mahasiswa = Mahasiswa::find($id);

            Storage::disk('public')->delete('upload/'.$mahasiswa->Mahasiswa_Foto);

            $mahasiswa->delete();

            return response()->json([
                "message" => "Mahasiswa deleted."
            ], 202);
        } else {
            return response()->json([
                "message" => "Mahasiswa not found."
            ], 404);
        }
    }
}
