<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Controllers;

use App\Domain\Dashboard\Actions\ActionPlans\GetOrganizationActionPlans;
use App\Domain\Dashboard\Actions\ActionPlans\GetSystemActionPlans;
use App\Domain\Dashboard\Actions\ActionPlans\GetUserActionPlans;
use App\Domain\Dashboard\Actions\Activities\GetOrganizationActivities;
use App\Domain\Dashboard\Actions\Activities\GetSystemActivities;
use App\Domain\Dashboard\Actions\Assessments\GetOrganizationAssessmentSummary;
use App\Domain\Dashboard\Actions\Assessments\GetSystemAssessmentSummary;
use App\Domain\Dashboard\Actions\Compliance\GetOrganizationComplianceTrend;
use App\Domain\Dashboard\Actions\Compliance\GetSystemComplianceTrend;
use App\Domain\Dashboard\Actions\Statistics\GetOrganizationAdminStatistics;
use App\Domain\Dashboard\Actions\Statistics\GetReviewerStatistics;
use App\Domain\Dashboard\Actions\Statistics\GetSuperAdminStatistics;
use App\Domain\Dashboard\Actions\Statistics\GetUserStatistics;
use App\Domain\Dashboard\Resources\ActionPlanSummaryResource;
use App\Domain\Dashboard\Resources\ActivityResource;
use App\Domain\Dashboard\Resources\AssessmentSummaryResource;
use App\Domain\Dashboard\Resources\ComplianceTrendResource;
use App\Domain\Dashboard\Resources\DashboardStatisticsResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected GetSuperAdminStatistics $getSuperAdminStatistics,
        protected GetOrganizationAdminStatistics $getOrganizationAdminStatistics,
        protected GetReviewerStatistics $getReviewerStatistics,
        protected GetUserStatistics $getUserStatistics,
        protected GetSystemActivities $getSystemActivities,
        protected GetOrganizationActivities $getOrganizationActivities,
        protected GetSystemAssessmentSummary $getSystemAssessmentSummary,
        protected GetOrganizationAssessmentSummary $getOrganizationAssessmentSummary,
        protected GetSystemComplianceTrend $getSystemComplianceTrend,
        protected GetOrganizationComplianceTrend $getOrganizationComplianceTrend,
        protected GetSystemActionPlans $getSystemActionPlans,
        protected GetOrganizationActionPlans $getOrganizationActionPlans,
        protected GetUserActionPlans $getUserActionPlans
    ) {}

    /**
     * Get dashboard statistics based on user role.
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isSuperAdmin()) {
            $data = ['superAdmin' => $this->getSuperAdminStatistics->handle()];
        } elseif ($user->isOrganizationAdmin()) {
            $data = ['orgAdmin' => $this->getOrganizationAdminStatistics->handle($user->organization_id)];
        } elseif ($user->hasPermissionTo('review-assessments')) {
            $data = ['reviewer' => $this->getReviewerStatistics->handle($user)];
        } else {
            $data = ['user' => $this->getUserStatistics->handle($user)];
        }

        return response()->json([
            'success' => true,
            'data' => new DashboardStatisticsResource($data),
        ]);
    }

    /**
     * Get recent activities for dashboard.
     */
    public function activities(Request $request): JsonResponse
    {
        $user = $request->user();
        $limit = $request->integer('limit', 10);

        if ($user->isSuperAdmin()) {
            $activities = $this->getSystemActivities->handle($limit);
        } else {
            $activities = $this->getOrganizationActivities->handle($user->organization_id, $limit);
        }

        return response()->json([
            'success' => true,
            'data' => ActivityResource::collection($activities),
        ]);
    }

    /**
     * Get assessment summary by status.
     */
    public function assessments(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isSuperAdmin()) {
            $data = $this->getSystemAssessmentSummary->handle();
        } else {
            $data = $this->getOrganizationAssessmentSummary->handle($user->organization_id);
        }

        return response()->json([
            'success' => true,
            'data' => new AssessmentSummaryResource($data),
        ]);
    }

    /**
     * Get compliance trend data.
     */
    public function complianceTrend(Request $request): JsonResponse
    {
        $user = $request->user();
        $months = $request->integer('months', 6);

        if ($user->isSuperAdmin()) {
            $data = $this->getSystemComplianceTrend->handle($months);
        } else {
            $data = $this->getOrganizationComplianceTrend->handle($user->organization_id, $months);
        }

        return response()->json([
            'success' => true,
            'data' => new ComplianceTrendResource($data),
        ]);
    }

    /**
     * Get action plans needing attention.
     */
    public function actionPlans(Request $request): JsonResponse
    {
        $user = $request->user();
        $limit = $request->integer('limit', 10);

        if ($user->isSuperAdmin()) {
            $data = $this->getSystemActionPlans->handle($limit);
        } elseif ($user->isOrganizationAdmin() || $user->hasPermissionTo('review-assessments')) {
            $data = $this->getOrganizationActionPlans->handle($user->organization_id, $limit);
        } else {
            $data = $this->getUserActionPlans->handle($user->id, $limit);
        }

        return response()->json([
            'success' => true,
            'data' => new ActionPlanSummaryResource($data),
        ]);
    }
}
