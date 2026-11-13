<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class MentorSaja
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless($request->user()?->adalahMentor(), 403, 'Halaman ini hanya untuk mentor.');
        return $next($request);
    }
}
