<?php

namespace App\Providers;

use App\Models\SantriProfile;
use App\Models\User;
use App\Services\HafalanProgress;
use App\Services\SetoranProgress;
use App\Support\OperationalAccess;
use App\Support\PortalAccess;
use App\Support\Role;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(OperationalAccess::class);
        $this->app->singleton(PortalAccess::class);
        $this->app->singleton(HafalanProgress::class);
        $this->app->singleton(SetoranProgress::class);
    }

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Paginator::useTailwind();

        Gate::define('manage-master', fn (User $user) => $user->hasRole(Role::Ketua));
        Gate::define('manage-holidays', fn (User $user) => $user->hasAnyRole([Role::Ketua, Role::KetuaPengajar]));
        Gate::define('operate-daily', fn (User $user) => app(OperationalAccess::class)->canOperateDaily($user));
        Gate::define('monitor-santri', fn (User $user, SantriProfile $santri) => app(PortalAccess::class)->canMonitor($user, $santri));
    }
}
