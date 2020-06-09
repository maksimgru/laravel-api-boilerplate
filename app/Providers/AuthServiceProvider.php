<?php

namespace App\Providers;

use App\Models\User;
use App\Models\VisitPlaceCategory;
use App\Models\VisitPlaceComment;
use App\Models\VisitPlaceRating;
use App\Policies\UserPolicy;
use App\Policies\VisitPlaceCategoryPolicy;
use App\Policies\VisitPlaceCommentPolicy;
use App\Policies\VisitPlaceRatingPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        User::class               => UserPolicy::class,
        VisitPlaceCategory::class => VisitPlaceCategoryPolicy::class,
        VisitPlaceComment::class  => VisitPlaceCommentPolicy::class,
        VisitPlaceRating::class   => VisitPlaceRatingPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
