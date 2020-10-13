<?php
namespace Tests\BotMan;

use Tests\TestCase;

class SearchBloodProductsStockTest extends TestCase
{
    public function testConversationStarting()
    {
        $this->bot
        ->receives('search')
        ->assertQuestion('What blood product you need?')
        ;
    }
}
