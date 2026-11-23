<?php

namespace App\Http\Middleware;

use App\Support\PesananSesi;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Sebelum halaman diproses, batalkan pesanan yang lewat batas waktu bayar (5 menit)
// supaya slotnya langsung tersedia lagi untuk pengguna lain.
class BatalkanPesananLewatBatas
{
    public function handle(Request $request, Closure $next): Response
    {
        PesananSesi::batalkanLewatBatasBayar();

        return $next($request);
    }
}
