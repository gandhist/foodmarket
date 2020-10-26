<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Actions\Fortify\PasswordValidationRules;
use Exception;
use App\Helpers\ResponseFormatter;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

// models
use App\Models\User;

class UserController extends Controller
{

    use PasswordValidationRules;
    //

    // login
    public function login(Request $request){
        try {
            //validation
            $request->validate(
                ["email" => 'required|email', "password" => "required"]
            );

            // cek credentials
            $credentials = request(['email','password']);
            if(!Auth::attempt($credentials)){
                return ResponseFormatter::error(['message' => 'Unauthorized'], 'Authentication Failed', 500);
            }

            // jika berhasil maka
            $user = User::where('email', $request->email)->first();
            // cek password
            if(!Hash::check($request->password, $$user->password)){
                throw new \Exception("password tidak sama");
            }

            // jika behasil maka erikan token
            $tokenResult = $user->createToken('authToken')->plainTextToken;
            return ResponseFormatter::success(
                [
                    'access_token' => $tokenResult,
                    'token_type' => 'Bearer',
                    'user' => $user
                ], 'Authenticated'
            );
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error
            ], 'Authentication Failed', 500);
        }
    }

    // registrasion
    public function register(Request $request){
        try {
            // validation
            $request->validate([
                'name' => ['required','string','max:255'],
                'email' => ['required','email','string','max:255', 'unique:users'],
                'password' => $this->passwordRules()

            ]);

            // store data to database
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'address' => $request->address,
                'houseNumber' => $request->houseNumber,
                'phoneNumber' => $request->phoneNumber,
                'city' => $request->city,
                'password' => Hash::make($request->password),
            ]);

            // get user
            $user = User::where('email', $request->email)->first();
            // memberikan token autentikasi
            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                "access_token" => $tokenResult,
                "token_type" => "Bearer",
                "user" => $user
            ],"Selamat, Registrasi berhasil!");

        } catch (Exception $e) {
            return ResponseFormatter::error([
                "message" => "Something went wrong",
                "error" => $e 
            ],"error ketika melakukan registrasi", 500);
        }
    }

    // logout
    public function logout(Request $request){
        // karena sudah login maka tinggal akses token yang dipakai sekarang
        $token = $request->user()->currentAccessToken()->delete();
        return ResponseFormatter::success($token, "berhasil logout, token revoked");
    }

    // get user profile login
    public function fetch(Request $request){
        return ResponseFormatter::success($request->user(), "Data profile berhasil di ambil");
    }

    // update profile 
    public function updateProfile(Request $request){
        $data = $request->all();

        $user = Auth::user();
        $user->update($data);
        return ResponseFormatter::success($user,'Profile updated');
    }

    // update foto profile pictures
    public function updatePhoto(Request $request){

        $validator = Validator::make($request->all(), [
            'file' => 'required|image|max:2048'
        ]);

        // jika validasi gagal
        if($validator->fails()){
            return ResponseFormatter::error(['error' => $validator->error()], 'gagal saat update foto', 401);
        }

        //upload foto profile
        if($request->file('file')){
            $file = $request->file->store('asset/user','public');

            // simpan path ke database
            $user = Auth::user();
            $user->profile_photo_path = $file;
            $user->update();

            return ResponseFormatter::success([$file], "Profile Pictures Update");
        }
    }

}