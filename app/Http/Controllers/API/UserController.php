<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Laravel\Fortify\Rules\Password;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'string', new Password],
        ]);

        try {
            User::create([
                'name' => $request->input('name'),
                'username' => $request->input('username'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->password),
                'role' => 'enduser',
            ]);

            $user = User::where('email', $request->input('email'))->first();
            $token = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ], 'Akun Berhasil Dibuat', 201);
        } catch (Exception $error) {
            return ResponseFormatter::error('Ada Yang Salah. Autentikasi Gagal.', 500);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required',
        ]);

        try {
            $credentials = request(['email', 'password']);

            if (!Auth::attempt($credentials)) {
                return ResponseFormatter::error('Email Atau Password Salah. Autentikasi Gagal.', 401);
            }

            $user = User::where('email', $request->input('email'))->first();

            if (!Hash::check($request->input('password'), $user->password, [])) {
                throw new Exception('Invalid Credentials');
            }

            $token = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ], 'Berhasil Masuk', 200);
        } catch (Exception $error) {
            return ResponseFormatter::error('Ada Yang Salah. Autentikasi Gagal.', 500);
        }
    }

    public function fetch()
    {
        $user = User::find(Auth::user()->id);

        return ResponseFormatter::success([
            'user' => $user,
        ], 'Data Pengguna Ditemukan', 200);
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'avatar' => 'nullable|file',
            'city' => 'nullable|string',
        ]);

        $user = user::query()->find(Auth::user()->id);

        if ($user->username != $request->input('username')) {
            $request->validate([
                'username' => 'required|string|max:50|unique:users',
            ]);
        }

        try {
            $user->update([
                'name' => $request->input('name'),
                'username' => $request->input('username'),
                'city' => $request->input('city'),
            ]);

            $avatar_path = '';

            if ($request->hasFile('avatar')) {

                // Delete old avatar
                if ($user->avatar) {
                    Storage::delete($user->avatar);
                }

                // Store avatar 
                $avatar_path = $request->file('avatar')->store('user');

                // Add to database
                $user->update([
                    'avatar' => $avatar_path,
                ]);
            }

            return ResponseFormatter::success([
                'user' => $user,
            ], 'Data Pengguna Diubah', 200);
        } catch (Exception $error) {
            return ResponseFormatter::error('Ada Yang Salah. Autentikasi Gagal.', 500);
        }
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken()->delete();

        return ResponseFormatter::success([
            'token' => $token,
        ], 'Berhasil Keluar', 200);
    }

    // Admin Area
    public function index(Request $request)
    {
        // Checking user role
        if (Auth::user()->role != 'admin') {
            return ResponseFormatter::error('Anda Bukan Admin', 401);
        }

        $limit = $request->input('limit', 10);

        $users = User::query()->latest();

        return ResponseFormatter::success(
            $users->paginate($limit),
            'Data Seluruh Pengguna Ditemukan',
        );
    }
}
