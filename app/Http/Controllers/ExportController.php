<?php

namespace App\Http\Controllers;

use App\Models\Opportunity;
use App\Services\ExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function __construct(private readonly ExportService $export) {}

    public function csv(Request $request): StreamedResponse
    {
        $this->authorize('export', Opportunity::class);

        $result = $this->export->csv($this->filters($request), $request->user());

        return response()->streamDownload(
            fn () => print ($result['content']),
            $result['filename'],
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }

    public function xlsx(Request $request)
    {
        $this->authorize('export', Opportunity::class);

        $result = $this->export->xlsx($this->filters($request), $request->user());

        return response()->download($result['path'], $result['filename'], [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /** I filtri dell'export sono esattamente quelli mostrati a schermo. */
    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'opportunity_id' => ['nullable', 'integer', 'exists:opportunities,id'],
            'status' => ['nullable', 'array'],
            'status.*' => ['string'],
            'delivery_date' => ['nullable', 'date'],
            'delivery_from' => ['nullable', 'date'],
            'delivery_to' => ['nullable', 'date'],
            'closes_from' => ['nullable', 'date'],
            'closes_to' => ['nullable', 'date'],
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
        ]);

        return array_filter($validated, fn ($v) => $v !== null && $v !== '');
    }
}
