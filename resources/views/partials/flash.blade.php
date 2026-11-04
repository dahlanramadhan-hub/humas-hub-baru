@if(session('ok'))<div class="flash">{{ session('ok') }}</div>@endif
@if(session('err'))<div class="flash err">{{ session('err') }}</div>@endif
@if($errors->any())
  <div class="flash err">
    <div style="margin-bottom:4px">⚠️ Periksa kembali isian Anda:</div>
    <ul style="margin:0;padding-left:18px;font-weight:600">
      @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
  </div>
@endif