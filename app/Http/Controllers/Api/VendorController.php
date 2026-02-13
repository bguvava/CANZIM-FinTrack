<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    /**
     * Display a listing of vendors.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Vendor::query()->orderBy('name');

        // Filter by active status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('vendor_code', 'like', '%'.$request->search.'%')
                    ->orWhere('name', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%')
                    ->orWhere('contact_person', 'like', '%'.$request->search.'%');
            });
        }

        $vendors = $query->paginate($request->get('per_page', 15));

        return response()->json($vendors);
    }

    /**
     * Store a newly created vendor.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:vendors,email',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'tax_id' => 'nullable|string|max:50',
            'payment_terms' => 'nullable|string|max:255',
        ]);

        $validated['vendor_code'] = Vendor::generateVendorCode();
        $validated['is_active'] = true;

        $vendor = Vendor::create($validated);

        ActivityLog::log(auth()->id(), 'vendor_created', 'Vendor created: '.$vendor->name, [
            'vendor_id' => $vendor->id,
            'vendor_code' => $vendor->vendor_code,
        ]);

        return response()->json([
            'message' => 'Vendor created successfully',
            'vendor' => $vendor,
        ], 201);
    }

    /**
     * Display the specified vendor.
     */
    public function show(Vendor $vendor): JsonResponse
    {
        $vendor->load(['purchaseOrders' => function ($query) {
            $query->latest()->limit(10);
        }]);

        return response()->json($vendor);
    }

    /**
     * Update the specified vendor.
     */
    public function update(Request $request, Vendor $vendor): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'contact_person' => 'nullable|string|max:255',
                'email' => 'nullable|email|unique:vendors,email,'.$vendor->id,
                'phone' => 'nullable|string|max:50',
                'address' => 'nullable|string|max:500',
                'tax_id' => 'nullable|string|max:50',
                'payment_terms' => 'nullable|string|max:255',
            ]);

            $vendor->update($validated);

            ActivityLog::log(auth()->id(), 'vendor_updated', 'Vendor updated: '.$vendor->name, [
                'vendor_id' => $vendor->id,
                'vendor_code' => $vendor->vendor_code,
            ]);

            return response()->json([
                'message' => 'Vendor updated successfully',
                'vendor' => $vendor,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating vendor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Deactivate the specified vendor.
     */
    public function deactivate(Vendor $vendor): JsonResponse
    {
        try {
            $vendor->update(['is_active' => false]);

            return response()->json([
                'message' => 'Vendor deactivated successfully',
                'vendor' => $vendor,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deactivating vendor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Activate the specified vendor.
     */
    public function activate(Vendor $vendor): JsonResponse
    {
        try {
            $vendor->update(['is_active' => true]);

            return response()->json([
                'message' => 'Vendor activated successfully',
                'vendor' => $vendor,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error activating vendor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get vendor summary with purchase orders.
     */
    public function summary(Vendor $vendor): JsonResponse
    {
        $summary = [
            'vendor' => $vendor,
            'total_orders' => $vendor->purchaseOrders()->count(),
            'total_value' => $vendor->purchaseOrders()->sum('total_amount'),
            'pending_orders' => $vendor->purchaseOrders()->pending()->count(),
            'completed_orders' => $vendor->purchaseOrders()->completed()->count(),
            'recent_orders' => $vendor->purchaseOrders()
                ->with('project')
                ->latest()
                ->limit(5)
                ->get(),
        ];

        return response()->json($summary);
    }

    /**
     * Remove the specified vendor.
     */
    public function destroy(Vendor $vendor): JsonResponse
    {
        try {
            // Check if vendor has purchase orders
            if ($vendor->purchaseOrders()->count() > 0) {
                return response()->json([
                    'message' => 'Cannot delete vendor with existing purchase orders. Deactivate instead.',
                ], 422);
            }

            $vendor->delete();

            return response()->json([
                'message' => 'Vendor deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting vendor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
