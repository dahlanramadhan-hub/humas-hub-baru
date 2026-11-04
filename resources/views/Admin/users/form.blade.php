@extends('layouts.app')
@section('title', $user->exists ? 'Ubah Akun' : 'Tambah Akun')
@section('content')
<main class="container">
  <a class="back" href="{{ route('admin.users.index') }}">← Kembali ke daftar akun</a>
  @include('partials.flash')

  @php $self = $user->exists && $user->id === auth()->id(); @endphp

  <div class="modal page">
    <div class="modal-head">
      <div>
        <div class="eyebrow">SUPER ADMIN</div>
        <h2 style="margin:5px 0">{{ $user->exists ? 'Ubah Akun' : 'Tambah Akun' }}</h2>
      </div>
    </div>

    <div class="modal-body">
      <form class="form" method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}">
        @csrf
        @if($user->exists) @method('PUT') @endif

        <div class="section">
          <h4>Data Akun</h4>

          <div class="form-group">
            <label>Nama Lengkap *</label>
            <input name="name" class="@error('name') invalid @enderror" value="{{ old('name', $user->name) }}" required>
            @error('name')<div class="error-text">{{ $message }}</div>@enderror
          </div>

          <div class="two">
            <div class="form-group">
              <label>Username (untuk login) *</label>
              <input name="username" class="@error('username') invalid @enderror" value="{{ old('username', $user->username) }}" required autocomplete="off">
              @error('username')
                <div class="error-text">{{ $message }}</div>
              @else
                <div class="item-note">Huruf, angka, - dan _ saja, tanpa spasi.</div>
              @enderror
            </div>
            <div class="form-group">
              <label>Email <span style="font-weight:400;color:#94a3b8">(opsional)</span></label>
              <input name="email" class="@error('email') invalid @enderror" value="{{ old('email', $user->exists ? $user->email : '') }}" placeholder="kosongkan = username@humas.local">
              @error('email')<div class="error-text">{{ $message }}</div>@enderror
            </div>
          </div>

          <div class="two">
            <div class="form-group">
              <label>Peran *</label>
              <select name="role" class="@error('role') invalid @enderror" @disabled($self)>
                @foreach(\App\Models\User::ROLES as $k => $label)
                  <option value="{{ $k }}" @selected(old('role', $user->role) === $k)>{{ $label }}</option>
                @endforeach
              </select>
              @error('role')<div class="error-text">{{ $message }}</div>@enderror
              @if($self)<div class="item-note">Peran akun Anda sendiri tidak bisa diubah.</div>@endif
            </div>
            <div class="form-group">
              <label>Status</label>
              <label class="check-pill" style="margin-top:7px">
                <input type="checkbox" name="is_active" value="1" @checked(old('_token') ? old('is_active') : $user->is_active) @disabled($self)> Akun aktif
              </label>
            </div>
          </div>

          @unless($user->exists)
            <div class="form-group">
              <label>Kata Sandi *</label>
              <x-password-input name="password" id="pw-create" :min="config('hub.min_password')" autocomplete="new-password" />
              <div class="item-note">Minimal {{ config('hub.min_password') }} karakter.</div>
            </div>
          @endunless

          <button class="btn primary">✓ Simpan</button>
        </div>
      </form>

      @if($user->exists)
        <form class="form" method="POST" action="{{ route('admin.users.password', $user) }}">
          @csrf @method('PUT')
          <div class="section">
            <h4>Ubah Kata Sandi</h4>
            <div class="two">
              <div class="form-group">
                <label>Kata Sandi Baru</label>
                <x-password-input name="password" id="pw-new" :min="config('hub.min_password')" autocomplete="new-password" />
                <div class="item-note">Minimal {{ config('hub.min_password') }} karakter.</div>
              </div>
              <div class="form-group">
                <label>Ulangi Kata Sandi Baru</label>
                <x-password-input name="password_confirmation" id="pw-confirm" match="#pw-new" autocomplete="new-password" />
              </div>
            </div>
            <button class="btn secondary">🔑 Ubah Kata Sandi</button>
          </div>
        </form>
      @endif
    </div>
  </div>
</main>
@endsection