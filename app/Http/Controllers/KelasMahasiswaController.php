<?php

namespace App\Http\Controllers;

use App\Models\Kelas_Mahasiswa;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use Validator;

class KelasMahasiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kelas_Mahasiswa = Kelas_Mahasiswa::all();
        return response()->json($kelas_Mahasiswa);
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
        $kelas_Mahasiswa = new kelas_Mahasiswa();
        $kelas_Mahasiswa->Kelas_Id = $request->Kelas_Id;
        $kelas_Mahasiswa->Mahasiswa_Id = $request->Mahasiswa_Id;
        $kelas_Mahasiswa->save();

        // $Mahasiswa_Id = is_array($request->Mahasiswa_Id) ? $request->Mahasiswa_Id : [$request->Mahasiswa_Id];
        // $mahasiswa = Mahasiswa::whereIn('Mahasiswa_Id', $Mahasiswa_Id)->select('Mahasiswa_Foto')->get();

        // $directoryPath = "/Users/angelokusuma/Documents/Kuliah/Semester 8/Sistem Presensi/sistemabsensi/storage/app/public/faces/38";
        // // if (!Storage::exists($directoryPath)) {
        //     Storage::makeDirectory(public_path"/faces/38");
        // // }

        // // Copy the file to the new directory
        // foreach ($mahasiswa as $mhs) {
        //     $oldPath = "/Users/angelokusuma/Documents/Kuliah/Semester 8/Sistem Presensi/sistemabsensi/storage/app/public/faces/Angelo.JPG";
        //     $newPath = $directoryPath . '/' . $mhs->Mahasiswa_Foto;
        //     if (Storage::exists($oldPath)) {
        //         Storage::copy($oldPath, $newPath);
        //     }
        // }

        return response()->json([
            "message" => "KelasMahasiswa Added."
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Kelas_Mahasiswa $kelas_Mahasiswa)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kelas_Mahasiswa $kelas_Mahasiswa)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'Kelas_Id' => 'required',
            'Mahasiswa_Id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => $validator->messages(),
            ], 422);
        } else {
            // Check if a record with the same Kelas_Id and Mahasiswa_Id already exists
            $existingRecord = Kelas_Mahasiswa::where('Kelas_Id', $request->Kelas_Id)
                ->where('Mahasiswa_Id', $request->Mahasiswa_Id)
                ->first();

            if ($existingRecord) {
                return response()->json([
                    'status' => 409,
                    'message' => 'A record with the same Kelas_Id and Mahasiswa_Id already exists.',
                ], 409);
            }

            $kelas_Mahasiswa = Kelas_Mahasiswa::find($id);

            if ($kelas_Mahasiswa) {
                $kelas_Mahasiswa->update([
                    'Kelas_Id' => $request->Kelas_Id,
                    'Mahasiswa_Id' => $request->Mahasiswa_Id,
                ]);

                return response()->json([
                    'status' => 200,
                    'message' => 'Kelas Mahasiswa record updated.'
                ], 200);
            } else {
                return response()->json([
                    'status' => 404,
                    'message' => "Kelas Mahasiswa record with ID $id not found."
                ], 404);
            }
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (Kelas_Mahasiswa::where('Kelas_Mahasiswa_Id', $id)->exists()) {
            $kelas_Mahasiswa = Kelas_Mahasiswa::find($id);
            $kelas_Mahasiswa->delete();

            return response()->json([
                "message" => "KelasMahasiswa deleted."
            ], 202);
        } else {
            return response()->json([
                "message" => "KelasMahasiswa not found."
            ], 404);
        }
    }

    public function getMahasiswaByKelasId(string $kelasId)
    {
        $kelasMahasiswa = Kelas_Mahasiswa::where('Kelas_Id', $kelasId)->get(); // Get all matching records

        // if ($kelasMahasiswa->isEmpty()) {
        //     return response()->json([
        //         'message' => 'Kelas Mahasiswa records with Kelas_Id ' . $kelasId . ' not found.'
        //     ], 404);
        // }

        // Extract mahasiswa IDs for efficient retrieval
        $mahasiswaIds = $kelasMahasiswa->pluck('Mahasiswa_Id');

        $mahasiswa = Mahasiswa::whereIn('Mahasiswa_Id', $mahasiswaIds)->get(); // Retrieve all mahasiswa in one query

        return response()->json($mahasiswa);
    }

    public function getKelasMahasiswaByKelasId(string $kelasId)
    {
        $kelasMahasiswa = Kelas_Mahasiswa::where('Kelas_Id', $kelasId)->get(); // Get all matching records

        return response()->json($kelasMahasiswa);
    }
}
