<?php

namespace Jeybin\Networkintl\Providers;

use Jeybin\Networkintl\Facades\NgeniusFacades;
use Jeybin\Networkintl\App\Console\PublishNgeniusWebhooks;
use Jeybin\Networkintl\App\Console\PublishNgeniusProviders;
use Jeybin\Networkintl\App\Console\PublishNgeniusMigrationFiles;
use Illuminate\Support\ServiceProvider;
use Jeybin\Networkintl\App\Services\PaymentMethodService;
use Jeybin\Networkintl\App\Services\TokenizationService;
use Jeybin\Networkintl\App\Services\PaymentLinkService;
use Jeybin\Networkintl\App\Services\RecurringPaymentService;
use Jeybin\Networkintl\App\Services\ReportService;

class NgeniusServiceProvider extends ServiceProvider
{   

    /***
     * Publish Service provider using
     *   php artisan ngenius:install 
     *   or 
     *   php artisan vendor:publish --provider=Jeybin\Networkintl\Providers\NgeniusServiceProvider
     * 
     * Copy migration files 
     *   php artisan vendor:publish 
     * 
     */


    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

        /**
         * Autoloading the helper functions
         */
        require_once __DIR__ . '/../App/Helpers/ResponseHelper.php';

        /**
         * Time stamp made as static 
         * to avoid multiple migration files creation
         * with multiple time stamp
         */            
        $timestamp = '2022_10_20_132000';

        /**
         * Path of the migration file of ngenius_gateway inside the composer package
         */
        $ngenius_gateway_package_path = __DIR__.'/../../database/migrations/create_ngenius_table.php.stub';

        /**
         * Migration file of ngenius_gateway path where it need to be copied
         */
        $ngenius_gateway_project_path = database_path("migrations/ngenius/{$timestamp}_create_ngenius_table.php");

        /**
         * Path of the migration file of ngenius_gateway_webhooks inside the composer package
         */
        $ngenius_gateway_webhook_package_path = __DIR__.'/../../database/migrations/create_ngenius_webhooks_table.php.stub';

        /**
         * Migration file of ngenius_gateway_webhooks path where it need to be copied
         */
        $ngenius_gateway_webhook_project_path = database_path("migrations/ngenius/{$timestamp}_create_ngenius_webhooks_table.php");

        /**
         * Migrations needed to be published,
         * can publish multiple files, add 
         * more into the array
         */
        $publishMigrations = [$ngenius_gateway_package_path =>$ngenius_gateway_project_path,
                              $ngenius_gateway_webhook_package_path =>$ngenius_gateway_webhook_project_path];

        /**
         * Publishes the Migrations files
         * with a tag name ngenius can use any tag 
         * name, use the same name while publishing the 
         * vendor 
         */
        $this->publishes($publishMigrations, 'ngenius-migrations');

        /**
         * Config file merging into the project
         */
        
        $configs = [
            __DIR__.'/../../Config/ngenius-config.php' => config_path('ngenius-config.php') 
        ];

        $this->publishes($configs, 'ngenius-config');



        /**
         * Publishing webhook jobs inside the folder NgeniusWebhooks
         * to App/Jobs/NgeniusWebhooks
         */
        $webhooks = [
            __DIR__.'/../../NgeniusWebhooks' => app_path('Jobs/NgeniusWebhooks') 
        ];
        $this->publishes($webhooks, 'ngenius-webhooks');



        /**
         * Adding the package routes to the project
         * Here we are adding the webhook listener api
         */
        $this->loadRoutesFrom(__DIR__.'/../Routes/ngenius-api.php');


        /**
         * Checking if the app is 
         * running from console
         */
        if ($this->app->runningInConsole()) {

            /**
             * Adding custom commands class to the 
             * service provider
             */
            $this->commands([
                PublishNgeniusProviders::class,
                PublishNgeniusMigrationFiles::class,
                PublishNgeniusWebhooks::class,
            ]);
        }

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register(){

        $this->mergeConfigFrom(
            __DIR__.'/../../Config/ngenius-config.php', 'ngenius-config'
        );

        
        $this->app->bind('ngenius',fn($app)=>new NgeniusFacades($app));

        $this->app->alias('ngenius', NgeniusFacades::class);

        $this->app->singleton(PaymentMethodService::class);
        $this->app->singleton(TokenizationService::class);
        $this->app->singleton(PaymentLinkService::class);
        $this->app->singleton(RecurringPaymentService::class);
        $this->app->singleton(ReportService::class);

    }
}