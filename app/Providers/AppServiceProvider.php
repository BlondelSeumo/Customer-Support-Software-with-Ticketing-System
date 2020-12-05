<?php namespace App\Providers;

use App\Scout\ElasticSearchEngine;
use App\Scout\MysqlSearchEngine;
use App\Services\Admin\GetAnalyticsHeaderData;
use App\Services\Envato\EnvatoApiClient;
use App\Services\Envato\EnvatoSupportPeriodValidator;
use App\Services\SocialiteProviders\EnvatoProvider;
use App\Services\UrlGenerator;
use App\Services\UserRepository;
use Common\Admin\Analytics\Actions\GetAnalyticsHeaderDataAction;
use Common\Core\Contracts\AppUrlGenerator;
use Common\Settings\Settings;
use Illuminate\Support\ServiceProvider;
use Laravel\Scout\EngineManager;
use Laravel\Socialite\Contracts\Factory;
use Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerScoutEngines();
        $this->registerSocialiteEnvatoDriver();
        $this->registerEnvatoFormValidations();

        $this->app->bind(\Common\Auth\UserRepository::class, UserRepository::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            AppUrlGenerator::class,
            UrlGenerator::class
        );

        $this->app->bind(
            GetAnalyticsHeaderDataAction::class,
            GetAnalyticsHeaderData::class
        );
    }

    /**
     * Register custom laravel scout search engines.
     */
    private function registerScoutEngines()
    {
        $manager = $this->app->make(EngineManager::class);

        if (config('scout.driver') === 'mysql' || config('app.env') === 'testing') {
            $manager->extend('mysql', function () {
                return new MysqlSearchEngine;
            });
        }

        if (config('scout.driver') === 'elastic') {
            $manager->extend('elastic', function () {
                return new ElasticSearchEngine();
            });
        }
    }

    private function registerEnvatoFormValidations()
    {
        if ($this->app->make(Settings::class)->get('envato.enable')) {
            $envato = $this->app->make(EnvatoApiClient::class);
            Validator::extend('purchase_code_valid', function ($attribute, $value) use($envato) {
                return $envato->purchaseCodeIsValid($value);
            });
        }

        Validator::extend('envatoSupportActive', EnvatoSupportPeriodValidator::class);
    }

    /**
     * Register custom laravel socialite envato driver.
     */
    private function registerSocialiteEnvatoDriver()
    {
        if ($this->app->make(Settings::class)->get('envato.enable')) {
            $socialite = $this->app->make(Factory::class);
            $socialite->extend('envato', function ($app) use ($socialite) {
                $config = $app['config']['services.envato'];
                return $socialite->buildProvider(EnvatoProvider::class, $config);
            });
        }
    }
}
