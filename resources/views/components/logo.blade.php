@props(['height' => 40, 'tone' => 'default'])

{{--
    Logo Markir.In. Sumber: public/images/logo.png; yang ditampilkan logo-trim.png
    (versi tanpa ruang kosong transparan, supaya tinggi = tinggi tulisan).
    height : tinggi dalam px, lebar otomatis.
    tone="on-primary" : logo berwarna cream (token --color-on-primary) untuk latar biru/foto gelap.
    Style ditulis inline karena komponen ini juga dipakai di halaman tanpa Tailwind.
--}}
@php($src = asset('images/logo-trim.png'))

@if (! file_exists(public_path('images/logo-trim.png')))
    <span {{ $attributes }} style="display:inline-block;font-weight:900;font-size:{{ $height * 0.7 }}px;line-height:1;color:var(--color-primary)">MARKIR<span style="font-size:.8em">.in</span></span>
@elseif ($tone === 'on-primary')
    <span role="img" aria-label="Markir.In" {{ $attributes }}
          style="display:block;height:{{ $height }}px;aspect-ratio:707/353;background-color:var(--color-on-primary);-webkit-mask:url('{{ $src }}') center/contain no-repeat;mask:url('{{ $src }}') center/contain no-repeat"></span>
@else
    <img src="{{ $src }}" alt="Markir.In" {{ $attributes }} style="display:block;height:{{ $height }}px;width:auto">
@endif
