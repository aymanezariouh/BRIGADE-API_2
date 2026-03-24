<?php

namespace App\Http\Controllers;

use App\Docs\ProfileDocumentation;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller implements ProfileDocumentation
{
    public function show(Request $request)
    {
        return response()->json([
            'profile' => $this->profileFor($request),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'dietary_tags' => ['required', 'array'],
            'dietary_tags.*' => ['string', Rule::in(Profile::DIETARY_TAGS)],
        ]);

        $dietaryTags = array_values(array_unique($data['dietary_tags']));
        $profile = $this->profileFor($request);

        $profile->update([
            'dietary_tags' => $dietaryTags,
        ]);

        $request->user()->update([
            'dietary_tags' => $dietaryTags,
        ]);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'profile' => $profile->fresh(),
        ]);
    }

    private function profileFor(Request $request): Profile
    {
        return $request->user()->profile()->firstOrCreate(
            [],
            ['dietary_tags' => $request->user()->dietary_tags ?? []]
        );
    }
}
