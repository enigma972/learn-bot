<?php

namespace App\Conversations;

use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Conversations\Conversation;

class SearchBloodProductsStockConversation extends Conversation
{
    protected $bloodProduct;

    protected $bloodProductName;

    protected $bloodGroup;

    protected $volume;

    protected $quantity;


    public function getQuery()
    {
        return "This is your query: \"blood product: {$this->bloodProductName}, blood group: {$this->bloodGroup}, volume and quantity: {$this->volume}ml x {$this->quantity}\"";
    }

    public function askBloodProduct()
    {
        $question = Question::create('What blood product you need?')
        ->fallback('Unable to ask question')
        ->callbackId('ask_blood_product')
        ->addButtons([
            Button::create('Blood red cells')->value(4),
            Button::create('Other')->value(0),
        ]);

        return $this->ask($question, function (Answer $answer) {
            $this->bloodProduct = $answer->getValue();


            if ($answer->getValue() == 4) {
                $this->bloodProductName = 'Blood red cells';
                $this->askBloodGroup();
            }else {
                $this->say('Others blood products will be supported very soon!');
                $this->askBloodProduct();
            }
            
        });
    }

    public function askBloodGroup()
    {
        //I can tell you blood stock availibity
        $question = Question::create('What blood group you need (e.g: b+) ?')
            ->fallback('Unable to ask question')
            ->callbackId('ask_blood_group')
            ->addButtons([
                Button::create('A+')->value('a+'),
                Button::create('A-')->value('a-'),
                Button::create('B+')->value('b+'),
                Button::create('B-')->value('b+'),
                Button::create('AB+')->value('ab+'),
                Button::create('AB-')->value('ab-'),
                Button::create('O+')->value('o+'),
                Button::create('O-')->value('o-'),
            ]);

        return $this->ask($question, function (Answer $answer) {
            $this->bloodGroup = $answer->getText();

            $this->askVolume();
        });
    }

    public function askVolume()
    {
        return $this->ask('What blood bag volume in ml (e.g: 270)', function (Answer $answer) {
            $this->volume = $answer->getText();

            $this->askQuantity();
        });
    }

    public function askQuantity()
    {
        return $this->ask('What blood bag quantity (e.g: 2)', function (Answer $answer) {
            $this->quantity = $answer->getText();

            $this->askConfirmation();
        });
    }

    public function askConfirmation()
    {
        $question = Question::create($this->getQuery())
            ->addButtons([
                Button::create('Yes')->value(1),
                Button::create('No')->value(0),
            ]);

        return $this->ask($question, function (Answer $answer) {
            if ($answer->getValue() == 1) {
                $this->say('...');

                try {
                    $response = $client = (new \GuzzleHttp\Client(['base_uri' => 'http://bloodbank-manager.dev']))
                    ->request('GET', '/search', [
                        'headers'   =>  ['X-Requested-With' => 'XMLHttpRequest'],
                        'query'     =>  [
                            'product_type_id'   =>  $this->bloodProduct,
                            'product_group'     =>  $this->bloodGroup,
                            'product_volume'    =>  $this->volume,
                            'quantity'          =>  $this->quantity,
                        ]
                    ]);

                    $results = (string) $response->getBody();
                    $this->say($results);
                } catch (\Exception $e) {
                    $this->say($e->getMessage());
                }
            } else {
                $this->askBloodProduct();
            }
        });
    }

    /**
     * Start the conversation.
     *
     * @return mixed
     */
    public function run()
    {
        $this->askBloodProduct();
    }
}
