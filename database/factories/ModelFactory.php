<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

use Illuminate\Support\Str;

$factory->define(App\User::class, function (Faker\Generator $faker) {
    return [
        'first_name' => $faker->name,
        'email' => $faker->safeEmail,
        'password' => Str::random(10),
        'remember_token' => Str::random(10),
        'language' => $faker->languageCode,
        'avatar'   => str_replace('http://', 'https://', $faker->imageUrl(64, 64)),
        'permissions' => '{"tickets.own.view":true,"tickets.own.update":true,"tickets.create":true,"tickets.own.delete":true}'
    ];
});

$factory->define(\Common\Auth\SocialProfile::class, function (Faker\Generator $faker) {
    return [
        'service' => 'facebook',
        'token' => $faker->numberBetween(0, 10000),
    ];
});

$factory->define(\Common\Auth\Roles\Role::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
    ];
});

$factory->define(App\Ticket::class, function (Faker\Generator $faker) {
    return [
        'subject' => $faker->sentence,
        'user_id' => $faker->numberBetween(50, 150),
    ];
});

$factory->define(App\Tag::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->words(3, true),
        'type' => 'default',
    ];
});

$factory->define(App\Reply::class, function (Faker\Generator $faker) {
    return [
        'body' => $faker->paragraph,
        'user_id' => $faker->numberBetween(50, 150),
        'ticket_id' => $faker->numberBetween(50, 150),
        'uuid' => Str::random(30),
    ];
});

$factory->define(App\CannedReply::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->sentence,
        'body' => $faker->paragraph,
        'user_id' => $faker->numberBetween(50, 150),
    ];
});

$factory->define(\Common\Files\FileEntry::class, function (Faker\Generator $faker) {
    return [
        'name'      => $faker->word,
        'file_name' => $faker->md5,
        'extension' => $faker->fileExtension,
        'file_size' => $faker->numberBetween(1,50),
        'mime'      => $faker->mimeType,
        'user_id'   => $faker->numberBetween(50, 150),
        'url'       => $faker->url,
    ];
});

$factory->define(App\Category::class, function (Faker\Generator $faker) {
    return [
        'name' => implode($faker->words(5), '-'),
        'description' => $faker->paragraph
    ];
});

$factory->define(App\Article::class, function (Faker\Generator $faker) {
    return [
        'title' => $faker->words(5, true),
        'slug'  => $faker->slug,
        'body'  => $faker->paragraph,
        'description' => $faker->paragraph,
    ];
});

$factory->define(App\ArticleFeedback::class, function (Faker\Generator $faker) {
    return [
        'was_helpful' => 1,
        'comment'  => $faker->paragraph,
    ];
});

$factory->define(App\Condition::class, function (Faker\Generator $faker) {
    return [
        'type' => $faker->word,
        'name' => $faker->word,
    ];
});

$factory->define(App\Action::class, function (Faker\Generator $faker) {
    return [
        'display_name' => $faker->word,
        'name' => $faker->word,
        'input_config' => json_encode(['inputs' => [
            'placeholder' => $faker->word,
            'name' => $faker->word,
            'value_type' => $faker->word,
            'value_options' => $faker->word,
        ]])
    ];
});

$factory->define(App\Operator::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->word,
        'display_name' => $faker->word,
    ];
});

$factory->define(App\Trigger::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->word,
    ];
});

$factory->define(App\Condition::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->word,
        'type' => $faker->word,
    ];
});

$factory->define(App\Action::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->word,
        'display_name' => $faker->word,
    ];
});

$factory->define(\Common\Pages\Page::class, function (Faker\Generator $faker) {
    return [
        'slug' => $faker->slug,
        'body' => $faker->words(5, true),
    ];
});

$factory->define(App\Email::class, function (Faker\Generator $faker) {
    return [
        'address' => $faker->email,
        'user_id' => $faker->numberBetween(1, 500),
    ];
});
