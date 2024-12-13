<?php

declare(strict_types=1);

namespace App\Providers;

use App\API\Client\APIClient;
use App\Services\ParserService;
use App\Services\Repositories\AccessoryRepository;
use App\Services\Repositories\BrawlerRepository;
use App\Services\Repositories\StarPowerRepository;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // repositories
        $this->app->singleton(abstract: AccessoryRepository::class, concrete: function ($app) {
            return new AccessoryRepository();
        });
        $this->app->singleton(abstract: StarPowerRepository::class, concrete: function ($app) {
            return new StarPowerRepository();
        });
        $this->app->singleton(abstract: BrawlerRepository::class, concrete: function ($app) {
            return new BrawlerRepository(
                accessoryRepository: $app->make(abstract: AccessoryRepository::class),
                starPowerRepository: $app->make(abstract: StarPowerRepository::class),
            );
        });

        // API
        $this->app->singleton(abstract: APIClient::class, concrete: function ($app) {
            return new APIClient(
                httpClient: new HttpClient(),
                apiBaseURI: config('brawlstars_api.api_base_uri'),
                apiKey: config('brawlstars_api.api_key')
            );
        });
        $this->app->singleton(abstract: ParserService::class, concrete: function ($app) {
            return new ParserService(
                $app->make(APIClient::class),
                $app->make(BrawlerRepository::class), // todo remove? what about other repos
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
