<?php

namespace App\Tests;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AppUrlTest extends WebTestCase{

    public function testUrl()
    {
        $client = self::createClient();
        $urls = $this->urlProvider();

        foreach($urls as $url){
            $client->request('GET', $url);
            $this->assertTrue($client->getResponse()->isSuccessful());
        }

    }

    private function urlProvider()
    {
        return [
            'Home' => '/',
            'Login' => '/login'
            
        ];
    }
}