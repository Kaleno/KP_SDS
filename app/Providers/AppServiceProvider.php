<?php

namespace App\Providers;

use App\Models\Halaqah;
use App\Models\SantriProfile;
use App\Models\User;
use App\Services\HafalanProgress;
use App\Support\OperationalAccess;
use App\Support\PortalAccess;
use App\Support\Role;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(OperationalAccess::class);
        $this->app->singleton(PortalAccess::class);
        $this->app->singleton(HafalanProgress::class);
    }

    public function boot(): void
    {
        Paginator::useTailwind();

        Gate::define('manage-master', fn (User $user) => $user->hasRole(Role::Ketua));
        Gate::define('operate-daily', fn (User $user) => app(OperationalAccess::class)->canOperateDaily($user));
        Gate::define('operate-halaqah', fn (User $user, Halaqah $halaqah) => app(OperationalAccess::class)->canOperateHalaqah($user, $halaqah));
        Gate::define('monitor-santri', fn (User $user, SantriProfile $santri) => app(PortalAccess::class)->canMonitor($user, $santri));
    }
}
