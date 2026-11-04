@extends('layouts.app')
@section('title', 'Daftar Konten')
@section('content')
<main class="container">
    @unless(isset($openContent) || isset($openCreate))
    @include('partials.flash')
  @endunless

  <div class="hero">
    <img class="hero-logo" src="{{ asset('images/logo.png') }}" alt="">
    <div>
      <div class="eyebrow">PUSAT PUBLIKASI HUMAS</div>
      <h1>Kelola konten tanpa ribet.</h1>
      <p>Semua proses publikasi dalam satu tempat — dari pengajuan sampai tayang.</p>
    </div>
    @can('create', \App\Models\Content::class)
      <a class="btn primary" href="{{ route('contents.create') }}">＋ Ajukan Konten</a>
    @endcan
  </div>

  <div class="stats">
    @foreach($cards as $key => $label)
      <div class="stat"><b>{{ $counts[$key] ?? 0 }}</b><span>{{ $label }}</span></div>
    @endforeach
  </div>

  <div class="toolbar">
    @foreach($filters as $key => $label)
      <a class="filter {{ $filter === $key ? 'active' : '' }}" href="{{ route('contents.index', ['status' => $key]) }}">{{ $label }}</a>
    @endforeach
  </div>

  <div class="grid">
    @forelse($contents as $c)
      @include('contents._card', ['c' => $c])
    @empty
      <div class="empty">Tidak ada konten pada status ini.</div>
    @endforelse
  </div>
</main>

{{-- POPUP: detail konten atau ajukan konten --}}
@if(isset($openContent) || isset($openCreate))
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700;900&display=swap">
  <link rel="stylesheet" href="{{ asset('css/detail.css') }}?v=6">

  <style>
    .dc-overlay{position:fixed;top:0;right:0;bottom:0;left:0;z-index:90;overflow-y:auto;overflow-x:hidden;-webkit-overflow-scrolling:touch;background:rgba(243,248,251,.62);backdrop-filter:blur(3.5px);-webkit-backdrop-filter:blur(3.5px)}
    .dc-overlay .dc{min-height:100%}
    .dc-scroll{position:fixed;right:22px;bottom:22px;z-index:95;display:flex;flex-direction:column;gap:8px}
    .dc-scroll button{width:44px;height:44px;border-radius:50%;border:1px solid #145EAD;background:#fff;color:#145EAD;font-size:20px;font-weight:700;line-height:1;cursor:pointer;box-shadow:0 8px 20px rgba(0,43,76,.25)}
    .dc-scroll button:hover{background:#E2F0FA}
  </style>

  <div class="dc-overlay" id="dcOverlay">
    <div class="dc">
      @isset($openContent)
        @include('contents._detail', ['content' => $openContent, 'editors' => $editors])
      @else
        @include('contents._create', ['editors' => $editors])
      @endisset
    </div>
  </div>

  <div class="dc-scroll">
    <button type="button" id="dcUp" title="Ke atas" aria-label="Gulir ke atas">↑</button>
    <button type="button" id="dcDown" title="Ke bawah" aria-label="Gulir ke bawah">↓</button>
  </div>

  <script>
  (function () {
    const overlay  = document.getElementById('dcOverlay');
    const closeUrl = @json(route('contents.index', ['status' => $filter]));
    const key      = 'dcScroll:' + location.pathname;

    const popupOk  = getComputedStyle(overlay).position === 'fixed';
    if (popupOk) document.body.style.overflow = 'hidden';

    const scroller = popupOk ? overlay : window;
    const height   = () => popupOk ? overlay.scrollHeight : document.documentElement.scrollHeight;
    document.getElementById('dcUp').addEventListener('click',   () => scroller.scrollTo({ top: 0, behavior: 'smooth' }));
    document.getElementById('dcDown').addEventListener('click', () => scroller.scrollTo({ top: height(), behavior: 'smooth' }));

    const saved = sessionStorage.getItem(key);
    if (saved) {
      try {
        const s = JSON.parse(saved);
        if (Number.isFinite(s.y) && Number.isFinite(s.t) && Date.now() - s.t < 8000) {
          popupOk ? (overlay.scrollTop = s.y) : window.scrollTo(0, s.y);
        }
      } catch (e) {
        sessionStorage.removeItem(key);
      }
      sessionStorage.removeItem(key);
    }

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !e.defaultPrevented) {
        window.location.assign(closeUrl);
      }
    });

    document.addEventListener('submit', function (e) {
      if (!e.defaultPrevented && e.target instanceof HTMLFormElement) {
        sessionStorage.setItem(key, JSON.stringify({ y: popupOk ? overlay.scrollTop : window.scrollY, t: Date.now() }));
      }
    })})();
  </script>
@endif
@endsection