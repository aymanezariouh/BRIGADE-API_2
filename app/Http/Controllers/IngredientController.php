<?php

namespace App\Http\Controllers;

use App\Docs\IngredientDocumentation;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IngredientController extends Controller implements IngredientDocumentation
{
    public function index()
    {
        return response()->json([
            'ingredients' => Ingredient::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        $ingredient = Ingredient::create([
            'name' => $data['name'],
            'tags' => array_values(array_unique($data['tags'])),
        ]);

        return response()->json([
            'message' => 'Ingredient created successfully.',
            'ingredient' => $ingredient,
        ], 201);
    }

    public function update(Request $request, Ingredient $ingredient)
    {
        $data = $this->validatedData($request, $ingredient->id);

        $ingredient->update([
            'name' => $data['name'],
            'tags' => array_values(array_unique($data['tags'])),
        ]);

        return response()->json([
            'message' => 'Ingredient updated successfully.',
            'ingredient' => $ingredient->fresh(),
        ]);
    }

    public function destroy(Ingredient $ingredient)
    {
        $ingredient->delete();

        return response()->json([
            'message' => 'Ingredient deleted successfully.',
        ]);
    }

    private function validatedData(Request $request, ?int $ingredientId = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('ingredients', 'name')->ignore($ingredientId),
            ],
            'tags' => ['required', 'array'],
            'tags.*' => ['string', Rule::in(Ingredient::TAGS)],
        ]);
    }
}
