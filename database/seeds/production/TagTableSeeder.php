<?php

use App\Tag;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TagTableSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        // create default status tags
        $tags = [
            ['name' => 'open', 'type' => 'status', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'closed', 'type' => 'status', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'pending', 'type' => 'status', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'spam', 'type' => 'status', 'created_at' => $now, 'updated_at' => $now],
        ];
        foreach ($tags as $tag) {
            if (is_null(Tag::where('name', $tag['name'])->first())) {
                Tag::create($tag);
            }
        }

        // Create one ticket category if none exist
        if ( ! Tag::where('type', 'category')->count()) {
            Tag::create([
                'name' => 'general',
                'type' => 'category',
                'created_at' => $now,
                'updated_at' => $now
            ]);
        }
    }
}
