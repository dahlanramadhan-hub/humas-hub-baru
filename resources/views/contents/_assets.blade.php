<div class="section">
  <h4>{{ $title }}</h4>
  @if(!empty($note))<p class="item-note">{{ $note }}</p>@endif

  @forelse($items as $f)
    <div class="file">
      <div style="min-width:0">
        <div class="file-name">{{ $f->kind === 'link' ? '🔗' : ($f->kind === 'text' ? '✎' : '📎') }} {{ $f->name }}</div>
        <div class="file-size">{{ $f->size_label }}</div>
        @if($f->kind === 'text')<div class="item-note" style="margin-top:4px">{{ $f->text }}</div>@endif
      </div>
      <div style="display:flex;gap:6px;align-items:center">
        @if($f->kind === 'file')
          <a class="btn secondary" href="{{ route('assets.download', $f) }}">↓ Download</a>
        @elseif($f->kind === 'link')
          <a class="btn secondary" href="{{ $f->text }}" target="_blank" rel="noopener">↗ Buka</a>
        @endif
        @if($canManage)
          <form method="POST" action="{{ route('assets.destroy', $f) }}"
      data-confirm="Item ini akan dihapus dari daftar."
      data-confirm-title="Hapus item?" data-confirm-ok="Ya, hapus" data-confirm-type="danger">
            @csrf @method('DELETE')
            <button class="btn danger">Hapus</button>
          </form>
        @endif
      </div>
    </div>
  @empty
    <p>Belum ada item.</p>
  @endforelse

  @if($canManage)
    @include('contents._adder', [
      'action' => route('contents.assets.store', $content),
      'prefix' => $prefix,
      'extra'  => ['category' => $category],
      'textPlaceholder' => $textPlaceholder,
    ])
  @endif
</div>