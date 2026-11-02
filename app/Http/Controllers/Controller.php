<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    public function showRegister()
    {
        return view('register');
    }

    public function storeRegister(Request $request)
    {
        $request->validate([
            'nama'     => 'required|string|max:255',
            'email'    => 'required|email',
            'no_hp'    => 'required|string|max:20',
            'password' => 'required|min:8|confirmed',
        ]);

        return redirect('/register')->with('sukses', 'Registrasi berhasil (data belum disimpan).');
    }
}
