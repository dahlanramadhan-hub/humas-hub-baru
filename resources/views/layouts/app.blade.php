<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Humas Content Hub') — Disnakertrans Jabar</title>
<link rel="stylesheet" href="{{ asset('css/hub.css') }}">
<style>
#navbar{position:fixed;top:0;left:0;height:3px;width:0;background:#145EAD;z-index:200;transition:width 8s cubic-bezier(.1,.6,.2,1)}
body.loading #navbar{width:90%}
body.loading{cursor:progress}
.cbox-overlay{position:fixed;inset:0;background:rgba(15,23,42,.45);backdrop-filter:blur(3px);display:flex;align-items:center;justify-content:center;z-index:100;padding:20px}
.cbox-overlay[hidden]{display:none}
.cbox{background:#fff;border-radius:18px;padding:26px 24px 22px;max-width:420px;width:100%;box-shadow:0 24px 60px rgba(0,43,76,.25);text-align:center}
.cbox-icon{font-size:34px;line-height:1;margin-bottom:8px}
.cbox h3{margin:0 0 6px;font-size:18px}
.cbox p{margin:0 0 20px;color:#52606d;font-size:14px;line-height:1.55}
.cbox-actions{display:flex;gap:10px;justify-content:center}
.cbox-actions .btn{min-width:110px;justify-content:center}
.pw-wrap{position:relative}
.pw-wrap input{padding-right:48px}
.pw-eye{position:absolute;right:8px;top:50%;transform:translateY(-50%);width:34px;height:34px;border:0;background:transparent;border-radius:8px;cursor:pointer;color:#64748b;display:flex;align-items:center;justify-content:center;padding:0}
.pw-eye:hover{background:#eef5fa;color:#0068af}
.pw-eye .eye-off{display:none}
.pw-eye.on .eye{display:none}
.pw-eye.on .eye-off{display:block}
.pw-hint{font-size:12px;margin-top:5px;font-weight:700}
.pw-hint.ok{color:#08730f}
.pw-hint.bad{color:#a32323}
input.invalid,select.invalid,textarea.invalid{border-color:#e11d48 !important;background:#fff8f8}
@media(min-width:801px){.stats{grid-template-columns:repeat(3,1fr)}}
a.btn,a.filter{text-decoration:none;display:inline-flex;align-items:center}
.filter.active{background:#e8f5fb;color:#0065a8;border-color:#79b9d9}
.flash{padding:12px 14px;border-radius:12px;margin-bottom:16px;font-size:13px;font-weight:700;background:#e7f7e9;color:#08730f;border:1px solid #bde6c3}
.flash.err{background:#fff0f0;color:#a32323;border-color:#ffd1d1}
.back{display:inline-block;margin-bottom:14px;font-size:13px;font-weight:700;color:#0068af;text-decoration:none}
.error-text{color:#a32323;font-size:12px;margin-top:4px}
.modal.page{margin:0 auto}
.form input[type=checkbox]{width:auto;margin-right:4px}
.form input[type=file]{width:auto;border:0;padding:0}
.inline-edit input{border:1px solid #d1d5db;border-radius:10px;padding:11px;outline:none}
.comment-form textarea{width:100%;border:1px solid #d1d5db;border-radius:10px;padding:11px}
form{margin:0}
</style>
</head>
<body>
<div id="navbar"></div>
@auth
<header class="topbar">
  <div class="brand">
    <img class="brand-logo" src="{{ asset('images/logo.png') }}" alt="Disnakertrans Provinsi Jawa Barat">
    <div class="brand-copy"><strong>Humas Content Hub</strong><small>DISNAKERTRANS PROVINSI JAWA BARAT</small></div>
  </div>
    <div class="userbox">
    <div class="account-profile">
      <span class="account-avatar">{{ \App\Models\Content::initials(auth()->user()->name) }}</span>
      <div class="account-info">
        <div class="account-name">{{ auth()->user()->name }}</div>
        <div class="account-role">{{ auth()->user()->roleLabel() }}</div>
      </div>
    </div>
    @if(auth()->user()->isSuperAdmin())
      <a class="btn ghost" href="{{ route('admin.users.index') }}">Kelola Akun</a>
    @endif
    <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn ghost">Keluar</button></form>
  </div>
</header>
@endauth

@yield('content')

@auth
<footer class="site-footer">
  <div class="footer-brand">
    <img class="footer-logo" src="{{ asset('images/logo.png') }}" alt="">
    <div><b>Humas Content Hub</b><span>Disnakertrans Provinsi Jawa Barat</span></div>
  </div>
  <div><span>Alur: Ajukan · Revisi · ACC · Final · Publish</span><span>© {{ date('Y') }} Humas</span></div>
</footer>
@endauth
<div id="confirmBox" class="cbox-overlay" hidden>
  <div class="cbox" role="dialog" aria-modal="true" aria-labelledby="cboxTitle">
    <div class="cbox-icon" id="cboxIcon">❓</div>
    <h3 id="cboxTitle">Konfirmasi</h3>
    <p id="cboxText"></p>
    <div class="cbox-actions">
      <button type="button" class="btn ghost" id="cboxCancel">Batal</button>
      <button type="button" class="btn primary" id="cboxOk">Ya, lanjutkan</button>
    </div>
  </div>
</div>
<script>
/* ---------- kotak konfirmasi ---------- */
let cboxForm = null;
function showConfirm(form, msg) {
  cboxForm = form;
  const danger = form.dataset.confirmType === 'danger';
  document.getElementById('cboxIcon').textContent = danger ? '🗑️' : '❓';
  document.getElementById('cboxTitle').textContent = form.dataset.confirmTitle || 'Konfirmasi';
  document.getElementById('cboxText').textContent = msg;
  const ok = document.getElementById('cboxOk');
  ok.textContent = form.dataset.confirmOk || 'Ya, lanjutkan';
  ok.className = 'btn ' + (danger ? 'danger' : 'primary');
  document.getElementById('confirmBox').hidden = false;
  ok.focus();
}
function closeConfirm() {
  document.getElementById('confirmBox').hidden = true;
  cboxForm = null;
}
document.getElementById('cboxCancel').addEventListener('click', closeConfirm);
document.getElementById('cboxOk').addEventListener('click', function () {
  const f = cboxForm;
  closeConfirm();
  if (f) { f.dataset.confirmed = '1'; f.requestSubmit(); }
});
document.getElementById('confirmBox').addEventListener('click', function (e) {
  if (e.target === this) closeConfirm();
});
document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') closeConfirm();
});

/* ---------- konfirmasi + cegah klik ganda pada semua form ---------- */
document.addEventListener('submit', function (e) {
  const form = e.target;

  if (form.dataset.confirm && !form.dataset.confirmed) {
    e.preventDefault();
    showConfirm(form, form.dataset.confirm);
    return;
  }
  if (form.dataset.busy) { e.preventDefault(); return; }

  form.dataset.busy = '1';
  setTimeout(function () {
    form.querySelectorAll('button:not([type="button"]), input[type="submit"]').forEach(function (b) {
      b.disabled = true;
      if (b.tagName === 'BUTTON') { b.dataset.label = b.textContent; b.textContent = 'Memproses…'; }
    });
  }, 0);
});

// tombol "Back" browser: kembalikan form ke keadaan semula
window.addEventListener('pageshow', function (e) {
  if (!e.persisted) return;
  document.querySelectorAll('form').forEach(function (f) {
    delete f.dataset.busy; delete f.dataset.confirmed;
    f.querySelectorAll('button').forEach(function (b) {
      b.disabled = false;
      if (b.dataset.label) b.textContent = b.dataset.label;
    });
  });
});
function switchInput(group, type, btn) {
  document.querySelectorAll('[id^="' + group + '-"]').forEach(el => el.classList.remove('active'));
  document.getElementById(group + '-' + type).classList.add('active');
  btn.parentElement.querySelectorAll('.input-tab').forEach(x => x.classList.remove('active'));
  btn.classList.add('active');
}
function pwToggle(btn) {
  const input = btn.parentElement.querySelector('input');
  const show = input.type === 'password';
  input.type = show ? 'text' : 'password';
  btn.classList.toggle('on', show);
  btn.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
}
function pwCheck(input) {
  // kolom pengulangan ikut dicek ulang saat kata sandi utama berubah
  document.querySelectorAll('[data-match="#' + input.id + '"]').forEach(pwCheck);

  const hint = document.querySelector('[data-hint-for="' + input.id + '"]');
  if (!hint) return;
  const v = input.value;
  if (v.length === 0) { hint.textContent = ''; hint.className = 'pw-hint'; return; }

  let ok = true, msg = '';
  if (input.dataset.min) {
    const min = +input.dataset.min;
    if (v.length < min) { ok = false; msg = '✗ Kurang ' + (min - v.length) + ' karakter lagi (minimal ' + min + ').'; }
    else { msg = '✓ Panjang kata sandi sudah cukup.'; }
  } else if (input.dataset.match) {
    const other = document.querySelector(input.dataset.match);
    ok = !!other && other.value === v;
    msg = ok ? '✓ Kata sandi cocok.' : '✗ Belum sama dengan kata sandi di atas.';
  }
  hint.textContent = msg;
  hint.className = 'pw-hint ' + (ok ? 'ok' : 'bad');
}
function showSelected(input, targetId) {
  const box = document.getElementById(targetId);
  box.innerHTML = '';
  [...input.files].forEach(f => {
    const row = document.createElement('div');
    row.className = 'file';
    const name = document.createElement('span'); name.className = 'file-name'; name.textContent = '📎 ' + f.name;
    const size = document.createElement('span'); size.className = 'file-size'; size.textContent = (f.size / 1048576).toFixed(2) + ' MB';
    row.append(name, size); box.appendChild(row);
  });
}
function copyCaption(btn) {
  const t = btn.dataset.caption || '';
  const done = () => { const o = btn.textContent; btn.textContent = '✓ Tercopy'; setTimeout(() => btn.textContent = o, 1500); };
  (navigator.clipboard ? navigator.clipboard.writeText(t) : Promise.reject()).then(done, () => prompt('Salin caption:', t));
}
(function () {
  const c = document.getElementById('clockChip'), d = document.getElementById('dateChip');
  if (!c || !d) return;
  function tick() {
    const n = new Date();
    c.textContent = n.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', timeZone: 'Asia/Jakarta' }).replace('.', ':');
    d.textContent = n.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', timeZone: 'Asia/Jakarta' });
  }
  tick(); setInterval(tick, 15000);
})();
document.addEventListener('submit', function (e) {
  if (!e.defaultPrevented) document.body.classList.add('loading');
});
document.addEventListener('click', function (e) {
  const a = e.target.closest('a[href]');
  if (!a || a.target || a.hasAttribute('download') || e.ctrlKey || e.metaKey || e.shiftKey) return;
  const href = a.getAttribute('href');
  if (!href || href.startsWith('#') || a.origin !== location.origin) return;
  if (/\/(download|final)(\/|$|\?)/.test(a.pathname)) return;   // unduhan tidak berpindah halaman
  document.body.classList.add('loading');
});
window.addEventListener('pageshow', function () { document.body.classList.remove('loading'); });
</script>
</body>
</html>
