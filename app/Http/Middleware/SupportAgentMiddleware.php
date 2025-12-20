<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SupportAgentMiddleware
{
    /**
     * Allow admins OR TARUMT staff members to access support inbox routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = Auth::user();

        $isAllowed = Auth::check()
            && $user
            && ($user->isAdmin() || $user->isTarumtStaff());

        if (!$isAllowed) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized. Support agent access required.'], 403);
            }

            abort(403, 'Unauthorized. Support agent access required.');
        }

        return $next($request);
    }
}

