@extends('layouts.app')
@section('title', 'Masuk')
@section('content')
<div class="login">
  <div class="login-card">
    <div class="login-brand"><img src="{{ asset('images/logo.png') }}" alt="Disnakertrans Provinsi Jawa Barat"></div>
    <div class="eyebrow">PUSAT PUBLIKASI HUMAS</div>
    <h1>Humas Content Hub</h1>
    <p>Masuk untuk mengelola seluruh proses publikasi Humas Disnakertrans Provinsi Jawa Barat.</p>

    <form class="form" method="POST" action="{{ route('login') }}">
      @csrf
      <div class="form-group">
        <label>Username</label>
        <input name="username" class="@error('username') invalid @enderror" value="{{ old('username') }}" autocomplete="username" placeholder="Username" required autofocus>
        @error('username')<div class="error-text">{{ $message }}</div>@enderror
      </div>
      <div class="form-group">
        <label>Password</label>
        <x-password-input name="password" placeholder="Password" autocomplete="current-password" />
      </div>
      <button class="btn primary" style="width:100%;margin-top:6px">Masuk ke Content Hub</button>
    </form>
  </div>
</div>
@endsection