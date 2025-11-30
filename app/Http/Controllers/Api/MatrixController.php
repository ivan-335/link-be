<?php

namespace App\Http\Controllers\Api;

use App\Models\Matrix;
use App\Models\Cell;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Services\MatrixService;
use Symfony\Component\HttpFoundation\JsonResponse;

class MatrixController extends Controller
{
    protected MatrixService $matrixService;
    
    public function __construct(MatrixService $matrixService)
    {
        $this->matrixService = $matrixService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $order = $request->query('order', 'desc'); 
        $order = $order === 'asc' ? 'asc' : 'desc';
        $matrix = $request->user()->matrices()->with('cells')->orderBy('created_at', $order)->get();
        return new JsonResponse($matrix, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $grid = $request->input('grid');
        $size = (int)$request->input('size');
        $parsedMatrix = $this->matrixService->parseMatrix($grid, $size);

        if ($size === 0 || $size !== count($parsedMatrix[0])) {
            return response()->json(['error' => 'Invalid grid'], 400);
        }

        $matrix = $request->user()->matrices()->create([
            'name' => $request->input('name') ?? 'matrix' . time(),
            'size' => $size
        ]);

        $cells = $this->matrixService->populateCells($parsedMatrix, $matrix->id);

        Cell::insert($cells);

        return response()->json([
            'matrix' => $matrix,
            'size' => $size
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Matrix $matrix): JsonResponse
    {
        if ($matrix->user_id !== $request->user()->id) {
            return new JsonResponse(['message' => 'Forbidden'], 403);
        }
        return new JsonResponse(
            $request->user()->matrices()->with('cells')->find($matrix->id), 
            200
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Matrix $matrix): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'size' => 'sometimes|integer|min:1',
            'grid' => 'sometimes|string',
        ]);

        $matrix->update($validated);
        // dd($validated);
        if (isset($validated['grid']) && isset($validated['size'])) {
            $parsedMatrix = $this->matrixService->parseMatrix($validated['grid'], (int)$validated['size']);
            Cell::where('matrix_id', $matrix->id)->delete();
            $cells = $this->matrixService->populateCells($parsedMatrix, $matrix->id);
            Cell::insert($cells);
        }
        return new JsonResponse($matrix, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Matrix $matrix): JsonResponse
    {
        if ($matrix->user_id !== $request->user()->id) {
            return new JsonResponse(['message' => 'Forbidden'], 403);
        }
        Cell::where('matrix_id', $matrix->id)->delete();
        $matrix->delete();
        return new JsonResponse(['message' => 'Matrix deleted'], 200);
    }

    /**
     * Calculate the visibility of the matrix
     */
    public function calculate(Matrix $matrix): JsonResponse
    {
        $count = $this->matrixService->calculateVisibility($matrix);
        return new JsonResponse(['visible_book_stacks' => $count], 200);
    }
}
