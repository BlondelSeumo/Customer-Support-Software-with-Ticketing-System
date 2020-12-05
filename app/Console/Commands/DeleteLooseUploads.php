<?php namespace App\Console\Commands;

use DB;
use App\FileEntry;
use Illuminate\Console\Command;

class DeleteLooseUploads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uploads:delete_loose';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete uploads that are not attached to anything.';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * @param FileEntry $entry
     * @throws \Exception
     */
    public function handle(FileEntry $entry)
    {
        //get ids of entries that are not attached to anything
        $ids = $entry->where('public', false)
            ->doesntHave('replies')
            ->doesntHave('canned_replies')
            ->pluck('id');

        //detach tags
        DB::table('taggables')
            ->whereIn('taggable_id', $ids)
            ->where('taggable_type', FileEntry::class)
            ->delete();

        //delete entries
        $entry->whereIn('id', $ids)->delete();

        $this->info('Loose uploads deleted.');
    }
}
