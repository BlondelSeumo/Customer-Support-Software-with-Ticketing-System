<?php

namespace App\Console\Commands;

use App\Article;
use App\Ticket;
use App\User;
use Common\Settings\DotEnvEditor;
use Artisan;
use Common\Settings\Setting;
use DB;
use DemoHelpCenterSeeder;
use DemoTicketsSeeder;
use DemoUserSeeder;
use Illuminate\Console\Command;
use Schema;

class RefreshDemoSite extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh demo site with sample data.';

    /**
     * @var Setting
     */
    private $setting;

    /**
     * @var DotEnvEditor
     */
    private $dotEnvEditor;

    /**
     * Create a new command instance.
     *
     * @param Setting $setting
     * @param DotEnvEditor $dotEnvEditor
     */
    public function __construct(Setting $setting, DotEnvEditor $dotEnvEditor)
    {
        parent::__construct();

        $this->setting = $setting;
        $this->dotEnvEditor = $dotEnvEditor;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ( ! config('common.site.demo')) {
            $this->warn('This is not a demo site! Aborting...');
            return;
        }

        $progress = $this->output->createProgressBar(9);
        $progress->advance();

        $tableNames = Schema::getConnection()->getDoctrineSchemaManager()->listTableNames();
       
        foreach ($tableNames as $name) {
            if ($name === 'migrations') continue;
            DB::table($name)->truncate();
        }
        $progress->advance();

        Artisan::call('db:seed', ['--force' => true]);
        Artisan::call('common:seed');
        $progress->advance();

        Artisan::call('db:seed', ['--force' => true, '--class' => DemoUserSeeder::class]);
        $progress->advance();

        Artisan::call('db:seed', ['--force' => true, '--class' => DemoHelpCenterSeeder::class]);
        $progress->advance();

        Artisan::call('db:seed', ['--force' => true, '--class' => DemoTicketsSeeder::class]);
        $progress->advance();

        Artisan::call('scout:import', ['model' => Article::class]);
        Artisan::call('scout:import', ['model' => User::class]);
        Artisan::call('scout:import', ['model' => Ticket::class]);
        $progress->advance();

        //menus
        $this->setting->where('name', 'menus')->update(['value' => json_encode([
            ['name' => 'Agent Mailbox', 'position' => 'agent-mailbox', 'items' => [
                ['type' => 'route', 'order' => 1, 'label' => 'Tickets', 'action' => 'mailbox'],
                ['type' => 'route', 'order' => 2, 'label' => 'Help Center', 'action' => 'help-center/manage'],
                ['type' => 'route', 'order' => 2, 'label' => 'Admin Area', 'action' => 'admin'],
            ]],
            ['name' => 'Header Menu', 'position' => 'header', 'items' => [
                ['type' => 'route', 'condition' => 'auth', 'label' => 'My Tickets', 'action' => '/help-center/tickets'],
            ]],
        ])]);

        //other settings
        $this->setting->where('name', 'i18n.enable')->update(['value' => false]);
        $this->setting->where('name', 'cookie_notice.enable')->update(['value' => false]);
        $this->setting->where('name', 'uploads.chunk')->update(['value' => false]);
        $this->setting->where('name', 'uploads.max-size')->update(['value' => 4194304]); //4MB
        $this->setting->where('name', 'mail.use_default_templates')->update(['value' => true]);
        $this->setting->where('name', 'realtime.pusher_key')->update(['value' => 'e6a31537e073f8bdce05']);
        $this->setting->where('name', 'realtime.enable')->update(['value' => true]);
        $this->setting->where('name', 'logging.sentry_public')->update(['value' => 'https://cc3692b6b6224c2ca965dea66017d565@sentry.io/203366']);
        $this->dotEnvEditor->write(['SCOUT_DRIVER' => 'mysql']);
        $progress->advance();

        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');
        $progress->advance();
    }
}
