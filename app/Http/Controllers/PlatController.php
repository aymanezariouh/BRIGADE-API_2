<?php

namespace App\Http\Controllers;

use App\Docs\PlatDocumentation;
use App\Models\Plate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PlatController extends Controller implements PlatDocumentation
{
    private const IMAGE_DISK = 'public';

    public function index(Request $request)
    {
        $query = Plate::query()
            ->with(['category', 'ingredients'])
            ->orderBy('name');

        if (! $request->user()->isAdmin()) {
            $query
                ->where('is_available', true)
                ->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('is_active', true));
        }

        return response()->json([
            'plates' => $query->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        $plate = Plate::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'image' => $this->storeImage($request),
            'is_available' => $data['is_available'] ?? true,
            'category_id' => $data['category_id'],
            'user_id' => $request->user()->id,
        ]);

        $plate->ingredients()->sync($data['ingredient_ids'] ?? []);

        return response()->json([
            'message' => 'Plate created successfully.',
            'plate' => $plate->load(['category', 'ingredients']),
        ], 201);
    }

    public function show(Request $request, Plate $plate)
    {
        $this->abortIfHiddenForCustomer($request, $plate);

        return response()->json([
            'plate' => $plate->load(['category', 'ingredients']),
        ]);
    }

    public function update(Request $request, Plate $plate)
    {
        $data = $this->validatedData($request);

        if ($request->hasFile('image')) {
            $this->deleteImage($plate->image);
        }

        $plate->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'image' => $request->hasFile('image') ? $this->storeImage($request) : $plate->image,
            'is_available' => $data['is_available'] ?? $plate->is_available,
            'category_id' => $data['category_id'],
        ]);

        $plate->ingredients()->sync($data['ingredient_ids'] ?? []);

        return response()->json([
            'message' => 'Plate updated successfully.',
            'plate' => $plate->fresh()->load(['category', 'ingredients']),
        ]);
    }

    public function destroy(Plate $plate)
    {
        $this->deleteImage($plate->image);
        $plate->delete();

        return response()->json([
            'message' => 'Plate deleted successfully.',
        ]);
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'is_available' => ['sometimes', 'boolean'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'ingredient_ids' => ['nullable', 'array'],
            'ingredient_ids.*' => ['integer', Rule::exists('ingredients', 'id')],
        ]);
    }

    private function storeImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        return $request->file('image')->store('plates', self::IMAGE_DISK);
    }

    private function deleteImage(?string $path): void
    {
        if ($path) {
            Storage::disk(self::IMAGE_DISK)->delete($path);
        }
    }

    private function abortIfHiddenForCustomer(Request $request, Plate $plate): void
    {
        abort_if(
            ! $request->user()->isAdmin() && (
                ! $plate->is_available
                || ! $plate->category
                || ! $plate->category->is_active
            ),
            404,
            'Plate not found.'
        );
    }
}
