<?php

namespace App\Http\Controllers;

use App\Docs\AdminStatsDocumentation;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Plate;
use App\Models\Recommendation;
use App\Models\User;

class AdminStatsController extends Controller implements AdminStatsDocumentation
{
    public function show()
    {
        return response()->json([
            'stats' => [
                'users' => [
                    'total' => User::count(),
                    'admins' => User::where('role', User::ROLE_ADMIN)->count(),
                    'customers' => User::where('role', User::ROLE_CUSTOMER)->count(),
                ],
                'categories' => [
                    'total' => Category::count(),
                    'active' => Category::where('is_active', true)->count(),
                    'inactive' => Category::where('is_active', false)->count(),
                ],
                'plates' => [
                    'total' => Plate::count(),
                    'available' => Plate::where('is_available', true)->count(),
                    'unavailable' => Plate::where('is_available', false)->count(),
                ],
                'ingredients' => [
                    'total' => Ingredient::count(),
                ],
                'recommendations' => [
                    'total' => Recommendation::count(),
                    'ready' => Recommendation::where('status', 'ready')->count(),
                    'processing' => Recommendation::where('status', 'processing')->count(),
                    'average_score' => round((float) Recommendation::avg('score'), 2),
                ],
            ],
        ]);
    }
}
