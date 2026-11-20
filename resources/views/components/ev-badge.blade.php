@props(['konektor' => null, 'daya' => null])

@if ($konektor)
    <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-0.5 rounded-full bg-emerald-50 px-1.5 py-0.5 text-[10px] font-medium text-emerald-700']) }}>
        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M13 2 4 14h6l-1 8 9-12h-6l1-8Z"/></svg>
        {{ $konektor }}@if ($daya) · {{ $daya }} kW @endif
    </span>
@endif
