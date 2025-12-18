<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!Auth::check() || !$user || !$user->isAdmin()) {
            $userId = $user?->id ?? 'guest';
            $userName = $user?->name ?? 'guest';
            Log::warning("Unauthorized access attempt by user {$userName} (ID: {$userId}) to {$request->method()} {$request->fullUrl()}");

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized. Admin access required.'], 403);
            }

            abort(403, 'Unauthorized. Admin access required.');
        }

        return $next($request);
    }
}