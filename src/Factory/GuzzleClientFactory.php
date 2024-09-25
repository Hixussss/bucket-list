<?php
namespace App\Factory;

use GuzzleHttp\Client;

class GuzzleClientFactory
{
    public static function createClient(): Client
    {
        return new Client();
    }
}