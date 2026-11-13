<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class AdminSaja
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless($request->user()?->adalahAdmin(), 403, 'Halaman ini hanya untuk administrator.');
        return $next($request);
    }
}
