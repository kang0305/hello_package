<?php

namespace Kang\SystemLogPackage;

use Illuminate\Http\Request;
use Kang\SystemLogPackage\SystemLogHelper;
use Illuminate\Support\Facades\Auth;

class SystemLogMiddleware
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, \Closure $next)
    {
        $preSaveUserId = null;

        if (str_starts_with($request->route()->uri(), 'api')) {
            $preSaveUserId = $request->user()?->id ?? null;
        } else {
            $preSaveUserId = Auth::check() ? Auth::id() : null;
        }

        $response = $next($request);

        SystemLogHelper::formatSystemLog($request, $preSaveUserId, $response);

        return $response;
    }
}