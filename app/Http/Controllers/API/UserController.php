<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use App\Models\Article;
use App\Models\Comment;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Laravel\Fortify\Rules\Password;
use App\Http\Controllers\Controller;
use DateTime;
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

            if ($user->disabled_at) {
                return ResponseFormatter::error('Status Pengguna Tidak Aktif. Hubungi Admin Apabila Ada Kesalahan.', 400);
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

    public function updateProfile(Request $request)
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

    // Get All Users
    public function index(Request $request)
    {
        // Checking user role
        if (Auth::user()->role != 'admin') {
            return ResponseFormatter::error('Anda Bukan Admin', 401);
        }

        $search = $request->input('search');
        $limit = $request->input('limit', 10);

        $users = User::query();

        if ($search) {
            $users
                ->where('users.name', 'like', '%' . $search . '%')
                ->orWhere('users.username', 'like', '%' . $search . '%')
                ->orWhere('users.email', 'like', '%' . $search . '%')
                ->orWhere('users.city', 'like', '%' . $search . '%');
        }

        $users->latest();

        return ResponseFormatter::success(
            $users->paginate($limit),
            'Data Seluruh Pengguna Ditemukan',
        );
    }

    // Add User
    public function store(Request $request)
    {
        // Checking user role
        if (Auth::user()->role != 'admin') {
            return ResponseFormatter::error('Anda Bukan Admin', 401);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'avatar' => 'nullable|file',
            'city' => 'nullable|string',
        ]);

        try {
            $user = User::create([
                'name' => $request->input('name'),
                'username' => $request->input('username'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->password),
                'city' => $request->input('city'),
                'role' => $request->input('role'),
            ]);

            if ($request->hasFile('avatar')) {
                $avatar_path = '';

                // Store avatar 
                $avatar_path = $request->file('avatar')->store('user');

                // Add to database
                $user->update([
                    'avatar' => $avatar_path,
                ]);
            }

            return ResponseFormatter::success([
                'user' => $user,
            ], 'Data Pengguna Berhasil Ditambahkan', 201);
        } catch (Exception $error) {
            return ResponseFormatter::error('Ada Yang Salah.', 500);
        }
    }

    // Get User
    public function show(string $id)
    {
        // Checking user role
        if (Auth::user()->role != 'admin') {
            return ResponseFormatter::error('Anda Bukan Admin', 401);
        }

        $user = User::find($id);

        if (!$user) {
            return ResponseFormatter::error('Data Pengguna Tidak Ditemukan', 404);
        }

        return ResponseFormatter::success(
            [
                'user' => $user,
            ],
            'Data Pengguna Ditemukan',
        );
    }

    // Update
    public function update(Request $request, string $id)
    {
        // Checking user role
        if (Auth::user()->role != 'admin') {
            return ResponseFormatter::error('Anda Bukan Admin', 401);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'avatar' => 'nullable|file',
            'city' => 'nullable|string',
        ]);

        $user = user::query()->find($id);

        if (!$user) {
            return ResponseFormatter::error('Data Pengguna Tidak Ditemukan', 404);
        }

        // Validate username
        if ($user->username != $request->input('username')) {
            $request->validate([
                'username' => 'required|string|max:50|unique:users',
            ]);
        }

        // Validate email
        if ($user->email != $request->input('email')) {
            $request->validate([
                'email' => 'required|string|email|max:255|unique:users',
            ]);
        }

        try {
            $user->update([
                'name' => $request->input('name'),
                'username' => $request->input('username'),
                'email' => $request->input('email'),
                'city' => $request->input('city'),
                'role' => $request->input('role'),
            ]);

            if ($request->input('password') && $request->input('password') != '') {
                $user->update([
                    'password' => Hash::make($request->password),
                ]);
            }

            if ($request->hasFile('avatar')) {
                $avatar_path = '';

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
            return ResponseFormatter::error('Ada Yang Salah.', 500);
        }
    }

    // Disable
    public function disable(string $id)
    {
        // Checking user role
        if (Auth::user()->role != 'admin') {
            return ResponseFormatter::error('Anda Bukan Admin', 401);
        }

        $user = user::query()->find($id);

        if (!$user) {
            return ResponseFormatter::error('Data Pengguna Tidak Ditemukan', 404);
        }

        if ($user->disabled_at) {
            return ResponseFormatter::error('Pengguna Telah Non-aktif Sebelumnya', 400);
        }

        $user->update([
            'disabled_at' => now()->toDateTimeString(),
        ]);

        $user = user::query()->find($id);

        return ResponseFormatter::success(
            [
                'user' => $user,
            ],
            'Pengguna Berhasil Di-non-aktifkan',
            200,
        );
    }

    // Enable
    public function enable(string $id)
    {
        // Checking user role
        if (Auth::user()->role != 'admin') {
            return ResponseFormatter::error('Anda Bukan Admin', 401);
        }

        $user = user::query()->find($id);

        if (!$user) {
            return ResponseFormatter::error('Data Pengguna Tidak Ditemukan', 404);
        }

        if (!$user->disabled_at) {
            return ResponseFormatter::error('Pengguna Telah Aktif Sebelumnya.', 400);
        }

        $user->update([
            'disabled_at' => null,
        ]);

        $user = user::query()->find($id);

        return ResponseFormatter::success(
            [
                'user' => $user,
            ],
            'Pengguna Berhasil Diaktifkan',
            200,
        );
    }
}
