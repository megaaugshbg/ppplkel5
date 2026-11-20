{{--
    Badge status booking. Selalu berisi TEKS + ikon (tidak hanya warna).
    $status: kode status (selesai, dibatalkan, ...) ; $label: teks yang ditampilkan.
--}}
@props(['status', 'label'])

@php
    $selesai = $status === \App\Models\Booking::SELESAI;
    $batal = $status === \App\Models\Booking::DIBATALKAN;
    $kelas = match (true) {
        $selesai => 'bg-emerald-50 text-emerald-700',
        $batal   => 'bg-surface-alt text-slate-600',
        default  => 'bg-primary-soft text-primary',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex shrink-0 items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold $kelas"]) }}>
    @if ($selesai)
        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.6" viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 5 5 9-10"/></svg>
    @elseif ($batal)
        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.6" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
    @endif
    {{ $label }}
</span>
