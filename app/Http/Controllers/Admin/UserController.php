<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('role')->orderBy('name')->get();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.form', ['user' => new User(['role' => 'pengaju', 'is_active' => true])]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:users,username'],
            'email'    => ['nullable', 'string', 'max:255', 'unique:users,email'],
            'role'     => ['required', Rule::in(array_keys(User::ROLES))],
            'password' => ['required', 'string', 'min:' . config('hub.min_password')],
        ]);

        $data['email']     = $data['email'] ?: $data['username'] . '@humas.local';
        $data['is_active'] = $request->boolean('is_active');

        $user = User::create($data);

        return redirect()->route('contents.index')
            ->with('ok', 'Akun ' . $user->name . ' (' . $user->roleLabel() . ') berhasil dibuat.');
    }

    public function edit(User $user)
    {
        return view('admin.users.form', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->id)],
            'email'    => ['nullable', 'string', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role'     => ['required', Rule::in(array_keys(User::ROLES))],
        ]);

        $data['email']     = $data['email'] ?: $data['username'] . '@humas.local';
        $data['is_active'] = $request->boolean('is_active');

        // cegah Super Admin mengunci dirinya sendiri
        if ($user->id === $request->user()->id) {
            $data['role']      = $user->role;
            $data['is_active'] = true;
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('ok', 'Akun diperbarui.');
    }

    public function password(Request $request, User $user)
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:' . config('hub.min_password'), 'confirmed'],
        ]);

        $user->update(['password' => $data['password']]);

        return back()->with('ok', 'Kata sandi untuk ' . $user->name . ' berhasil diubah.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('err', 'Anda tidak bisa menghapus akun sendiri.');
        }

        $dipakai = Content::where('requester_id', $user->id)->orWhere('editor_id', $user->id)->exists();
        if ($dipakai) {
            return back()->with('err', 'Akun ini terkait dengan konten. Nonaktifkan saja lewat menu Ubah.');
        }

        $user->delete();

        return back()->with('ok', 'Akun dihapus.');
    }
}