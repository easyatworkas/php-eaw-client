<?php

use Eaw\Client;
use GuzzleHttp\Client as Guzzle;

Client::setInstance(new class extends Client {
    public function __construct()
    {
        $this->guzzle = (new \Prophecy\Prophet())->prophesize(Guzzle::class)->reveal();
    }
});
