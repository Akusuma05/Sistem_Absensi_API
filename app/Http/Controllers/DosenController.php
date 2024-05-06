<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use Illuminate\Http\Request;
use Validator;

class DosenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dosen = Dosen::all();
        return response()->json($dosen);
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
        $dosen = new Dosen();
        $dosen->Dosen_Nama = $request->Dosen_Nama;
        $dosen->Dosen_Email = $request->Dosen_Email;
        $dosen->Dosen_Password = $request->Dosen_Password;
        $dosen->save();

        return response()->json([
            "message" => "Dosen Added."
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Dosen $dosen)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Dosen $dosen)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'Dosen_Nama' => 'required',
            'Dosen_Email' => 'required',
            'Dosen_Password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => $validator->messages(),
            ], 422);
        } else {
            $dosen = Dosen::find($id);

            if ($dosen) {
                $dosen->update([
                    'Dosen_Nama' => $request->Dosen_Nama,
                    'Dosen_Email' => $request->Dosen_Email,
                    'Dosen_Password' => $request->Dosen_Password,
                ]);

                return response()->json([
                    'status' => 200,
                    'message' => 'Dosen record updated.'
                ], 200);
            } else {
                return response()->json([
                    'status' => 404,
                    'message' => "Dosen record with ID $id not found."
                ], 404);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (Dosen::where('Dosen_Id', $id)->exists()) {
            $dosen = Dosen::find($id);
            $dosen->delete();

            return response()->json([
                "message" => "Dosen deleted."
            ], 202);
        } else {
            return response()->json([
                "message" => "Dosen not found."
            ], 404);
        }
    }
}
