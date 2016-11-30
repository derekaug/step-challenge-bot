<?php

namespace App\Providers;

use App\StepLog;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Frlnc\Slack\Core\Commander;
use Frlnc\Slack\Http\CurlInteractor;
use Frlnc\Slack\Http\SlackResponseFactory;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        StepLog::saved(function (StepLog $step_log) {
            $user = $step_log->slackUser;
            $user->steps = $user->stepLogs()->sum('steps');
            $user->save();
        });
    }

    /**
     * Register any application services.
     *php artisan ide-helper:generate
     * @return void
     */
    public function register()
    {
        if ($this->app->environment() === 'local') {
            $this->app->register(IdeHelperServiceProvider::class);
        }

        $this->app->singleton('slack_api', function () {
            $interactor = new CurlInteractor();
            $interactor->setResponseFactory(new SlackResponseFactory());
            return new Commander(env('SLACK_TOKEN'), $interactor);
        });
    }
}
