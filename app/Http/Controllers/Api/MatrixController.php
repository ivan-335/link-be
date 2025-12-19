<?php

namespace App\Http\Controllers\Api;

use App\Models\Matrix;
use App\Models\Cell;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Services\MatrixService;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Http\Requests\Matrix\StoreMatrixRequest;
use OpenApi\Annotations as OA;

class MatrixController extends Controller
{
    protected MatrixService $matrixService;

    public function __construct(MatrixService $matrixService)
    {
        $this->matrixService = $matrixService;
    }
    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *   path="/api/matrix",
     *   tags={"Matrix"},
     *   summary="List matrices for the authenticated user",
     *   security={{"sanctum":{}}},
     *   @OA\Parameter(
     *     name="order",
     *     in="query",
     *     required=false,
     *     @OA\Schema(type="string", enum={"asc","desc"})
     *   ),
     *   @OA\Response(response=200, description="Matrix list"),
     *   @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $order = $request->query("order", "desc");
        $order = $order === "asc" ? "asc" : "desc";
        $matrix = $request
            ->user()
            ->matrices()
            ->with("cells")
            ->orderBy("created_at", $order)
            ->get();
        return new JsonResponse($matrix, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *   path="/api/matrix",
     *   tags={"Matrix"},
     *   summary="Create a matrix",
     *   security={{"sanctum":{}}},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"grid","size"},
     *       @OA\Property(property="name", type="string", example="My Matrix"),
     *       @OA\Property(property="grid", type="string", example="1 2 3\n4 5 6\n7 8 9"),
     *       @OA\Property(property="size", type="integer", example=3)
     *     )
     *   ),
     *   @OA\Response(response=200, description="Matrix created"),
     *   @OA\Response(response=400, description="Invalid grid"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(StoreMatrixRequest $request): JsonResponse
    {
        $grid = $request->input("grid");
        $size = (int) $request->input("size");
        $parsedMatrix = $this->matrixService->parseMatrix($grid, $size);

        if ($size === 0 || $size !== count($parsedMatrix[0])) {
            return response()->json(["error" => "Invalid grid"], 400);
        }

        $matrix = $request
            ->user()
            ->matrices()
            ->create([
                "name" => $request->input("name") ?? "matrix" . time(),
                "size" => $size,
            ]);

        $cells = $this->matrixService->populateCells(
            $parsedMatrix,
            $matrix->id,
        );

        Cell::insert($cells);

        return response()->json(
            [
                "matrix" => $matrix,
                "size" => $size,
            ],
            200,
        );
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *   path="/api/matrix/{matrix}",
     *   tags={"Matrix"},
     *   summary="Get a matrix by id",
     *   security={{"sanctum":{}}},
     *   @OA\Parameter(
     *     name="matrix",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(response=200, description="Matrix details"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function show(Request $request, Matrix $matrix): JsonResponse
    {
        if ($matrix->user_id !== $request->user()->id) {
            return new JsonResponse(["message" => "Forbidden"], 403);
        }
        return new JsonResponse(
            $request->user()->matrices()->with("cells")->find($matrix->id),
            200,
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *   path="/api/matrix/{matrix}",
     *   tags={"Matrix"},
     *   summary="Update a matrix",
     *   security={{"sanctum":{}}},
     *   @OA\Parameter(
     *     name="matrix",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\RequestBody(
     *     required=false,
     *     @OA\JsonContent(
     *       @OA\Property(property="name", type="string", example="Renamed Matrix"),
     *       @OA\Property(property="grid", type="string", example="1 2\n3 4"),
     *       @OA\Property(property="size", type="integer", example=2)
     *     )
     *   ),
     *   @OA\Response(response=200, description="Matrix updated"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden"),
     *   @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(Request $request, Matrix $matrix): JsonResponse
    {
        $validated = $request->validate([
            "name" => "sometimes|string|max:255",
            "size" => "sometimes|integer|min:1",
            "grid" => "sometimes|string",
        ]);

        $matrix->update($validated);

        if (isset($validated["grid"]) && isset($validated["size"])) {
            $parsedMatrix = $this->matrixService->parseMatrix(
                $validated["grid"],
                (int) $validated["size"],
            );
            Cell::where("matrix_id", $matrix->id)->delete();
            $cells = $this->matrixService->populateCells(
                $parsedMatrix,
                $matrix->id,
            );
            Cell::insert($cells);
        }
        return new JsonResponse($matrix, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *   path="/api/matrix/{matrix}",
     *   tags={"Matrix"},
     *   summary="Delete a matrix",
     *   security={{"sanctum":{}}},
     *   @OA\Parameter(
     *     name="matrix",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(response=200, description="Matrix deleted"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function destroy(Request $request, Matrix $matrix): JsonResponse
    {
        if ($matrix->user_id !== $request->user()->id) {
            return new JsonResponse(["message" => "Forbidden"], 403);
        }
        Cell::where("matrix_id", $matrix->id)->delete();
        $matrix->delete();
        return new JsonResponse(["message" => "Matrix deleted"], 200);
    }

    /**
     * Calculate the visibility of the matrix
     *
     * @OA\Get(
     *   path="/api/matrix/{matrix}/calculate",
     *   tags={"Matrix"},
     *   summary="Calculate visibility for a matrix",
     *   security={{"sanctum":{}}},
     *   @OA\Parameter(
     *     name="matrix",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(response=200, description="Visibility result"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function calculate(Matrix $matrix): JsonResponse
    {
        $count = $this->matrixService->calculateVisibility($matrix);
        return new JsonResponse(["visible_book_stacks" => $count], 200);
    }
}
