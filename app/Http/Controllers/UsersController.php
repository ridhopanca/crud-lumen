<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller
{
    public function login(Request $request){
        try {
            $dataRequest = json_decode($request->getContent(), true) ? json_decode($request->getContent(), true) : [];
            $validator = Validator::make($dataRequest, [
                'email' => 'required|email',
                'password' => 'required',
            ],[
                'email.required' => 'email is required',
                'password.required' => 'Password is required',
            ]);

            if($validator->fails()){
                return $this->failedValidation(422, 'Unprocessable Requests', $validator->errors());
            }

            $email = $request->email;
            $user = User::where('email', $email)->first();
            if (!$user) {
                return $this->failedValidation(404, 'User not found');
            }
            $validPassword = Hash::check($request->password, $user->password);
            if(!$validPassword){
                return $this->failedValidation(401, 'Wrong Password');
            }
            $data['token'] =  $user->createToken('keuApp')->accessToken;
            $data['name'] =  $user->name;
            $data['email'] = $user->email;
            $responseData = [
                'response_code' => 200,
                'response_desc' => 'Success',
                'response_data' => $data
            ];
            return response()->json($responseData,200);
        } catch(\Exception $e){
            $responseData = [
                'response_code' => 500,
                'response_desc' => 'Internal Server Error',
                'response_error' => $e->getMessage().' - '.$e->getLine()
            ];
            return response()->json($responseData,500);
        }
    }
}
