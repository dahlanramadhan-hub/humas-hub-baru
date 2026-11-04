@php
  $icons    = ['Instagram' => 'instagram-c', 'TikTok' => 'tiktok-c', 'Twitter' => 'twitter-c'];
  $closeUrl = route('contents.index', ['status' => $filter ?? 'Semua']);
@endphp

<div class="dc-card">
  <form method="POST" action="{{ route('contents.store') }}" enctype="multipart/form-data">
    @csrf

    <div class="dc-head">
      <div>
        <div class="dc-eyebrow">Disnakertrans Jabar</div>
        <h1>Ajukan Konten</h1>
        <p>Isi informasi dasar dan upload file yang dibutuhkan.</p>
      </div>
      <a class="dc-x" href="{{ $closeUrl }}" title="Tutup (Esc)">X</a>
    </div>

    @include('partials.flash')

    {{-- INFORMASI KONTEN --}}
    <section class="dc-sec">
      <div class="dc-sec-head">
        <x-icon name="doc" :size="31" />
        <div><h2>Informasi Konten</h2><p>Lengkapi informasi dasar mengenai konten yang diajukan.</p></div>
      </div>
      <div class="dc-body">
        <div class="dc-grid2">
          <div class="dc-col">
            <div class="dc-field">
              <label>Nama / Judul Konten</label>
              <input name="title" class="@error('title') invalid @enderror" value="{{ old('title') }}" placeholder="Contoh: Hari Batik Nasional" required>
            </div>
            <div class="dc-field grow">
              <label>Brief / Arahan</label>
              <textarea name="brief" placeholder="Tulis arahan untuk editor...">{{ old('brief') }}</textarea>
            </div>
          </div>

          <div class="dc-col">
            <div class="dc-field">
              <label>Nama Pengaju Konten</label>
              <input value="{{ auth()->user()->name }}" readonly>
            </div>
            <div class="dc-field">
              <label>Penanggung Jawab Konten</label>
              <select name="editor_id" class="@error('editor_id') invalid @enderror" required>
                <option value="">— Pilih editor —</option>
                @foreach($editors as $e)
                  <option value="{{ $e->id }}" @selected((string) old('editor_id') === (string) $e->id)>{{ $e->name }}</option>
                @endforeach
              </select>
              @if($editors->isEmpty())
                <div class="dc-hint">Belum ada akun editor. Minta Super Admin membuatnya di menu Kelola Akun.</div>
              @endif
            </div>
            <div class="dc-field">
              <label>Jenis Konten</label>
              <select name="type">
                @foreach(\App\Models\Content::TYPES as $t)
                  <option @selected(old('type') === $t)>{{ $t }}</option>
                @endforeach
              </select>
            </div>
            <div class="dc-row">
              <div class="dc-field dc-w141">
                <label>Deadline Konten</label>
                <input type="date" name="deadline" class="@error('deadline') invalid @enderror" value="{{ old('deadline') }}" required>
              </div>
              <div class="dc-field grow">
                <label>Media Sosial</label>
                <div class="dc-chips">
                  @foreach(\App\Models\Content::SOCIALS as $s)
                    <label class="dc-chip">
                      <input type="checkbox" name="channels[]" value="{{ $s }}" @checked(in_array($s, old('channels', [])))>
                      <span><x-icon :name="$icons[$s]" :size="21" />{{ $s }}</span>
                    </label>
                  @endforeach
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    {{-- REFERENSI & BAHAN --}}
    <div class="dc-grid-assets">
      @foreach(['reference' => ['Referensi', 'Referensi dapat berupa file, teks atau link', 'link'],
                'material'  => ['Bahan / Footage', 'Footage dapat berupa file, teks atau link', 'clip']] as $cat => [$t, $sub, $ic])
        <section class="dc-sec">
          <div class="dc-sec-head">
            <x-icon :name="$ic" :size="30" />
            <div><h2>{{ $t }}</h2><p>{{ $sub }}</p></div>
          </div>
          <div class="dc-body">
            <input name="{{ $cat }}_text" value="{{ old($cat . '_text') }}" placeholder="Masukan teks atau link disini" autocomplete="off">
            <div class="dc-pick">
              <label class="dc-btn blue" for="cf-{{ $cat }}"><x-icon name="upload" :size="17" /> Pilih file</label>
              <span class="dc-pick-name" id="cn-{{ $cat }}">Belum ada file yang dipilih</span>
              <input type="file" id="cf-{{ $cat }}" name="{{ $cat }}_files[]" multiple hidden onchange="dcPicked(this,'cn-{{ $cat }}')">
            </div>
          </div>
        </section>
      @endforeach
    </div>

    <div class="dc-bar">
      <button class="dc-big yel"><x-icon name="check" :size="20" /> Ajukan Konten</button>
      <a class="dc-big ghost" href="{{ $closeUrl }}">Batal</a>
    </div>
  </form>
</div>

<script>
function dcPicked(input, id) {
  const n = input.files.length;
  document.getElementById(id).textContent = n === 0 ? 'Belum ada file yang dipilih' : (n === 1 ? input.files[0].name : n + ' file dipilih');
}
</script>