<?php

namespace App\Http\Controllers;

use App\Docs\CategoryDocumentation;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller implements CategoryDocumentation
{
    public function index(Request $request)
    {
        $query = Category::query()
            ->withCount('plates')
            ->orderBy('name');

        if (! $request->user()->isAdmin()) {
            $query->where('is_active', true);
        }

        return response()->json([
            'categories' => $query->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        $category = Category::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'color' => $data['color'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Category created successfully.',
            'category' => $category->loadCount('plates'),
        ], 201);
    }

    public function show(Request $request, Category $category)
    {
        $this->abortIfHiddenForCustomer($request, $category);

        return response()->json([
            'category' => $category->loadCount('plates'),
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $data = $this->validatedData($request, $category->id);

        $category->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'color' => $data['color'] ?? null,
            'is_active' => $data['is_active'] ?? $category->is_active,
        ]);

        return response()->json([
            'message' => 'Category updated successfully.',
            'category' => $category->fresh()->loadCount('plates'),
        ]);
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully.',
        ]);
    }

    public function plates(Request $request, Category $category)
    {
        $this->abortIfHiddenForCustomer($request, $category);

        $query = $category->plates()
            ->with(['category', 'ingredients'])
            ->orderBy('name');

        if (! $request->user()->isAdmin()) {
            $query->where('is_available', true);
        }

        return response()->json([
            'category' => $category->loadCount('plates'),
            'plates' => $query->get(),
        ]);
    }

    private function validatedData(Request $request, ?int $categoryId = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'name')->ignore($categoryId),
            ],
            'description' => ['nullable', 'string'],
            'color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }

    private function abortIfHiddenForCustomer(Request $request, Category $category): void
    {
        abort_if(
            ! $request->user()->isAdmin() && ! $category->is_active,
            404,
            'Category not found.'
        );
    }
}
