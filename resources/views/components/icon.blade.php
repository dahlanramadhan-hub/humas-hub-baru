@props(['name', 'size' => 20])
<svg {{ $attributes->merge(['class' => 'ic']) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
@switch($name)
@case('doc')<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>@break
@case('clip')<path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>@break
@case('link')<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>@break
@case('clock')<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>@break
@case('chat')<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>@break
@case('check')<polyline points="20 6 9 17 4 12"/>@break
@case('bin')<polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>@break
@case('download')<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>@break
@case('upload')<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>@break
@case('plus')<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>@break
@case('pen')<path d="M17 3a2.83 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>@break
@case('eye')<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>@break
@case('x')<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>@break
@case('copy')<rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>@break
@case('instagram')<rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>@break
@case('tiktok')<path d="M16 3v10.5a4.5 4.5 0 1 1-4.5-4.5"/><path d="M16 3c.5 2.5 2.5 4.5 5 4.5"/>@break
@case('twitter')<path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"/>@break
@case('instagram-c')
<defs><linearGradient id="igGrad" x1="0" y1="24" x2="24" y2="0" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#FEDA75"/><stop offset=".35" stop-color="#FA7E1E"/><stop offset=".6" stop-color="#D62976"/><stop offset=".85" stop-color="#962FBF"/><stop offset="1" stop-color="#4F5BD5"/></linearGradient></defs>
<rect x="2" y="2" width="20" height="20" rx="5" ry="5" stroke="url(#igGrad)"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" stroke="url(#igGrad)"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5" stroke="url(#igGrad)"/>
@break
@case('tiktok-c')
<g transform="translate(.9 .9)" stroke="#FE2C55"><path d="M16 3v10.5a4.5 4.5 0 1 1-4.5-4.5"/><path d="M16 3c.5 2.5 2.5 4.5 5 4.5"/></g>
<g transform="translate(-.9 -.9)" stroke="#25F4EE"><path d="M16 3v10.5a4.5 4.5 0 1 1-4.5-4.5"/><path d="M16 3c.5 2.5 2.5 4.5 5 4.5"/></g>
<g stroke="#000"><path d="M16 3v10.5a4.5 4.5 0 1 1-4.5-4.5"/><path d="M16 3c.5 2.5 2.5 4.5 5 4.5"/></g>
@break
@case('twitter-c')
<path fill="#1D9BF0" stroke="#1D9BF0" stroke-width="1" d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"/>
@break
@endswitch
</svg>