<?php

namespace App\Services\Admin;

use App\Article;
use App\Category;
use App\Ticket;
use App\User;
use Common\Admin\Analytics\Actions\GetAnalyticsHeaderDataAction;

class GetAnalyticsHeaderData implements GetAnalyticsHeaderDataAction
{
    public function execute($channel = null)
    {
        return [
            [
                'icon' => 'person',
                'name' => 'Total Users',
                'type' => 'number',
                'value' => app(User::class)->count(),
            ],
            [
                'icon' => 'inbox',
                'name' => 'Total Tickets',
                'type' => 'number',
                'value' => app(Ticket::class)->count(),
            ],
            [
                'icon' => 'description',
                'name' => 'Total Articles',
                'type' => 'number',
                'value' => app(Article::class)->count(),
            ],
            [
                'icon' => 'local-offer',
                'name' => 'Total Categories',
                'type' => 'number',
                'value' => app(Category::class)->count(),
            ],
        ];
    }
}
