<?php

namespace App\Http\Services;

use App\Models\Matrix;

class MatrixService
{
    // Service methods would go here
    public function parseMatrix(string $grid, int $size): array
    {
        $lines = array_values(array_filter(array_map('trim', explode("\n", $grid))));

        // Extract only the matrix rows
        $rows = array_slice($lines, 0);

        if (count($rows) !== $size) {
            throw new \Exception("Expected $size rows, received " . count($rows));
        }

        $matrix = [];

        foreach ($rows as $line) {
            // Split by EXACTLY one space
            $values = explode(' ', $line);

            if (count($values) !== $size) {
                throw new \Exception("Row '$line' does not contain $size values.");
            }

            // Convert to integers
            $matrix[] = array_map('intval', $values);
        }

        return $matrix;
    }

    public function populateCells(array $parsedMatrix, int $matrixId): array
    {
        $cells = [];

        foreach ($parsedMatrix as $r => $row) {
            foreach ($row as $c => $height) {
                if ($height < 0 || $height > 1000) {
                    throw new \Exception("Cell height $height at ($r, $c) is out of bounds.");
                }
                $cells[] = [
                    'matrix_id' => $matrixId,
                    'row' => $r,
                    'col' => $c,
                    'height' => $height
                ];
            }
        }

        return $cells;
    }

    public function calculateVisibility(Matrix $matrix): int
    {
        $visible = [];
        $cells = $matrix->cells()->get()->toArray();
        $size = $matrix->size;
        $grid = [];

        foreach ($cells as $cell) {
            $row = (int)$cell['row'];
            $col = (int)$cell['col'];
            $grid[$row][$col] = (int)$cell['height'];
        }
        // left of row
        for ($r = 0; $r < $size; $r++) {
            $max = 0;
            for ($c = 0; $c < $size; $c++) {
                $h = (int)$grid[$r][$c];
                if ($h > $max) {
                    $visible["$r,$c"] = true;
                    $max = $h;
                }
            }
        }

        // right of row
        for ($r = 0; $r < $size; $r++) {
            $max = 0;
            for ($c = $size - 1; $c >= 0; $c--) {
                $h = (int)$grid[$r][$c];
                if ($h > $max) {
                    $visible["$r,$c"] = true;
                    $max = $h;
                }
            }
        }

        // top of column
        for ($c = 0; $c < $size; $c++) {
            $max = 0;
            for ($r = 0; $r < $size; $r++) {
                $h = (int)$grid[$r][$c];
                if ($h > $max) {
                    $visible["$r,$c"] = true;
                    $max = $h;
                }
            }
        }

        // bottom of column
        for ($c = 0; $c < $size; $c++) {
            $max = 0;
            for ($r = $size - 1; $r >= 0; $r--) {
                $h = (int)$grid[$r][$c];
                if ($h > $max) {
                    $visible["$r,$c"] = true;
                    $max = $h;
                }
            }
        }

        return count($visible);
    }
}