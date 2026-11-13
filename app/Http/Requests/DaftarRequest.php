<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class DaftarRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'peran'    => ['required', 'in:pengguna,mentor'], // admin tidak bisa dipilih
            'name'     => ['required', 'string', 'max:120'],
            'email'    => ['required', 'email:rfc', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'setuju'   => ['accepted'],
        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'setuju.accepted' => 'Kamu harus menyetujui Syarat Penggunaan dan Kebijakan Privasi.',
            'peran.in' => 'Peran tidak valid.',
        ];
    }
}
