<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsCustomer
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->isCustomer()) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'Halaman ini hanya untuk akun customer.');
        }

        return $next($request);
    }
}
