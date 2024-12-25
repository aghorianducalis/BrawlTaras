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
use App\Services\Repositories\Contracts\Event\EventMapRepositoryInterface;
use App\Services\Repositories\Contracts\Event\EventModeRepositoryInterface;
use App\Services\Repositories\Contracts\Event\EventModifierRepositoryInterface;
use App\Services\Repositories\Contracts\Event\EventRepositoryInterface;
use App\Services\Repositories\Contracts\Event\EventRotationRepositoryInterface;
use App\Services\Repositories\Contracts\Event\EventRotationSlotRepositoryInterface;
use App\Services\Repositories\Contracts\StarPowerRepositoryInterface;
use App\Services\Repositories\Event\EventMapRepository;
use App\Services\Repositories\Event\EventModeRepository;
use App\Services\Repositories\Event\EventModifierRepository;
use App\Services\Repositories\Event\EventRepository;
use App\Services\Repositories\Event\EventRotationRepository;
use App\Services\Repositories\Event\EventRotationSlotRepository;
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

        // events rotation
        $this->app->singleton(abstract: EventMapRepositoryInterface::class, concrete: function ($app) {
            return new EventMapRepository();
        });
        $this->app->singleton(abstract: EventModeRepositoryInterface::class, concrete: function ($app) {
            return new EventModeRepository();
        });
        $this->app->singleton(abstract: EventModifierRepositoryInterface::class, concrete: function ($app) {
            return new EventModifierRepository();
        });
        $this->app->singleton(abstract: EventRepositoryInterface::class, concrete: function ($app) {
            return new EventRepository(
                mapRepository: $app->make(abstract: EventMapRepositoryInterface::class),
                modeRepository: $app->make(abstract: EventModeRepositoryInterface::class),
                modifierRepository: $app->make(abstract: EventModifierRepositoryInterface::class),
            );
        });
        $this->app->singleton(abstract: EventRotationSlotRepositoryInterface::class, concrete: function ($app) {
            return new EventRotationSlotRepository();
        });
        $this->app->singleton(abstract: EventRotationRepositoryInterface::class, concrete: function ($app) {
            return new EventRotationRepository(
                eventRepository: $app->make(abstract: EventRepositoryInterface::class),
                slotRepository: $app->make(abstract: EventRotationSlotRepositoryInterface::class),
            );
        });

        // Register Parser
        $this->app->singleton(abstract: ParserInterface::class, concrete: function ($app) {
            return new Parser(
                apiClient: $app->make(APIClientInterface::class),
                brawlerRepository: $app->make(BrawlerRepositoryInterface::class),
                eventRotationRepository: $app->make(EventRotationRepositoryInterface::class),
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
