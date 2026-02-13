<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBankAccountRequest;
use App\Models\ActivityLog;
use App\Models\BankAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    /**
     * Display a listing of bank accounts.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', BankAccount::class);

        $query = BankAccount::query()->orderBy('account_name');

        // Filter by active status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filter by currency
        if ($request->filled('currency')) {
            $query->where('currency', $request->currency);
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('account_name', 'like', '%'.$request->search.'%')
                    ->orWhere('account_number', 'like', '%'.$request->search.'%')
                    ->orWhere('bank_name', 'like', '%'.$request->search.'%');
            });
        }

        $accounts = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $accounts->items(),
            'meta' => [
                'current_page' => $accounts->currentPage(),
                'from' => $accounts->firstItem(),
                'last_page' => $accounts->lastPage(),
                'per_page' => $accounts->perPage(),
                'to' => $accounts->lastItem(),
                'total' => $accounts->total(),
            ],
        ]);
    }

    /**
     * Store a newly created bank account.
     */
    public function store(StoreBankAccountRequest $request): JsonResponse
    {
        $this->authorize('create', BankAccount::class);

        try {
            $data = $request->validated();
            $data['is_active'] = true;

            $account = BankAccount::create($data);

            ActivityLog::log(auth()->id(), 'bank_account_created', 'Bank account created: '.$account->account_name, [
                'bank_account_id' => $account->id,
                'account_name' => $account->account_name,
                'bank_name' => $account->bank_name,
            ]);

            return response()->json([
                'message' => 'Bank account created successfully',
                'account' => $account,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating bank account',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified bank account.
     */
    public function show(BankAccount $bankAccount): JsonResponse
    {
        $this->authorize('view', $bankAccount);

        $bankAccount->load(['cashFlows' => function ($query) {
            $query->latest()->limit(10);
        }]);

        return response()->json($bankAccount);
    }

    /**
     * Update the specified bank account.
     */
    public function update(Request $request, BankAccount $bankAccount): JsonResponse
    {
        $this->authorize('update', $bankAccount);

        try {
            $validated = $request->validate([
                'account_name' => 'sometimes|string|max:255',
                'bank_name' => 'sometimes|string|max:255',
                'branch' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:1000',
            ]);

            $bankAccount->update($validated);

            return response()->json([
                'message' => 'Bank account updated successfully',
                'account' => $bankAccount,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating bank account',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Deactivate the specified bank account.
     */
    public function deactivate(BankAccount $bankAccount): JsonResponse
    {
        $this->authorize('deactivate', $bankAccount);

        try {
            $bankAccount->update(['is_active' => false]);

            return response()->json([
                'message' => 'Bank account deactivated successfully',
                'account' => $bankAccount,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deactivating bank account',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Activate the specified bank account.
     */
    public function activate(BankAccount $bankAccount): JsonResponse
    {
        $this->authorize('update', $bankAccount);

        try {
            $bankAccount->update(['is_active' => true]);

            return response()->json([
                'message' => 'Bank account activated successfully',
                'account' => $bankAccount,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error activating bank account',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get account summary with recent transactions.
     */
    public function summary(BankAccount $bankAccount): JsonResponse
    {
        $this->authorize('view', $bankAccount);

        $summary = [
            'account' => $bankAccount,
            'total_inflows' => $bankAccount->inflows()->sum('amount'),
            'total_outflows' => $bankAccount->outflows()->sum('amount'),
            'recent_transactions' => $bankAccount->cashFlows()
                ->with(['project', 'donor', 'expense'])
                ->latest()
                ->limit(10)
                ->get(),
        ];

        return response()->json($summary);
    }
}
