<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    /**
     * Display a listing of accounts with filtering and search
     */
    public function index(Request $request): JsonResponse
    {
        $query = Account::where('user_id', Auth::id());

        // Search by name
        if ($request->has('q') && ! empty($request->q)) {
            $searchTerm = $request->q;
            $query->where('name', 'like', "%{$searchTerm}%");
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $isActive = filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($isActive !== null) {
                $query->where('is_active', $isActive);
            }
        }

        // Order by created_at desc
        $accounts = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'data' => $accounts,
            'meta' => [
                'total' => $accounts->count(),
                'filters_applied' => [
                    'search' => $request->q ?? null,
                    'is_active' => $request->is_active ?? null,
                ],
            ],
        ]);
    }

    /**
     * Store a newly created account
     */
    public function store(StoreAccountRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $account = Account::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'type' => $validated['type'],
            'starting_balance' => $validated['starting_balance'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'message' => 'Account created successfully',
            'data' => $account,
        ], 201);
    }

    /**
     * Display the specified account
     */
    public function show(string $id): JsonResponse
    {
        $account = Account::where('user_id', Auth::id())
            ->where('id', $id)
            ->first();

        if (! $account) {
            return response()->json([
                'message' => 'Account not found',
            ], 404);
        }

        return response()->json([
            'data' => $account,
        ]);
    }

    /**
     * Update the specified account
     */
    public function update(UpdateAccountRequest $request, string $id): JsonResponse
    {
        $account = Account::where('user_id', Auth::id())
            ->where('id', $id)
            ->first();

        if (! $account) {
            return response()->json([
                'message' => 'Account not found',
            ], 404);
        }

        $validated = $request->validated();
        $account->update($validated);

        return response()->json([
            'message' => 'Account updated successfully',
            'data' => $account->fresh(),
        ]);
    }

    /**
     * Remove the specified account
     */
    public function destroy(string $id): JsonResponse
    {
        $account = Account::where('user_id', Auth::id())
            ->where('id', $id)
            ->first();

        if (! $account) {
            return response()->json([
                'message' => 'Account not found',
            ], 404);
        }

        // Check if account has transactions
        if ($account->transactions()->exists()) {
            return response()->json([
                'message' => 'Cannot delete account that has transactions',
            ], 422);
        }

        $account->delete();

        return response()->json([
            'message' => 'Account deleted successfully',
        ]);
    }
}
