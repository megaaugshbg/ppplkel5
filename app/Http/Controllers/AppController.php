<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppController extends Controller
{
    public function showRegister()
    {
        return view('register');
    }

    /** Insert form: menyimpan akun baru ke tabel users, lalu langsung login. */
    public function storeRegister(Request $request)
    {
        $data = $request->validate([
            'nama'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'no_hp'    => ['required', 'regex:/^\+?[0-9]{9,15}$/', 'unique:users,no_hp'],
            'password' => ['required', 'min:8', 'confirmed'],
        ], [
            'required'        => ':attribute wajib diisi.',
            'email.email'     => 'Format email tidak valid.',
            'email.unique'    => 'Email sudah terdaftar.',
            'no_hp.regex'     => 'Nomor HP harus 9-15 digit angka.',
            'no_hp.unique'    => 'Nomor HP sudah terdaftar.',
            'password.min'    => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
        ], [
            'nama'     => 'Nama',
            'email'    => 'Email',
            'no_hp'    => 'Nomor HP',
            'password' => 'Password',
        ]);

        $user = User::create([
            'name'     => $data['nama'],
            'email'    => $data['email'],
            'no_hp'    => $data['no_hp'],
            'password' => $data['password'], // di-hash otomatis oleh cast 'hashed' pada model User
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/cari-parkir')->with('sukses', 'Registrasi berhasil. Silakan cari lokasi parkir.');
    }

    public function showLogin()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ], [
            'required' => ':attribute wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ], [
            'email'    => 'Email',
            'password' => 'Password',
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended('/cari-parkir');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
