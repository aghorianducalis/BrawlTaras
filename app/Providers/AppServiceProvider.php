<?php

declare(strict_types=1);

namespace App\Providers;

use App\API\Client\APIClient;
use App\API\Contracts\APIClientInterface;
use App\Services\Parser\Contracts\ParserInterface;
use App\Services\Parser\Parser;
use App\Services\Repositories\AccessoryRepository;
use App\Services\Repositories\BrawlerRepository;
use App\Services\Repositories\Contracts\AccessoryRepositoryInterface;
use App\Services\Repositories\Contracts\BrawlerRepositoryInterface;
use App\Services\Repositories\Contracts\StarPowerRepositoryInterface;
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
        // Register API Client
        $this->app->singleton(abstract: APIClientInterface::class, concrete: function ($app) {
            return new APIClient(
                httpClient: new HttpClient(),
                apiBaseURI: config('brawlstars_api.api_base_uri'),
                apiKey: config('brawlstars_api.api_key')
            );
        });

        // Register repositories
        $this->app->singleton(abstract: AccessoryRepositoryInterface::class, concrete: function ($app) {
            return new AccessoryRepository();
        });
        $this->app->singleton(abstract: StarPowerRepositoryInterface::class, concrete: function ($app) {
            return new StarPowerRepository();
        });
        $this->app->singleton(abstract: BrawlerRepositoryInterface::class, concrete: function ($app) {
            return new BrawlerRepository(
                accessoryRepository: $app->make(abstract: AccessoryRepositoryInterface::class),
                starPowerRepository: $app->make(abstract: StarPowerRepositoryInterface::class),
            );
        });

        // Register Parser
        $this->app->singleton(abstract: ParserInterface::class, concrete: function ($app) {
            return new Parser(
                apiClient: $app->make(APIClientInterface::class),
                brawlerRepository: $app->make(BrawlerRepositoryInterface::class)
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
