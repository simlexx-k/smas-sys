<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Activity;
use App\Observers\ActivityObserver;
use App\Services\StorageUsageService;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(StorageUsageService::class, function ($app) {
            return new StorageUsageService();
        });

        Blade::directive('currency', function ($expression) {
            return "<?php echo config('app.currency_symbol') . number_format($expression, 2); ?>";
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Activity::observe(ActivityObserver::class);
    }
}
