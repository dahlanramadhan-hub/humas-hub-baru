@php
  $u          = auth()->user();
  $canUpdate  = $u->can('update', $content);
  $canAssets  = $u->can('viewAssets', $content);
  $canManage  = $u->can('manageAssets', $content);
  $canDrafts  = $u->can('viewDrafts', $content);
  $canUpload  = $u->can('uploadDraft', $content);
  $canReview  = $u->can('review', $content);
  $canReopen  = $u->can('reopen', $content);
  $canCaption = $u->can('editCaption', $content);
  $canFinal   = $u->can('downloadFinal', $content);
  $canPublish = $u->can('publish', $content);
  $canDelete  = $u->can('delete', $content);

  $drafts   = $content->drafts->sortByDesc('id')->values();   // terbaru di atas
  $draftIds = $content->drafts->pluck('id');
  $lastId   = $draftIds->last();
  $icons    = ['Instagram' => 'instagram-c', 'TikTok' => 'tiktok-c', 'Twitter' => 'twitter-c'];
  $closeUrl = route('contents.index', ['status' => $filter ?? 'Semua']);

  $note = null;
  if ($canUpload && in_array($content->status, ['Belum Dikerjakan', 'Revisi'])) {
      $note = '✏️ Giliran editor: unggah draft (atau draft revisi) di bagian Draft & Revisi.';
  } elseif ($canReview) {
      $note = '👀 Draft dari editor siap diperiksa. Klik ACC jika sudah benar, atau tambahkan komentar jika ada yang perlu direvisi.';
  } elseif ($canPublish && $content->status === 'Approved') {
      $note = '✅ Konten sudah di-ACC pengaju. Silakan jadwalkan atau langsung publish.';
  }
@endphp

