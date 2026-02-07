<?php

namespace App\Providers;

use App\Domain\Organization\Models\Organization;
use App\Domain\Organization\Policies\OrganizationPolicy;
use App\Domain\Standard\Models\Standard;
use App\Domain\Standard\Models\StandardSection;
use App\Domain\Standard\Models\StandardRequirement;
use App\Domain\Standard\Policies\StandardPolicy;
use App\Domain\Standard\Policies\StandardSectionPolicy;
use App\Domain\Standard\Policies\StandardRequirementPolicy;
use App\Domain\Assessment\Models\Assessment;
use App\Domain\Assessment\Models\AssessmentResponse;
use App\Domain\Assessment\Policies\AssessmentPolicy;
use App\Domain\Assessment\Policies\AssessmentResponsePolicy;
use App\Domain\Role\Policies\RolePolicy;
use App\Domain\User\Models\User as DomainUser;
use App\Domain\User\Policies\UserPolicy;
use App\Domain\Attachment\Models\Attachment;
use App\Domain\Attachment\Policies\AttachmentPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Spatie\Permission\Models\Role;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Organization::class => OrganizationPolicy::class,
        Role::class => RolePolicy::class,
        DomainUser::class => UserPolicy::class,
        Standard::class => StandardPolicy::class,
        StandardSection::class => StandardSectionPolicy::class,
        StandardRequirement::class => StandardRequirementPolicy::class,
        Assessment::class => AssessmentPolicy::class,
        AssessmentResponse::class => AssessmentResponsePolicy::class,
        Attachment::class => AttachmentPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
