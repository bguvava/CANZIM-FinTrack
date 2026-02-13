<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommunicationRequest;
use App\Http\Requests\UpdateCommunicationRequest;
use App\Models\Communication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommunicationController extends Controller
{
    /**
     * List communications with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Communication::class);

        $query = Communication::with(['communicable', 'creator']);

        // Filter by entity type and ID
        if ($request->filled('communicable_type')) {
            $query->where('communicable_type', $request->communicable_type);
        }

        if ($request->filled('communicable_id')) {
            $query->where('communicable_id', $request->communicable_id);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('communication_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('communication_date', '<=', $request->date_to);
        }

        $communications = $query->latest('communication_date')->paginate(25);

        return response()->json([
            'success' => true,
            'data' => $communications,
        ]);
    }

    /**
     * Store a new communication.
     */
    public function store(StoreCommunicationRequest $request): JsonResponse
    {
        $this->authorize('create', Communication::class);

        try {
            $data = $request->validated();

            // Handle file upload if present
            if ($request->hasFile('attachment')) {
                $path = $request->file('attachment')->store('communications', 'public');
                $data['attachment_path'] = $path;
            }

            $data['created_by'] = auth()->id();

            $communication = Communication::create($data);
            $communication->load('creator');

            return response()->json([
                'success' => true,
                'message' => 'Communication logged successfully',
                'data' => $communication,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to log communication: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing communication.
     */
    public function update(UpdateCommunicationRequest $request, Communication $communication): JsonResponse
    {
        $this->authorize('update', $communication);

        try {
            $data = $request->validated();

            // Handle file upload if present
            if ($request->hasFile('attachment')) {
                // Delete old attachment if exists
                if ($communication->attachment_path) {
                    \Storage::disk('public')->delete($communication->attachment_path);
                }

                $path = $request->file('attachment')->store('communications', 'public');
                $data['attachment_path'] = $path;
            }

            $communication->update($data);
            $communication->load('creator');

            return response()->json([
                'success' => true,
                'message' => 'Communication updated successfully',
                'data' => $communication,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update communication: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a communication.
     */
    public function destroy(Communication $communication): JsonResponse
    {
        $this->authorize('delete', $communication);

        try {
            // Delete attachment if exists
            if ($communication->attachment_path) {
                \Storage::disk('public')->delete($communication->attachment_path);
            }

            $communication->delete();

            return response()->json([
                'success' => true,
                'message' => 'Communication deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete communication: '.$e->getMessage(),
            ], 500);
        }
    }
}
