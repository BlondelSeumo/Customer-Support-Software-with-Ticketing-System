<?php namespace App\Providers;

use App\Policies\ReportPolicy;
use App\Policies\TicketFileEntryPolicy;
use Common\Files\FileEntry;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'ReportPolicy'      => ReportPolicy::class,
        FileEntry::class    => TicketFileEntryPolicy::class,
    ];

    /**
     * Register any application authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }
}
