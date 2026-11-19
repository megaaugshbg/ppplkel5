<?php

namespace App\Http\Controllers;

use App\Models\WomanParkingSlot;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WomanParkingController extends Controller
{
    // View page: daftar slot parkir khusus perempuan
    public function index()
    {
        $slots = WomanParkingSlot::orderBy('lokasi')->orderBy('kode_slot')->get();

        return view('woman-parking', compact('slots'));
    }

    // Insert form: tampilkan form tambah slot
    public function create()
    {
        return view('woman-parking-create');
    }

    // Insert form: validasi lalu simpan ke database
    public function store(Request $request)
    {
        $data = $request->validate([
            'lokasi' => ['required', 'string', 'max:100'],
            'kode_slot' => [
                'required', 'string', 'max:10', 'regex:/^[A-Za-z0-9-]+$/',
                Rule::unique('woman_parking_slots')->where('lokasi', $request->input('lokasi')),
            ],
            'tarif' => ['required', 'integer', 'min:0', 'max:1000000'],
            'status' => ['required', Rule::in(['tersedia', 'terisi'])],
            'catatan' => ['nullable', 'string', 'max:255'],
        ], [
            'lokasi.required' => 'Lokasi wajib diisi.',
            'kode_slot.required' => 'Kode slot wajib diisi.',
            'kode_slot.regex' => 'Kode slot hanya boleh huruf, angka, dan tanda hubung.',
            'kode_slot.unique' => 'Kode slot ini sudah ada di lokasi tersebut.',
            'tarif.required' => 'Tarif wajib diisi.',
            'tarif.integer' => 'Tarif harus berupa angka bulat.',
            'tarif.min' => 'Tarif tidak boleh negatif.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status tidak valid.',
        ]);

        $data['kode_slot'] = strtoupper($data['kode_slot']);

        WomanParkingSlot::create($data);

        return redirect('/woman-parking')->with('sukses', 'Slot parkir berhasil ditambahkan.');
    }
}
