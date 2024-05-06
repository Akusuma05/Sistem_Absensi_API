<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Validator;

class RegisterController extends Controller
{
    // public function register(Request $request): JsonResponse{
    //     $validator = Validator::make($request->all(),[
    //         'name' => 'required',
    //         'email' => 'required|email',
    //         'password' => 'required'
    //     ]);

    //     if($validator->fails()){
    //         return $this->sendError('Validation Error.', $validator->error());
    //     }

    //     $input = $request->all();
    //     $input['password'] = bcrypt($input['password']);
    //     $user = User::create()
    // }
}
