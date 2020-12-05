<?php namespace App\Console\Commands;

use Exception;
use Elasticsearch\Client;
use Illuminate\Console\Command;
use Elasticsearch\ClientBuilder;

class CreateElasticIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elastic:index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create elastic search index.';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $progress = $this->output->createProgressBar(4);
        $progress->advance();

        $client = ClientBuilder::create()->build();

        $this->deleteOldIndex($client);
        $progress->advance();

        $this->createNewIndex($client);
        $progress->advance();

        $this->importSearchData();
        $progress->advance();

        $this->info('Index created successfully.');
    }

    /**
     * Delete old elasticsearch index if it exists.
     *
     * @param $client
     */
    private function deleteOldIndex(Client $client)
    {
        try {
            $client->indices()->delete(['index' => 'general']);
        } catch (Exception $e) {
            //prevent error if index does not exist
        }
    }

    /**
     * Import search data into elasticsearch index.
     */
    private function importSearchData()
    {
        $this->callSilent('scout:import', ['model' => 'App\Article']);
        $this->callSilent('scout:import', ['model' => 'App\Ticket']);
        $this->callSilent('scout:import', ['model' => 'App\User']);
    }

    /**
     * Create a new elasticsearch index.
     *
     * @param $client
     */
    private function createNewIndex(Client $client)
    {
        $client->indices()->create([
            'index' => 'general'
        ]);
    }
}
