<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Traits\AppliesQueryOptions;
use App\Http\Controllers\Controller;
use App\Http\Resources\PulseInstrumentResource;
use App\Models\PulseInstrument;
use App\Services\SimpleXlsxReader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PulseInstrumentController extends Controller
{
    use AppliesQueryOptions;

    /**
     * Display a listing of pulse instruments.
     */
    public function index(Request $request): JsonResponse
    {
        $query = PulseInstrument::query();

        $paginator = $this->applyQueryOptions(
            $query,
            $request,
            ['statement'],
            ['learning_material_id', 'dimension']
        );

        return $this->respondWithPagination($paginator, PulseInstrumentResource::class, 'Pulse instruments retrieved successfully');
    }

    /**
     * Store a newly created pulse instrument.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'learning_material_id' => 'required|exists:learning_materials,id',
            'dimension' => ['required', Rule::in(['P', 'U', 'L', 'SE'])],
            'statement' => 'required|string',
        ]);

        $instrument = PulseInstrument::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Pulse instrument created successfully',
            'data' => new PulseInstrumentResource($instrument),
        ], 201);
    }

    /**
     * Display the specified pulse instrument.
     */
    public function show(PulseInstrument $pulseInstrument): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Pulse instrument retrieved successfully',
            'data' => new PulseInstrumentResource($pulseInstrument),
        ]);
    }

    /**
     * Update the specified pulse instrument.
     */
    public function update(Request $request, PulseInstrument $pulseInstrument): JsonResponse
    {
        $validated = $request->validate([
            'learning_material_id' => 'nullable|exists:learning_materials,id',
            'dimension' => ['nullable', Rule::in(['P', 'U', 'L', 'SE'])],
            'statement' => 'nullable|string',
        ]);

        $pulseInstrument->update(array_filter($validated));

        return response()->json([
            'success' => true,
            'message' => 'Pulse instrument updated successfully',
            'data' => new PulseInstrumentResource($pulseInstrument),
        ]);
    }

    /**
     * Remove the specified pulse instrument.
     */
    public function destroy(PulseInstrument $pulseInstrument): JsonResponse
    {
        $instrumentId = $pulseInstrument->id;
        $pulseInstrument->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pulse instrument deleted successfully',
            'data' => [
                'id' => $instrumentId,
            ],
        ]);
    }

    /**
     * Import pulse instruments from Excel.
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'learning_material_id' => 'required|exists:learning_materials,id',
            'file' => 'required|file|mimes:xlsx|max:51200',
        ]);

        try {
            $rows = SimpleXlsxReader::read($request->file('file')->getRealPath());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca berkas Excel: '.$e->getMessage(),
            ], 422);
        }

        $instrumentsData = [];
        foreach ($rows as $row) {
            $dimension = strtoupper($row['dimension'] ?? '');
            $statement = $row['statement'] ?? '';

            if (empty($dimension) && empty($statement)) {
                continue;
            }

            $rowIndex = $row['__row_index__'] ?? '?';

            if (! in_array($dimension, ['P', 'U', 'L', 'SE'])) {
                return response()->json([
                    'success' => false,
                    'message' => "Dimensi pada baris {$rowIndex} harus berupa P, U, L, atau SE.",
                ], 422);
            }

            if (empty($statement)) {
                return response()->json([
                    'success' => false,
                    'message' => "Pernyataan pada baris {$rowIndex} tidak boleh kosong.",
                ], 422);
            }

            $instrumentsData[] = [
                'dimension' => $dimension,
                'statement' => $statement,
            ];
        }

        if (empty($instrumentsData)) {
            return response()->json([
                'success' => false,
                'message' => 'Berkas Excel tidak memiliki data instrumen yang valid.',
            ], 422);
        }

        $materialId = $request->learning_material_id;

        DB::transaction(function () use ($materialId, $instrumentsData) {
            PulseInstrument::where('learning_material_id', $materialId)->delete();

            foreach ($instrumentsData as $data) {
                PulseInstrument::create([
                    'learning_material_id' => $materialId,
                    'dimension' => $data['dimension'],
                    'statement' => $data['statement'],
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => count($instrumentsData).' instrumen PULSE berhasil diimpor.',
        ]);
    }
}
