@props(['class' => 'h-10'])

{{-- Taruh logo di public/images/logo.png, otomatis dipakai. Kalau belum ada, tampil teks. --}}
@if (file_exists(public_path('images/logo.png')))
    <img src="{{ asset('images/logo.png') }}" alt="Markir.In" {{ $attributes->merge(['class' => $class . ' w-auto']) }}>
@else
    <span {{ $attributes->merge(['class' => 'inline-block font-black tracking-tight text-brand-900 text-3xl']) }}>MARKIR<span class="text-brand-500">.in</span></span>
@endif
