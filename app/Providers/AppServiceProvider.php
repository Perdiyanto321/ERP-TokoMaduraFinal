<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());
        Model::preventSilentlyDiscardingAttributes(! $this->app->isProduction());

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Gate::define('kelola-pengguna', fn (User $user) => $user->hasRole('admin'));
        Gate::define('kelola-master', fn (User $user) => $user->hasRole(['admin', 'gudang']));
        Gate::define('akses-kasir', fn (User $user) => $user->hasRole(['admin', 'kasir']));
        Gate::define('kelola-pembelian', fn (User $user) => $user->hasRole(['admin', 'gudang']));
        Gate::define('setujui-po', fn (User $user) => $user->hasRole('admin'));
        Gate::define('kelola-keuangan', fn (User $user) => $user->hasRole(['admin', 'keuangan']));
        Gate::define('lihat-laporan', fn (User $user) => $user->hasRole(['admin', 'keuangan']));
    }
}
