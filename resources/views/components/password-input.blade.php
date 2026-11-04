@props([
    'name'         => 'password',
    'id'           => null,
    'min'          => null,
    'match'        => null,
    'placeholder'  => '',
    'autocomplete' => 'current-password',
    'required'     => true,
])
@php $id = $id ?: $name . '-' . uniqid(); @endphp

<div class="pw-wrap">
  <input type="password" id="{{ $id }}" name="{{ $name }}"
         class="@error($name) invalid @enderror"
         placeholder="{{ $placeholder }}" autocomplete="{{ $autocomplete }}"
         @if($required) required @endif
         @if($min) data-min="{{ $min }}" @endif
         @if($match) data-match="{{ $match }}" @endif
         oninput="pwCheck(this)">
  <button type="button" class="pw-eye" onclick="pwToggle(this)" aria-label="Tampilkan kata sandi" title="Tampilkan / sembunyikan kata sandi">
    <svg class="eye" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
    <svg class="eye-off" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
  </button>
</div>
@if($min || $match)<div class="pw-hint" data-hint-for="{{ $id }}"></div>@endif
@error($name)<div class="error-text">{{ $message }}</div>@enderror