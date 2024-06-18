<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
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

        // Simpan foto mahasiswa
        $imageName = 'detect.' . $request->file('Mahasiswa_Foto')->getClientOriginalExtension();
        $request->file('Mahasiswa_Foto')->storeAs('public/detect', $imageName);
        $arg1 = "/Users/angelokusuma/Documents/Kuliah/Semester 8/Sistem Presensi/sistemabsensi/storage/app/public/detect/$imageName";
        $arg2 = $request->Mahasiswa_Nama;

        $process = new Process([
            '/Users/angelokusuma/Documents/Kuliah/Semester 8/Sistem Presensi/sistemabsensi/path/to/venv/bin/python',
            '/Users/angelokusuma/Documents/Kuliah/Semester 8/Sistem Presensi/sistemabsensi/app/Python/AddMahasiswa.py',
            $arg1,
            $arg2,
            $imageName,
        ]);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // Ekstrak hasil deteksi wajah dari output script Python
        $output = $process->getOutput();

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => $validator->messages(),
            ], 422);
        } else {
            $imageName = $request->Mahasiswa_Nama . '.jpg';

            // $request->file('Mahasiswa_Foto')->storeAs('public/faces', $imageName);

            $mahasiswa->Mahasiswa_Foto = $imageName;
            $mahasiswa->save();

            return response()->json([
                "message" => "Mahasiswa Added.",
                "image" => $imageName,
                "Python" => $output
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
                    Storage::disk('public')->delete('faces/' . $mahasiswa->Mahasiswa_Foto);

                    $imageName = $request->Mahasiswa_Nama . '.' . $request->file('Mahasiswa_Foto')->getClientOriginalExtension();

                    $mahasiswa->Mahasiswa_Foto = $imageName;

                    // Simpan foto mahasiswa
                    $imageName = 'detect.' . $request->file('Mahasiswa_Foto')->getClientOriginalExtension();
                    $request->file('Mahasiswa_Foto')->storeAs('public/detect', $imageName);
                    $arg1 = "/Users/angelokusuma/Documents/Kuliah/Semester 8/Sistem Presensi/sistemabsensi/storage/app/public/detect/$imageName";
                    $arg2 = $request->Mahasiswa_Nama;

                    $process = new Process([
                        '/Users/angelokusuma/Documents/Kuliah/Semester 8/Sistem Presensi/sistemabsensi/path/to/venv/bin/python',  # Ganti dengan path ke interpreter Python virtual environment
                        '/Users/angelokusuma/Documents/Kuliah/Semester 8/Sistem Presensi/sistemabsensi/app/Python/EditMahasiswa.py',           # Ganti dengan path ke script Python (main.py)
                        $arg1,
                        $arg2,
                        $imageName,
                    ]);

                    $process->run();

                    if (!$process->isSuccessful()) {
                        throw new ProcessFailedException($process);
                    }

                    // Ekstrak hasil deteksi wajah dari output script Python
                    $output = $process->getOutput();
                }

                $mahasiswa->save();

                return response()->json([
                    'status' => 200,
                    'message' => 'Mahasiswa record updated.',
                    "Python" => $output
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

            Storage::disk('public')->delete('faces/' . $mahasiswa->Mahasiswa_Foto);

            // Simpan foto mahasiswa
            $arg1 = $mahasiswa->Mahasiswa_Nama;

            $process = new Process([
                '/Users/angelokusuma/Documents/Kuliah/Semester 8/Sistem Presensi/sistemabsensi/path/to/venv/bin/python',  # Ganti dengan path ke interpreter Python virtual environment
                '/Users/angelokusuma/Documents/Kuliah/Semester 8/Sistem Presensi/sistemabsensi/app/Python/DeleteMahasiswa.py',           # Ganti dengan path ke script Python (main.py)
                $arg1,
            ]);

            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            // Ekstrak hasil deteksi wajah dari output script Python
            $output = $process->getOutput();

            $mahasiswa->delete();

            return response()->json([
                "message" => "Mahasiswa deleted.",
                "Python" => $output
            ], 202);
        } else {
            return response()->json([
                "message" => "Mahasiswa not found."
            ], 404);
        }
    }

    public function detectMahasiswa(Request $request)
    {
        //   """ Mendeteksi wajah mahasiswa dari foto yang diunggah.

        //   Args:
        //       request (Request): Objek request yang berisi data yang dikirimkan, termasuk foto mahasiswa.

        //   Returns:
        //       JsonResponse: Respon JSON berisi status deteksi dan nama wajah yang terdeteksi (jika ada).
        //   """

        // Validasi input foto mahasiswa
        $validator = Validator::make($request->all(), [
            'Mahasiswa_Foto' => 'required|image|mimes:jpeg,png,jpg,JPG',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => $validator->messages(),
            ], 422);
        }

        // Simpan foto mahasiswa
        $imageName = 'detect.' . $request->file('Mahasiswa_Foto')->getClientOriginalExtension();
        $request->file('Mahasiswa_Foto')->storeAs('public/detect', $imageName);
        $arg1 = "/Users/angelokusuma/Documents/Kuliah/Semester 8/Sistem Presensi/sistemabsensi/storage/app/public/detect/$imageName";
        // $arg2 = "/Users/angelokusuma/Documents/Kuliah/Semester 8/Sistem Presensi/sistemabsensi/storage/app/public/faces/$request->Kelas_Id";

        $process = new Process([
            '/Users/angelokusuma/Documents/Kuliah/Semester 8/Sistem Presensi/sistemabsensi/path/to/venv/bin/python',  # Ganti dengan path ke interpreter Python virtual environment
            '/Users/angelokusuma/Documents/Kuliah/Semester 8/Sistem Presensi/sistemabsensi/app/Python/main.py',           # Ganti dengan path ke script Python (main.py)
            $arg1,
            // $arg2,
            $imageName,
        ]);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // Ekstrak hasil deteksi wajah dari output script Python
        $output = $process->getOutput();
        $knownFaces = [];
        preg_match_all('/Match found for (.+?) vs\. known face (.+?)\.jpg/', $output, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $knownFaces[] = $match[2];  // Ekstrak nama file wajah yang dikenal
        }

        // Respon deteksi wajah berhasil
        return response()->json([
            'message' => 'Wajah terdeteksi',
            'Detected Faces' => $knownFaces,
            'python' => $output
        ], 202);
    }
}
