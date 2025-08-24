<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Category::where('user_id', Auth::id())
            ->with('parent:id,name,type');

        // Filter by type if provided
        if ($request->filled('type')) {
            $type = $request->input('type');
            if (in_array($type, ['income', 'expense'])) {
                $query->where('type', $type);
            }
        }

        $categories = $query->orderBy('type')
            ->orderBy('name')
            ->get();

        return response()->json([
            'message' => 'Categories retrieved successfully',
            'data' => $categories,
        ]);
    }

    /**
     * Store a newly created category.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        try {
            $category = Category::create([
                'user_id' => Auth::id(),
                'name' => $request->validated()['name'],
                'type' => $request->validated()['type'],
                'parent_id' => $request->validated()['parent_id'] ?? null,
            ]);

            $category->load('parent:id,name,type');

            return response()->json([
                'message' => 'Category created successfully',
                'data' => $category,
            ], 201);
        } catch (QueryException $e) {
            // Handle unique constraint violation
            if ($e->errorInfo[1] === 1062) { // MySQL duplicate entry error
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => [
                        'name' => ['A category with this name and type already exists.']
                    ]
                ], 422);
            }

            return response()->json([
                'message' => 'An error occurred while creating the category'
            ], 500);
        }
    }

    /**
     * Display the specified category.
     */
    public function show(Category $category): JsonResponse
    {
        // Check if the category belongs to the authenticated user
        if ($category->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Category not found'
            ], 404);
        }

        $category->load('parent:id,name,type', 'children:id,name,type,parent_id');

        return response()->json([
            'message' => 'Category retrieved successfully',
            'data' => $category,
        ]);
    }

    /**
     * Update the specified category.
     */
    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        // Check if the category belongs to the authenticated user
        if ($category->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Category not found'
            ], 404);
        }

        try {
            $validatedData = $request->validated();

            // Check if changing type would break parent-child relationships
            if (isset($validatedData['type']) && $validatedData['type'] !== $category->type) {
                // Check if category has children with different type
                $hasChildren = $category->children()->where('type', '!=', $validatedData['type'])->exists();
                if ($hasChildren) {
                    return response()->json([
                        'message' => 'Validation error',
                        'errors' => [
                            'type' => ['Cannot change type: category has children of different type.']
                        ]
                    ], 422);
                }

                // Check if parent exists and has same type
                if ($category->parent_id) {
                    $parentType = $category->parent->type;
                    if ($parentType !== $validatedData['type']) {
                        return response()->json([
                            'message' => 'Validation error',
                            'errors' => [
                                'type' => ['Cannot change type: parent category has different type.']
                            ]
                        ], 422);
                    }
                }
            }

            // Check for circular reference if parent_id is being updated
            if (isset($validatedData['parent_id']) && $validatedData['parent_id']) {
                if ($this->wouldCreateCircularReference($category, $validatedData['parent_id'])) {
                    return response()->json([
                        'message' => 'Validation error',
                        'errors' => [
                            'parent_id' => ['Cannot set parent: would create circular reference.']
                        ]
                    ], 422);
                }
            }

            $category->update($validatedData);
            $category->load('parent:id,name,type', 'children:id,name,type,parent_id');

            return response()->json([
                'message' => 'Category updated successfully',
                'data' => $category,
            ]);
        } catch (QueryException $e) {
            // Handle unique constraint violation
            if ($e->errorInfo[1] === 1062) { // MySQL duplicate entry error
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => [
                        'name' => ['A category with this name and type already exists.']
                    ]
                ], 422);
            }

            return response()->json([
                'message' => 'An error occurred while updating the category'
            ], 500);
        }
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Category $category): JsonResponse
    {
        // Check if the category belongs to the authenticated user
        if ($category->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Category not found'
            ], 404);
        }

        // Check if category has any transactions
        if ($category->transactions()->exists()) {
            return response()->json([
                'message' => 'Cannot delete category that has transactions'
            ], 422);
        }

        // Check if category has children
        if ($category->children()->exists()) {
            return response()->json([
                'message' => 'Cannot delete category that has subcategories'
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully'
        ]);
    }

    /**
     * Check if setting a parent would create a circular reference.
     */
    private function wouldCreateCircularReference(Category $category, int $parentId): bool
    {
        $currentParent = Category::find($parentId);

        while ($currentParent) {
            if ($currentParent->id === $category->id) {
                return true;
            }
            $currentParent = $currentParent->parent;
        }

        return false;
    }
}
