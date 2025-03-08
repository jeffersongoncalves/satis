<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use function App\Support\package_name;

class EnsureUserHasLicense
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $vendor = $request->route('vendor');
        $package = $request->route('package');
        [$name] = package_name("{$vendor}/{$package}");

        $teams = $user->teams()
            ->whereHas('licenses', function (Builder $query) use ($name) {
                $query->where('name', $name);
            })
            ->get();

        if ($teams->isEmpty()) {
            abort(401);
        }

        return $next($request);
    }
}
