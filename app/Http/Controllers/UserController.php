<?php

namespace App\Http\Controllers;

use App\Docs\UserDocumentation;
use Illuminate\Http\Request;

class UserController extends Controller implements UserDocumentation
{
    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load('profile'),
        ]);
    }
}
