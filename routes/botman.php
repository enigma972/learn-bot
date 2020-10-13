<?php
use App\Http\Controllers\BotManController;

$botman = resolve('botman');

$botman->hears('Hi|Hi Nathalie', function ($bot) {
    $bot->reply('Hello!');
});
$botman->hears('What is your name', function ($bot) {
    $bot->reply('My name is Nathalie and i am a bot!');
});

$botman->fallback(function ($bot) {
   $bot->reply('Sorry i can\'t understand!') ;
});

$botman->hears('Start conversation', BotManController::class.'@startConversation');
$botman->hears('Get started', BotManController::class.'@getStarted');
$botman->hears('Search', BotManController::class.'@search');
