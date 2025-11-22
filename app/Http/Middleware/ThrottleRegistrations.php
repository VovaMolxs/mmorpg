<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ThrottleRegistrations
{
    /**
     * Maximum number of registrations allowed per IP in 24 hours.
     */
    private const MAX_REGISTRATIONS_PER_IP = 3;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ipAddress = $request->ip();

        $registrationsCount = DB::table('registration_attempts')
            ->where('ip_address', $ipAddress)
            ->where('created_at', '>=', now()->subDay())
            ->count();

        if ($registrationsCount >= self::MAX_REGISTRATIONS_PER_IP) {
            return redirect()->back()
                ->withErrors([
                    'error' => 'Превышен лимит регистраций с вашего IP адреса. Пожалуйста, попробуйте позже.',
                ])
                ->withInput();
        }

        return $next($request);
    }
}
