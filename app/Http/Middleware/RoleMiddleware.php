<?php

namespace App\Http\Middleware;

class RoleMiddleware
{
    public function handle($request, $next, $role)
    {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $role) {
            header('Location: /dashboard');
            exit;
        }

        return $next($request);
    }
} 