<div class="dc-card">
    @include('partials.flash')

    {{-- HEADER --}}
    <div class="dc-head">
      <div>
        <div class="dc-eyebrow">Disnakertrans Jabar</div>
        <h1>Detail Konten</h1>
        <p>{{ $content->title }}</p>
        <div class="dc-meta">
          <span class="badge {{ $content->badgeClass() }}">{{ $content->status }}</span>
          <span>{{ $content->type }}</span><span>·</span>
          <span>{{ $content->status === 'Publish' ? '✅ Selesai' : '👉 Giliran ' . $content->turnLabel() }}</span>
        </div>
      </div>
            <a class="dc-x" href="{{ $closeUrl }}" title="Tutup (Esc)">X</a>
    </div>

    @if($note)<div class="dc-note">{{ $note }}</div>@endif

    {{-- INFORMASI KONTEN --}}
    <section class="dc-sec">
      <div class="dc-sec-head">
        <x-icon name="doc" :size="31" />
        <div><h2>Informasi Konten</h2><p>Lengkapi informasi dasar mengenai konten yang diajukan.</p></div>
      </div>
      <form id="info-form" class="dc-body" method="POST" action="{{ route('contents.update', $content) }}">
        @csrf @method('PUT')
        <fieldset class="dc-fs" @disabled(! $canUpdate)>
          <div class="dc-grid2">
            <div class="dc-col">
              <div class="dc-field">
                <label>Nama / Judul Konten</label>
                <input name="title" value="{{ old('title', $content->title) }}" required>
              </div>
              <div class="dc-field grow">
                <label>Brief / Arahan</label>
                <textarea name="brief" placeholder="Tulis arahan untuk editor...">{{ old('brief', $content->brief) }}</textarea>
              </div>
            </div>

            <div class="dc-col">
              <div class="dc-field">
                <label>Nama Pengaju Konten</label>
                <input value="{{ $content->requester_name }}" readonly>
              </div>
              <div class="dc-field">
                <label>Penanggung Jawab Konten</label>
                @if($canUpdate)
                  <select name="editor_id">
                    @foreach($editors as $e)
                      <option value="{{ $e->id }}" @selected((int) old('editor_id', $content->editor_id) === $e->id)>{{ $e->name }}</option>
                    @endforeach
                  </select>
                @else
                  <input value="{{ $content->responsible_name }}" readonly>
                @endif
              </div>
            <div class="dc-field">
                <label>Jenis Konten</label>
                @if($canUpdate)
                  <select name="type">
                    @foreach(\App\Models\Content::TYPES as $t)
                      <option @selected(old('type', $content->type) === $t)>{{ $t }}</option>
                    @endforeach
                  </select>
                @else
                  <input value="{{ $content->type }}" readonly>
                @endif
              </div>
              <div class="dc-row">
                <div class="dc-field dc-w141">
                  <label>Deadline Konten</label>
                  <input type="date" name="deadline" value="{{ old('deadline', $content->deadline->format('Y-m-d')) }}" required>
                </div>
                <div class="dc-field grow">
                  <label>Media Sosial</label>
                  <div class="dc-chips">
                    @foreach(\App\Models\Content::SOCIALS as $s)
                      @php $on = in_array($s, old('channels', $content->channels)); @endphp
                      @if($canUpdate || $on)
                        <label class="dc-chip">
                          <input type="checkbox" name="channels[]" value="{{ $s }}" @checked($on)>
                          <span><x-icon :name="$icons[$s]" :size="21" />{{ $s }}</span>
                        </label>
                      @endif
                    @endforeach
                  </div>
                </div>
              </div>
            </div>
          </div>
        </fieldset>
      </form>
    </section>

    {{-- REFERENSI & BAHAN (tidak tampil untuk Publisher) --}}
    @if($canAssets)
      @php
        $blocks = [
          'reference' => ['title' => 'Referensi',       'sub' => 'Referensi dapat berupa file, teks atau link', 'icon' => 'link', 'items' => $content->references],
          'material'  => ['title' => 'Bahan / Footage', 'sub' => 'Footage dapat berupa file, teks atau link',   'icon' => 'clip', 'items' => $content->materials],
        ];
      @endphp
      <div class="dc-grid-assets">
        @foreach($blocks as $cat => $b)
          <section class="dc-sec" id="box-{{ $cat }}">
            <div class="dc-sec-head">
              <x-icon :name="$b['icon']" :size="30" />
              <div><h2>{{ $b['title'] }}</h2><p>{{ $b['sub'] }}</p></div>
            </div>
            <div class="dc-body">

              @if($canManage)
                <form id="add-{{ $cat }}" method="POST" action="{{ route('contents.assets.store', $content) }}" enctype="multipart/form-data">
                  @csrf
                  <input type="hidden" name="category" value="{{ $cat }}">
                  <input name="text" placeholder="Masukan teks atau link disini" autocomplete="off">
                  <div class="dc-pick">
                    <label class="dc-btn blue" for="file-{{ $cat }}"><x-icon name="upload" :size="17" /> Pilih file</label>
                    <span class="dc-pick-name" id="name-{{ $cat }}">Belum ada file yang dipilih</span>
                    <input type="file" id="file-{{ $cat }}" name="files[]" multiple hidden onchange="dcPicked(this,'name-{{ $cat }}')">
                  </div>
                </form>
              @endif

              <div class="dc-items">
                @forelse($b['items'] as $f)
                  <div class="dc-item" data-dl="{{ $f->kind === 'file' ? route('assets.download', $f) : '' }}">
                    <input type="checkbox" class="dc-check" name="ids[]" value="{{ $f->id }}" form="bulk-{{ $cat }}" aria-label="Pilih item">
                    <div class="dc-item-info">
                      <div class="dc-fname">{{ $f->kind === 'link' ? '🔗 ' : ($f->kind === 'text' ? '✎ ' : '') }}{{ $f->name }}</div>
                      <div class="dc-fsize">{{ $f->size_label }}</div>
                      @if($f->kind === 'text')<div class="dc-ftext">{{ $f->text }}</div>@endif
                    </div>
                    <div class="dc-item-act">
                      @if($f->kind === 'file')
                        <a class="dc-ico dl" href="{{ route('assets.download', $f) }}" title="Download"><x-icon name="download" :size="19" /></a>
                      @elseif($f->kind === 'link')
                        <a class="dc-ico dl" href="{{ $f->text }}" target="_blank" rel="noopener" title="Buka link"><x-icon name="link" :size="19" /></a>
                      @endif
                      @if($canManage)
                        <form method="POST" action="{{ route('assets.destroy', $f) }}"
                              data-confirm="Item ini akan dihapus dari daftar." data-confirm-title="Hapus item?"
                              data-confirm-ok="Ya, hapus" data-confirm-type="danger">
                          @csrf @method('DELETE')
                          <button class="dc-ico del" title="Hapus"><x-icon name="bin" :size="19" /></button>
                        </form>
                      @endif
                    </div>
                  </div>
                @empty
                  <p class="dc-empty">Belum ada item.</p>
                @endforelse
              </div>

              <div class="dc-btns">
                @if($canManage)
                  <button type="submit" form="add-{{ $cat }}" class="dc-btn blue"><x-icon name="plus" :size="18" /> Tambahkan</button>
                  <button type="button" class="dc-btn yel" onclick="dcBulkDelete('bulk-{{ $cat }}')"><x-icon name="bin" :size="17" /> Hapus</button>
                @endif
                <button type="button" class="dc-btn grn" onclick="dcDownload('box-{{ $cat }}')"><x-icon name="download" :size="17" /> Download</button>
              </div>

              @if($canManage)
                <form id="bulk-{{ $cat }}" method="POST" action="{{ route('contents.assets.bulk', $content) }}"
                      data-confirm="Item yang dicentang akan dihapus dari daftar." data-confirm-title="Hapus item terpilih?"
                      data-confirm-ok="Ya, hapus" data-confirm-type="danger">
                  @csrf @method('DELETE')
                </form>
              @endif
            </div>
          </section>
        @endforeach
      </div>
    @endif

    {{-- DRAFT & REVISI (tidak tampil untuk Publisher) --}}
    @if($canDrafts)
      <section class="dc-sec" id="draft-box">
        <div class="dc-sec-head">
          <x-icon name="clock" :size="31" />
          <div><h2>Draft & Revisi</h2><p>Silahkan upload draf dan komentar disini</p></div>
          @if($canUpload)
            <form id="draft-up" class="dc-head-act" method="POST" action="{{ route('drafts.store', $content) }}" enctype="multipart/form-data">
              @csrf
              <input type="file" id="draft-file" name="files[]" multiple hidden onchange="this.form.requestSubmit()">
              <label for="draft-file" class="dc-btn white"><x-icon name="plus" :size="17" /> Upload file</label>
            </form>
          @endif
        </div>

        <div class="dc-body">
          @if($canUpload)
            <details class="dc-more">
              <summary>+ Kirim teks atau link sebagai draft</summary>
              <form method="POST" action="{{ route('drafts.store', $content) }}">
                @csrf
                <input name="text" placeholder="Tulis draft atau tempel link disini" autocomplete="off" required>
                <button class="dc-btn blue">Kirim</button>
              </form>
            </details>
          @endif

          @if($drafts->isEmpty())
            <p class="dc-empty">Belum ada draft dari editor.</p>
          @else
            <div class="dc-timeline">
              @foreach($drafts as $d)
                @php $no = $draftIds->search($d->id) + 1; $isLast = $d->id === $lastId; @endphp
                <div class="dc-draft">
                  <h3>
                    Draft {{ $no }}
                    @if($content->final_draft_id === $d->id)<span class="badge b-green">FINAL</span>@endif
                  </h3>
                  <div class="dc-date">{{ $d->created_at->translatedFormat('d/m/Y, H:i') }}</div>

                  <div class="dc-ditem">
                    <div class="dc-dtop">
                      <div class="dc-dfile">
                        <x-icon name="check" :size="23" />
                        <div style="min-width:0">
                          <div class="dc-fname">{{ $d->displayName() }}</div>
                          <div class="dc-fsize">{{ $d->size_label }}</div>
                        </div>
                      </div>
                      <div class="dc-dact">
                        @if($isLast && $canReview)
                          <button type="button" class="dc-btn yel" onclick="dcToggle('cpanel')"><x-icon name="plus" :size="17" /> Tambahkan komentar</button>
                          <form method="POST" action="{{ route('contents.approve', $content) }}"
                                data-confirm="Draft terakhir sudah benar dan akan diteruskan ke Publisher."
                                data-confirm-title="ACC draft ini?" data-confirm-ok="Ya, ACC">
                            @csrf
                            <button class="dc-btn grn"><x-icon name="check" :size="17" /> ACC</button>
                          </form>
                        @endif

                        @if($canUpload && $d->comments->isEmpty())
                          <form method="POST" action="{{ route('drafts.destroy', $d) }}"
                                data-confirm="Draft {{ $no }} akan dihapus beserta filenya. Gunakan ini jika salah unggah."
                                data-confirm-title="Hapus draft?" data-confirm-ok="Ya, hapus" data-confirm-type="danger">
                            @csrf @method('DELETE')
                            <button class="dc-btn yel"><x-icon name="bin" :size="17" /> Hapus</button>
                          </form>
                        @endif

                        @if($d->kind === 'file')
                          <a class="dc-btn blue" target="_blank" rel="noopener" href="{{ route('drafts.download', ['draft' => $d, 'inline' => 1]) }}"><x-icon name="eye" :size="18" /> View</a>
                        @elseif($d->kind === 'link')
                          <a class="dc-btn blue" target="_blank" rel="noopener" href="{{ $d->text }}"><x-icon name="eye" :size="18" /> View</a>
                        @endif
                      </div>
                    </div>

                    @if($d->kind === 'text')<div class="dc-text">{{ $d->text }}</div>@endif

                    @foreach($d->comments as $cm)
                      @php $acc = str_starts_with($cm->body, 'ACC'); @endphp
                      <div class="dc-comment {{ $acc ? 'acc' : '' }}">
                        <span class="dc-tag {{ $acc ? 'acc' : '' }}">{{ $acc ? 'ACC' : 'Revisi' }}</span>
                        <div><small>{{ $cm->user_name }}</small>{{ $cm->body }}</div>
                      </div>
                    @endforeach

                    @if($isLast && $canReview)
                      <form id="cpanel" class="dc-cpanel" method="POST" action="{{ route('contents.comments.store', $content) }}" hidden>
                        @csrf
                        <textarea name="body" rows="3" placeholder="Ada yang salah? Tulis revisi untuk editor..." required></textarea>
                        <button class="dc-btn blue"><x-icon name="pen" :size="17" /> Kirim Revisi ke Editor</button>
                      </form>
                    @endif
                  </div>
                </div>
              @endforeach
            </div>
          @endif
        </div>
      </section>
    @endif

    {{-- FILE FINAL --}}
    @if($canFinal)
      <section class="dc-sec">
        <div class="dc-sec-head">
          <x-icon name="check" :size="31" />
          <div><h2>File Final</h2><p>Draft yang sudah disetujui pengaju (ACC)</p></div>
        </div>
        <div class="dc-body">
          <div class="dc-ditem">
            <div class="dc-dtop">
              <div class="dc-dfile">
                <x-icon name="check" :size="23" />
                <div style="min-width:0">
                  <div class="dc-fname">{{ $content->finalDraft->displayName() }}</div>
                  <div class="dc-fsize">{{ $content->finalDraft->size_label }}</div>
                </div>
              </div>
              <div class="dc-dact">
                @if($content->finalDraft->kind === 'file')
                  <a class="dc-btn blue" target="_blank" rel="noopener" href="{{ route('contents.final', ['content' => $content, 'inline' => 1]) }}"><x-icon name="eye" :size="18" /> View</a>
                @endif
                <a class="dc-btn grn" href="{{ route('contents.final', $content) }}"><x-icon name="download" :size="17" /> Download</a>
              </div>
            </div>
            @if($content->finalDraft->kind === 'text')<div class="dc-text">{{ $content->finalDraft->text }}</div>@endif
          </div>
        </div>
      </section>
    @endif

    {{-- CAPTION POSTINGAN --}}
    <section class="dc-sec">
      <div class="dc-sec-head">
        <x-icon name="chat" :size="31" />
        <div><h2>Caption Postingan</h2><p>Isi caption postingan kontenmu disini.</p></div>
      </div>
      <div class="dc-body">
        @if($canCaption)
          <form id="caption-form" method="POST" action="{{ route('contents.caption', $content) }}">
            @csrf @method('PUT')
            <textarea id="caption-box" name="caption" class="dc-caption" placeholder="Masukan caption disini" @if(filled($content->caption)) readonly @endif>{{ old('caption', $content->caption) }}</textarea>
          </form>
        @else
          <textarea class="dc-caption" readonly placeholder="Belum ada caption.">{{ $content->caption }}</textarea>
        @endif

        <div class="dc-btns right">
          @if($canCaption)
            <button type="button" class="dc-btn blue" onclick="dcEditCaption()"><x-icon name="pen" :size="16" /> Edit caption</button>
            <button type="submit" form="caption-form" class="dc-btn yel"><x-icon name="check" :size="17" /> Simpan</button>
          @elseif(filled($content->caption))
            <button type="button" class="dc-btn blue" data-caption="{{ $content->caption }}" onclick="dcCopy(this)"><x-icon name="copy" :size="16" /> Copy Caption</button>
          @endif

          @if($canReview)
            <form method="POST" action="{{ route('contents.approve', $content) }}" style="margin:0"
                  data-confirm="Draft dan caption sudah benar dan akan diteruskan ke Publisher."
                  data-confirm-title="ACC draft ini?" data-confirm-ok="Ya, ACC">
              @csrf
              <button class="dc-btn grn"><x-icon name="check" :size="17" /> ACC</button>
            </form>
          @endif
        </div>
      </div>
    </section>

    {{-- JADWAL PUBLISH --}}
    @if(in_array($content->status, ['Approved', 'Publish Terjadwal']) || $content->publish_at)
      <section class="dc-sec">
        <div class="dc-sec-head">
          <x-icon name="clock" :size="31" />
          <div>
            <h2>Jadwal Publish</h2>
            <p>{{ $content->publish_at ? $content->publish_at->translatedFormat('j F Y, H:i') : 'Belum dijadwalkan' }}</p>
          </div>
        </div>
        @if($canPublish)
          <form class="dc-body" method="POST" action="{{ route('contents.schedule', $content) }}">
            @csrf
            <div class="dc-row">
              <div class="dc-field grow">
                <label>Tanggal</label>
                <input type="date" name="publish_date" value="{{ old('publish_date', $content->publish_at?->format('Y-m-d')) }}" required>
              </div>
              <div class="dc-field grow">
                <label>Jam</label>
                <input type="time" name="publish_time" value="{{ old('publish_time', $content->publish_at?->format('H:i')) }}" required>
              </div>
            </div>
            <div class="dc-btns" style="margin-top:0">
              <button class="dc-btn yel"><x-icon name="clock" :size="17" /> {{ $content->publish_at ? 'Ubah Jadwal' : 'Jadwalkan Publish' }}</button>
            </div>
          </form>
        @endif
      </section>
    @endif

    {{-- TOMBOL AKSI BAWAH --}}
    @if($canUpdate || $canReview || $canReopen || $canPublish || $canDelete)
      <div class="dc-bar">
        @if($canUpdate)
          <button type="submit" form="info-form" class="dc-big yel"><x-icon name="check" :size="20" /> Simpan</button>
        @endif

        @if($canReopen)
          <form method="POST" action="{{ route('contents.reopen', $content) }}"
                data-confirm="Konten dibuka kembali untuk revisi dan file final dicabut dari Publisher."
                data-confirm-title="Revisi lagi?" data-confirm-ok="Ya, buka revisi">
            @csrf
            <button class="dc-big blue"><x-icon name="pen" :size="20" /> Revisi</button>
          </form>
        @elseif($canReview)
          <button type="button" class="dc-big blue" onclick="dcToggle('cpanel')"><x-icon name="pen" :size="20" /> Revisi</button>
        @endif

        @if($canPublish)
          <form method="POST" action="{{ route('contents.publish', $content) }}"
                data-confirm="Konten ini akan ditandai sudah dipublish."
                data-confirm-title="Tandai sudah publish?" data-confirm-ok="Ya, sudah publish">
            @csrf
            <button class="dc-big green"><x-icon name="upload" :size="20" /> Publish</button>
          </form>
        @endif

        @if($canDelete)
          <form method="POST" action="{{ route('contents.destroy', $content) }}"
                data-confirm="Konten {{ $content->title }} beserta semua draft, bahan, referensi, dan komentarnya akan dihapus permanen."
                data-confirm-title="Hapus konten?" data-confirm-ok="Ya, hapus konten" data-confirm-type="danger">
            @csrf @method('DELETE')
            <button class="dc-big red"><x-icon name="bin" :size="20" /> Hapus</button>
          </form>
        @endif
      </div>
    @endif
