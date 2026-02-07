<?php

declare(strict_types=1);

namespace App\Domain\Report\Controllers;

use App\Domain\Assessment\Models\AssessmentResponse;
use App\Domain\Report\Actions\GetOrganizationsByStandard;
use App\Domain\Report\Actions\GetStandardReport;
use App\Domain\Report\Resources\StandardReportCollection;
use App\Domain\Report\Resources\ResponseHistoryResource;
use App\Domain\Standard\Models\Standard;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        private GetOrganizationsByStandard $getOrganizationsByStandard,
        private GetStandardReport $getStandardReport
    ) {
    }

    /**
     * Get assessment response workflow history.
     */
    public function responseHistory(AssessmentResponse $assessmentResponse): JsonResponse
    {
        $this->authorize('view', $assessmentResponse);

        $logs = $assessmentResponse->workflowLogs()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return ResponseHistoryResource::collection($logs)->response();
    }

    /**
     * Get organizations with their assessment info for a specific standard.
     */
    public function standardOrganizations(Request $request, string $standard): JsonResponse
    {
        $standardModel = Standard::findOrFail($standard);

        $this->authorize('view', $standardModel);

        $filters = [
            'search' => $request->input('search'),
            'sortBy' => $request->input('sort_by'),
            'sortOrder' => $request->input('sort_order'),
            'page' => $request->input('page', 1),
            'perPage' => $request->input('per_page', 15),
        ];

        $result = $this->getOrganizationsByStandard->execute($standardModel, $filters);

        return response()->json([
            'success' => true,
            'data' => $result['data'],
            'meta' => $result['meta'],
        ]);
    }

    /**
     * Get standard report summary with stats.
     */
    public function standardReport(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Standard::class);

        $filters = [
            'search' => $request->input('search'),
            'sortBy' => $request->input('sortBy', $request->input('sort_by', 'createdAt')),
            'sortOrder' => $request->input('sortOrder', $request->input('sort_order', 'desc')),
            'page' => $request->input('page', 1),
            'perPage' => $request->input('perPage', $request->input('per_page', 15)),
        ];

        $standards = $this->getStandardReport->execute($filters);

        return (new StandardReportCollection($standards))->response();
    }
}
