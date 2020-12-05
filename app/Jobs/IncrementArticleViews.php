<?php

namespace App\Jobs;

use App\Article;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Session\Store;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class IncrementArticleViews implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    private $articleId;

    /**
     * Create a new command instance.
     *
     * @param integer $articleId
     */
    public function __construct($articleId)
    {
        $this->articleId = $articleId;
    }

    /**
     * Execute the console command.
     *
     * @param Store $session
     * @param Article $article
     * @return mixed
     */
    public function handle(Store $session, Article $article)
    {
        if ( ! $this->shouldIncrement($session)) return;

        $session->put("articleViews.$this->articleId", Carbon::now()->timestamp);

        $article->where('id', $this->articleId)->increment('views');
    }

    /**
     * Check if article views should be incremented.
     *
     * @param Store $session
     * @return boolean
     */
    private function shouldIncrement(Store $session)
    {
        $views = $session->get('articleViews');

        //user has not viewed this article yet
        if ( ! $views || ! isset($views[$this->articleId])) return true;

        //see if user last viewed this article over 10 hours ago
        $time = Carbon::createFromTimestamp($views[$this->articleId]);

        return Carbon::now()->diffInHours($time) > 10;
    }
}
