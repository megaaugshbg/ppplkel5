{{--
    Paginasi bertema (pengganti view paginasi bawaan Laravel).
    Dipakai: $paginator->links('components.paginasi')
--}}
@if ($paginator->hasPages())
    @php
        $tombol = 'inline-flex items-center gap-1 rounded-xl border px-4 py-2 text-sm font-semibold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus';
    @endphp
    <nav role="navigation" aria-label="Halaman riwayat" class="flex items-center justify-between gap-3">
        @if ($paginator->onFirstPage())
            <span aria-disabled="true" class="{{ $tombol }} cursor-not-allowed border-line text-slate-400">&lsaquo; Sebelumnya</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $tombol }} border-line bg-surface text-primary hover:bg-surface-alt">&lsaquo; Sebelumnya</a>
        @endif

        <p class="text-sm text-slate-500" aria-current="page">Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}</p>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $tombol }} border-line bg-surface text-primary hover:bg-surface-alt">Berikutnya &rsaquo;</a>
        @else
            <span aria-disabled="true" class="{{ $tombol }} cursor-not-allowed border-line text-slate-400">Berikutnya &rsaquo;</span>
        @endif
    </nav>
@endif
