@php $extra = $extra ?? []; @endphp
<div class="input-tabs" style="margin-top:10px">
  <button type="button" class="input-tab active" onclick="switchInput('{{ $prefix }}','file',this)">📎 File</button>
  <button type="button" class="input-tab" onclick="switchInput('{{ $prefix }}','text',this)">✎ Teks</button>
  <button type="button" class="input-tab" onclick="switchInput('{{ $prefix }}','link',this)">🔗 Link</button>
</div>

<div id="{{ $prefix }}-file" class="input-panel active">
  <form method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="kind" value="file">
    @foreach($extra as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
    <div class="upload"><input type="file" name="files[]" multiple onchange="this.form.submit()"></div>
  </form>
</div>

<div id="{{ $prefix }}-text" class="input-panel">
  <form method="POST" action="{{ $action }}">
    @csrf
    <input type="hidden" name="kind" value="text">
    @foreach($extra as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
    <div class="inline-edit">
      <input name="text" placeholder="{{ $textPlaceholder ?? 'Tulis teks...' }}" required>
      <button class="btn secondary">Tambah</button>
    </div>
  </form>
</div>

<div id="{{ $prefix }}-link" class="input-panel">
  <form method="POST" action="{{ $action }}">
    @csrf
    <input type="hidden" name="kind" value="link">
    @foreach($extra as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
    <div class="inline-edit">
      <input type="url" name="text" placeholder="https://..." required>
      <button class="btn secondary">Tambah</button>
    </div>
  </form>
</div>
