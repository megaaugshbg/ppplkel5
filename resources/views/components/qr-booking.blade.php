{{-- Komponen QR Code booking (SVG). --}}
@props(['kode', 'ukuran' => 'h-48 w-48'])

@php
    $svg = (string) \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
        ->size(300)
        ->margin(0)
        ->errorCorrection('M')
        ->generate($kode);

    $svg = substr($svg, (int) strpos($svg, '<svg'));
@endphp

<figure class="mx-auto w-fit text-center">
    <div class="rounded-2xl border border-line bg-white p-3">
        <div class="{{ $ukuran }} [&>svg]:h-full [&>svg]:w-full" role="img" aria-label="QR Code booking {{ $kode }}">
            {!! $svg !!}
        </div>
    </div>
    <figcaption class="mt-2 text-xs text-slate-500">
        Kode booking: <strong class="tracking-wide text-primary">{{ $kode }}</strong>
    </figcaption>
</figure>
