<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    // Menampilkan halaman + menyediakan data JSON untuk JavaScript
    public function index()
    {
        // Kalau yang minta adalah JavaScript (fetch dengan Accept: application/json),
        // kirim data dalam bentuk JSON saja.
        if (request()->wantsJson()) {
            return response()->json([
                'contacts' => Contact::latest()->get()
            ]);
        }

        // Kalau yang buka adalah browser biasa (ketik URL langsung),
        // tampilkan halaman HTML-nya.
        return view('contacts.index');
    }

    // Pesan error custom berbahasa Indonesia, dipakai oleh store() dan update()
    private function messages(): array
    {
        return [
            'name.required'  => 'Nama wajib diisi.',
            'name.min'       => 'Nama minimal 3 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
            'email.unique'   => 'Email ini sudah dipakai kontak lain.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.regex'    => 'Nomor telepon harus 9-15 digit angka.',
        ];
    }

    // Tambah data baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|min:3',
            'email' => 'required|email|unique:contacts,email',
            'phone' => 'required|regex:/^[0-9+]{9,15}$/',
        ], $this->messages());

        $contact = Contact::create($validated);

        return response()->json([
            'message' => 'Kontak berhasil ditambahkan.',
            'data' => $contact
        ], 201);
    }

    // Ubah data yang sudah ada
    public function update(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'name'  => 'required|min:3',
            // abaikan (ignore) email milik kontak ini sendiri, supaya
            // saat edit tanpa ubah email tidak dianggap "duplikat"
            'email' => 'required|email|unique:contacts,email,' . $contact->id,
            'phone' => 'required|regex:/^[0-9+]{9,15}$/',
        ], $this->messages());

        $contact->update($validated);

        return response()->json([
            'message' => 'Kontak berhasil diperbarui.',
            'data' => $contact
        ]);
    }

    // Hapus data
    public function destroy(Contact $contact)
    {
        $contact->delete();

        return response()->json([
            'message' => 'Kontak berhasil dihapus.'
        ]);
    }
}