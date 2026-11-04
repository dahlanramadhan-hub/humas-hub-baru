@extends('layouts.app')
@section('title', 'Kelola Akun')
@section('content')
<main class="container">
  <a class="back" href="{{ route('contents.index') }}">← Kembali ke daftar konten</a>
  @include('partials.flash')

  <div class="modal page">
    <div class="modal-head">
      <div>
        <div class="eyebrow">SUPER ADMIN</div>
        <h2 style="margin:5px 0">Kelola Akun</h2>
        <div class="meta">Buat akun, atur peran, dan ubah kata sandi.</div>
      </div>
      <a class="btn primary" href="{{ route('admin.users.create') }}">＋ Tambah Akun</a>
    </div>

    <div class="modal-body">
      <div class="section">
        @foreach($users as $u)
          <div class="file">
            <div style="min-width:0">
              <div class="file-name">
                {{ $u->name }}
                <span class="badge {{ $u->role === 'super_admin' ? 'b-purple' : ($u->role === 'editor' ? 'b-yellow' : ($u->role === 'publisher' ? 'b-green' : 'b-blue')) }}">{{ $u->roleLabel() }}</span>
                @unless($u->is_active)<span class="badge b-gray">Nonaktif</span>@endunless
              </div>
              <div class="file-size">{{ $u->username }} · {{ $u->email }}</div>
            </div>
            <div style="display:flex;gap:6px;align-items:center">
              <a class="btn secondary" href="{{ route('admin.users.edit', $u) }}">Ubah</a>
              @if($u->id !== auth()->id())
                <form method="POST" action="{{ route('admin.users.destroy', $u) }}"
                    data-confirm="Akun {{ $u->name }} akan dihapus permanen dan tidak bisa dikembalikan."
                    data-confirm-title="Hapus akun?" data-confirm-ok="Ya, hapus" data-confirm-type="danger">
                  <button class="btn danger">Hapus</button>
                </form>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>
</main>
@endsection