</div>

<script>
function dcToast(m){const t=document.createElement('div');t.className='dc-toast';t.textContent=m;document.body.appendChild(t);setTimeout(()=>t.remove(),2600);}
function dcToggle(id){
  const el=document.getElementById(id); if(!el) return;
  el.hidden=!el.hidden;
  if(!el.hidden){el.scrollIntoView({behavior:'smooth',block:'center'});const t=el.querySelector('textarea');if(t)t.focus();}
}
function dcPicked(input,id){
  const n=input.files.length;
  document.getElementById(id).textContent=n===0?'Belum ada file yang dipilih':(n===1?input.files[0].name:n+' file dipilih');
}
function dcEditCaption(){
  const t=document.getElementById('caption-box'); if(!t) return;
  t.removeAttribute('readonly'); t.focus(); t.setSelectionRange(t.value.length,t.value.length);
}
function dcCopy(btn){
  const text=btn.dataset.caption||''; const old=btn.innerHTML;
  const done=()=>{btn.textContent='✓ Tercopy';setTimeout(()=>btn.innerHTML=old,1500);};
  (navigator.clipboard?navigator.clipboard.writeText(text):Promise.reject()).then(done,()=>prompt('Salin caption:',text));
}
function dcBulkDelete(formId){
  const f=document.getElementById(formId); if(!f) return;
  if(!document.querySelector('input.dc-check[form="'+formId+'"]:checked')){dcToast('Centang item yang ingin dihapus terlebih dahulu.');return;}
  f.requestSubmit();
}
function dcDownload(boxId){
  const box=document.getElementById(boxId); if(!box) return;
  let items=[...box.querySelectorAll('.dc-item')];
  const picked=items.filter(i=>i.querySelector('.dc-check').checked);
  if(picked.length) items=picked;
  const urls=items.map(i=>i.dataset.dl).filter(Boolean);
  if(!urls.length){dcToast('Tidak ada file yang bisa diunduh.');return;}
  urls.forEach((u,i)=>setTimeout(()=>{const a=document.createElement('a');a.href=u;a.download='';document.body.appendChild(a);a.click();a.remove();},i*500));
}
</script>