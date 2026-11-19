<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required'    => 'Alamat email wajib diisi.',
            'email.email'       => 'Format email belum benar.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $attempt = Auth::attempt(
            ['email' => $credentials['email'], 'password' => $credentials['password']],
            $request->boolean('remember')
        );

        if (! $attempt) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Email atau password salah.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'nama_depan'   => ['required', 'string', 'max:100'],
            'nama_belakang'=> ['nullable', 'string', 'max:100'],
            'alamat'       => ['required', 'string', 'max:255'],
            'nik'          => ['required', 'digits:16', 'unique:users,nik'],
            'no_hp'        => ['required', 'regex:/^[0-9]{8,13}$/'],
            'email'        => ['required', 'email', 'unique:users,email'],
            'password'     => ['required', 'min:8', 'confirmed'],
        ], [
            'nik.digits'      => 'NIK harus 16 digit angka.',
            'nik.unique'      => 'NIK sudah terdaftar.',
            'no_hp.regex'     => 'Nomor HP diisi tanpa +62, contoh: 81234567890.',
            'email.unique'    => 'Email sudah terdaftar.',
            'password.min'    => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Verifikasi password tidak cocok.',
        ]);

        $user = User::create([
            'name'         => trim($data['nama_depan'] . ' ' . ($data['nama_belakang'] ?? '')),
            'email'        => $data['email'],
            'no_hp'        => '+62' . ltrim($data['no_hp'], '0'),
            'alamat'       => $data['alamat'],
            'nik'          => $data['nik'],
            'password'     => $data['password'], // di-hash otomatis oleh casts()
            'status_verif' => false,
        ]);

        Auth::login($user);

        return redirect()->route('home');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
