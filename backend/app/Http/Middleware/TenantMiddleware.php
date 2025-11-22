<?php

namespace App\Http\Middleware;

use App\Support\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = optional($request->user())->salon_id ?? $request->session()->get('salon_id');

        abort_unless($tenantId, Response::HTTP_UNAUTHORIZED);

        Tenant::set((int) $tenantId);

        /** @var Response $response */
        $response = $next($request);

        Tenant::clear();

        return $response;
    }
}
