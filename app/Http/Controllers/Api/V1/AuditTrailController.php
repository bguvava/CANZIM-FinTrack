<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AuditTrailController extends Controller
{
    /**
     * Display a listing of audit trails with filtering and search
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('manage-settings');

        $query = AuditTrail::with('user:id,name,email');

        // Search by description or auditable type
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('auditable_type', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%");
            });
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by auditable type
        if ($request->filled('auditable_type')) {
            $query->where('auditable_type', 'like', "%{$request->auditable_type}%");
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Pagination
        $auditTrails = $query->latest('created_at')->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $auditTrails,
        ]);
    }

    /**
     * Display the specified audit trail
     */
    public function show(AuditTrail $auditTrail): JsonResponse
    {
        Gate::authorize('manage-settings');

        $auditTrail->load('user:id,name,email');

        return response()->json([
            'success' => true,
            'data' => $auditTrail,
        ]);
    }

    /**
     * Export audit trails to CSV
     */
    public function export(Request $request): JsonResponse
    {
        Gate::authorize('manage-settings');

        $query = AuditTrail::with('user:id,name,email');

        // Apply same filters as index
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('auditable_type', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%");
            });
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('auditable_type')) {
            $query->where('auditable_type', 'like', "%{$request->auditable_type}%");
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $auditTrails = $query->latest('created_at')->get();

        // Generate CSV content
        $csv = "ID,User,Action,Module,Description,IP Address,Date/Time\n";

        foreach ($auditTrails as $trail) {
            $csv .= sprintf(
                '%d,"%s","%s","%s","%s","%s","%s"'."\n",
                $trail->id,
                $trail->user ? $trail->user->name : 'System',
                $trail->action,
                class_basename($trail->auditable_type ?? 'System'),
                str_replace('"', '""', $trail->description ?? ''),
                $trail->ip_address ?? '',
                $trail->created_at->format('Y-m-d H:i:s')
            );
        }

        return response()->json([
            'success' => true,
            'data' => [
                'filename' => 'audit_trail_'.now()->format('Y-m-d_His').'.csv',
                'content' => base64_encode($csv),
            ],
        ]);
    }

    /**
     * Get available filter options
     */
    public function filters(): JsonResponse
    {
        Gate::authorize('manage-settings');

        $actions = AuditTrail::select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $auditableTypes = AuditTrail::select('auditable_type')
            ->distinct()
            ->whereNotNull('auditable_type')
            ->orderBy('auditable_type')
            ->pluck('auditable_type')
            ->map(function ($type) {
                return [
                    'value' => $type,
                    'label' => class_basename($type),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'actions' => $actions,
                'auditable_types' => $auditableTypes,
            ],
        ]);
    }
}
