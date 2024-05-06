<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Kelas_Mahasiswa;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Validator;

class AbsensiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $absensi = Absensi::all();

        // Cast absensiId and kelasMahasiswaId to integer before returning
        $absensi = $absensi->map(function ($item) {
            $item->Absensi_Id = (int) $item->Absensi_Id;
            $item->Kelas_Mahasiswa_Id = (int) $item->Kelas_Mahasiswa_Id;
            return $item;
        });

        return response()->json($absensi);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //   """Menyimpan foto mahasiswa baru dan melakukan pencocokan wajah dengan database.

        //   Args:
        //       request (Request): Objek request yang berisi data yang dikirimkan.

        //   Returns:
        //       JsonResponse: Respon JSON berisi status dan data terkait pencocokan wajah.
        //   """

        // Validasi input foto mahasiswa
        $validator = Validator::make($request->all(), [
            'Mahasiswa_Foto' => 'required|image|mimes:jpeg,png,jpg,JPG',
        ]);

        // Retrieve all mahasiswa in one query
        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => $validator->messages(),
            ], 422);
        }

        // Memproses foto mahasiswa dengan script Python
        $imageName = 'detect.' . $request->file('Mahasiswa_Foto')->getClientOriginalExtension();
        $request->file('Mahasiswa_Foto')->storeAs('public/detect', $imageName);
        $arg1 = "/Users/angelokusuma/Documents/Kuliah/Semester 8/Sistem Presensi/sistemabsensi/storage/app/public/detect/$imageName";
        // $arg2 = "/Users/angelokusuma/Documents/Kuliah/Semester 8/Sistem Presensi/sistemabsensi/storage/app/public/faces/$request->Kelas_Id";

        $process = new Process([
            '/Users/angelokusuma/Documents/Kuliah/Semester 8/Sistem Presensi/sistemabsensi/path/to/venv/bin/python3',  # Ganti dengan path ke interpreter Python virtual environment
            '/Users/angelokusuma/Documents/Kuliah/Semester 8/Sistem Presensi/sistemabsensi/app/Python/main.py',           # Ganti dengan path ke script Python (main.py)
            $arg1,
            // $arg2,
            $imageName,
        ]);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // Mendapatkan hasil pencocokan wajah dari output script Python
        $output = $process->getOutput();
        $knownFaceNames = [];
        preg_match_all('/Match found for (.+?) vs\. known face (.+?)\.JPG/', $output, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $knownFaceNames[] = $match[2];  # Ekstrak nama file wajah yang dikenal
        }

        # Mencari ID mahasiswa berdasarkan nama yang terdeteksi
        $mahasiswaIds = Mahasiswa::whereIn('Mahasiswa_Nama', $knownFaceNames)->pluck('Mahasiswa_Id');

        # Filter mahasiswa yang hadir berdasarkan ID kelas
        $presentMahasiswa = Kelas_Mahasiswa::whereIn('Mahasiswa_Id', $mahasiswaIds)
            ->where('Kelas_Id', $request->Kelas_Id)
            ->with('mahasiswa')  # Eager load relasi dengan tabel mahasiswa
            ->get();

        # Tidak ada mahasiswa yang sesuai terdeteksi
        if ($presentMahasiswa->isEmpty()) {
            return response()->json([
                "message" => "Tidak ada mahasiswa yang cocok ditemukan untuk wajah terdeteksi di kelas dengan Kelas_Id: " . $request->Kelas_Id,
                "Detected Faces" => $knownFaceNames,
                "Present Mahasiswa" => $presentMahasiswa,
            ], 404);
        }

        # Mencatat kehadiran mahasiswa yang terdeteksi
        $kelasMahasiswaIds = $presentMahasiswa->pluck('Kelas_Mahasiswa_Id');
        foreach ($kelasMahasiswaIds as $kelasMahasiswaId) {
            $absensi = new Absensi();
            $absensi->Kelas_Mahasiswa_Id = $kelasMahasiswaId;
            $absensi->Absensi_Waktu = $request->Absensi_Waktu;
            $absensi->save();
        }

        # Mendapatkan nama mahasiswa yang hadir
        $presentMahasiswaNames = $presentMahasiswa->map(function ($km) {
            return $km->mahasiswa->Mahasiswa_Nama;
        })->toArray();

        return response()->json([
            "message" => "Presensi dicatat untuk mahasiswa yang cocok",
            "Detected Faces" => $knownFaceNames,
            "Present Mahasiswa" => $presentMahasiswaNames,
        ], 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(Absensi $absensi)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Absensi $absensi)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //   """ Memperbarui data absensi berdasarkan ID yang diberikan.

        //   Args:
        //       request (Request): Objek request yang berisi data yang akan diperbarui.
        //       id (string): ID absensi yang akan diperbarui.

        //   Returns:
        //       JsonResponse: Respon JSON berisi status pembaruan.
        //   """

        // Validasi input data absensi
        $validator = Validator::make($request->all(), [
            'Kelas_Mahasiswa_Id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => $validator->messages(),
            ], 422);
        }

        // Mencari data absensi dengan ID yang diberikan
        $absensi = Absensi::find($id);

        if ($absensi) {
            // Perbarui data absensi
            $absensi->update([
                'Kelas_Mahasiswa_Id' => $request->Kelas_Mahasiswa_Id,
                'Absensi_Waktu' => $request->Absensi_Waktu ?? $absensi->Absensi_Waktu,  // Gunakan waktu yang ada jika tidak disediakan
            ]);

            return response()->json([
                'message' => 'Data absensi diperbarui.'
            ], 200);
        } else {
            return response()->json([
                'message' => "Data absensi dengan ID $id tidak ditemukan."
            ], 404);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //         """ Menghapus data absensi berdasarkan ID yang diberikan.

        //   Args:
        //       id (string): ID absensi yang akan dihapus.

        //   Returns:
        //       JsonResponse: Respon JSON berisi status penghapusan.
        //   """

        // Periksa keberadaan absensi dengan ID yang diberikan
        if (Absensi::where('Absensi_Id', $id)->exists()) {
            $absensi = Absensi::find($id);
            $absensi->delete();

            return response()->json([
                "message" => "Absensi deleted."
            ], 202);
        } else {
            return response()->json([
                "message" => "Absensi not found."
            ], 404);
        }
    }

    public function exportToExcel()
    {
        //   """Mengekspor data absensi ke file Excel.

        //   Returns:
        //       JsonResponse: Respon JSON berisi URL file Excel yang diekspor.
        //   """

        // Mengambil data absensi dari database
        $queryResults = \DB::select("SELECT
            k.Kelas_Nama,
            km.Mahasiswa_Id,
            m.Mahasiswa_Nama,
            DATE(a.Absensi_Waktu) AS Absensi_Date,
            TIME(a.Absensi_Waktu) AS Absensi_Time
        FROM Kelas k
        JOIN Kelas_Mahasiswa km ON k.Kelas_Id = km.Kelas_Id
        JOIN Mahasiswa m ON km.Mahasiswa_Id = m.Mahasiswa_Id
        JOIN Absensi a ON km.Kelas_Mahasiswa_Id = a.Kelas_Mahasiswa_Id
        ORDER BY k.Kelas_Nama, km.Mahasiswa_Id, Absensi_Date, Absensi_Time;");

        // Membuat spreadsheet baru
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Mengatur judul kolom
        $headers = ['Kelas Nama', 'Mahasiswa Id', 'Mahasiswa Nama', 'Tanggal Absensi', 'Jam Absensi'];
        $sheet->fromArray([$headers], null, 'A1');

        // Menambahkan data absensi ke spreadsheet
        $rowIndex = 2; // Baris awal untuk data (mengabaikan judul)
        foreach ($queryResults as $row) {
            $rowData = [
                $row->Kelas_Nama,
                $row->Mahasiswa_Id,
                $row->Mahasiswa_Nama,
                $row->Absensi_Date,
                $row->Absensi_Time,
            ];
            $sheet->fromArray([$rowData], null, 'A' . $rowIndex++);
        }

        // Menyimpan file spreadsheet
        $writer = new Xlsx($spreadsheet);
        $filename = 'exported_data.xlsx'; // Nama file spreadsheet
        $writer->save(storage_path('app/' . $filename));

        // Mengembalikan URL file spreadsheet untuk diunduh
        return response()->download(storage_path('app/' . $filename));
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

        $process = new Process(['/Users/angelokusuma/Documents/Kuliah/Semester 8/Sistem Presensi/sistemabsensi/path/to/venv/bin/python3', '/Users/angelokusuma/Documents/Kuliah/Semester 8/Sistem Presensi/Sistem_Presensi_API/app/Python/main.py']);

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // Ekstrak hasil deteksi wajah dari output script Python
        $output = $process->getOutput();
        $knownFaces = [];
        preg_match_all('/Match found for (.+?) vs\. known face (.+?)\.JPG/', $output, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $knownFaces[] = $match[2];  // Ekstrak nama file wajah yang dikenal
        }

        // Respon deteksi wajah berhasil
        return response()->json([
            'message' => 'Wajah terdeteksi',
            'Detected Faces' => $knownFaces,
        ], 202);
    }

    public function getAbsensibyId(string $kelasId, string $mahasiswaId)
    {
        // Validate input parameters
        if (!is_numeric($kelasId) || !is_numeric($mahasiswaId)) {
            return response()->json([
                'message' => 'Invalid input. Kelas_Id and Mahasiswa_Id must be numeric.'
            ], 400);
        }

        // Check if Kelas_Mahasiswa record exists for the given Kelas_Id and Mahasiswa_Id
        $kelasMahasiswa = Kelas_Mahasiswa::where(['Kelas_Id' => $kelasId, 'Mahasiswa_Id' => $mahasiswaId])->first();

        if (!$kelasMahasiswa) {
            return response()->json([
                'message' => 'Kelas Mahasiswa record not found for Kelas_Id ' . $kelasId . ' and Mahasiswa_Id ' . $mahasiswaId . '.'
            ], 404);
        }

        // Extract Kelas_Mahasiswa_Id for efficient retrieval
        $kelasMahasiswaId = $kelasMahasiswa->Kelas_Mahasiswa_Id;

        // Retrieve absensi records for the corresponding Kelas_Mahasiswa_Id
        $absensi = Absensi::where('Kelas_Mahasiswa_Id', $kelasMahasiswaId)->get();

        // Return absensi records
        return response()->json($absensi);
    }
}
