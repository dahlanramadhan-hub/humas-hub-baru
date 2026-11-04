<article class="card">
  <div class="card-head">
    <div>
      <h3>{{ $c->title }}</h3>
      <div class="meta"><span>{{ $c->type }}</span><span>•</span><span>{{ implode(' · ', $c->channels) }}</span></div>
    </div>
    <span class="badge {{ $c->badgeClass() }}">{{ $c->status }}</span>
  </div>

  <div class="deadline"><b>Deadline:</b> {{ $c->deadline->translatedFormat('j M Y') }} · {{ $c->deadline_note }}</div>

  <div class="card-highlights">
    <span class="card-highlight social-highlight">📱 {{ implode(' · ', $c->channels) }}</span>
    @if($c->responsible_name)
      <span class="card-highlight pic-highlight">
        <span class="mini-avatar">{{ \App\Models\Content::initials($c->responsible_name) }}</span> ✏️ Editor: {{ $c->responsible_name }}
      </span>
    @endif
    @if($c->requester_name)
      <span class="card-highlight" style="background:#f1edff;color:#6246a5;border-color:#d9cff5">📝 Pengaju: {{ $c->requester_name }}</span>
    @endif
  </div>

  <div class="meta" style="margin-top:10px">
    @if($c->status === 'Publish')
      <span>✅ Selesai</span>
    @else
      <span>👉 Giliran {{ $c->turnLabel() }}</span>
    @endif
    @cannot('downloadFinal', $c)
      <span>📝 {{ $c->drafts_count }} draft</span>
    @endcannot
    @if($c->final_draft_id && $c->isFinished())<span>✅ Final tersedia</span>@endif
  </div>

  <div class="actions">
    <a class="btn primary" href="{{ route('contents.show', ['content' => $c, 'status' => $filter ?? 'Semua']) }}">Lihat Detail</a>
    @if($c->caption)
      <button type="button" class="btn secondary" data-caption="{{ $c->caption }}" onclick="copyCaption(this)">⧉ Copy Caption</button>
    @endif
    @can('downloadFinal', $c)
      <a class="btn secondary" href="{{ route('contents.final', $c) }}">↓ Download Final</a>
    @endcan
        @can('delete', $c)
      <form method="POST" action="{{ route('contents.destroy', $c) }}" style="display:inline"
            data-confirm="Konten {{ $c->title }} beserta semua draft, bahan, dan referensinya akan dihapus permanen."
            data-confirm-title="Hapus konten?" data-confirm-ok="Ya, hapus" data-confirm-type="danger">
        @csrf @method('DELETE')
        <button class="btn danger">🗑 Hapus</button>
      </form>
    @endcan
  </div>
</article>