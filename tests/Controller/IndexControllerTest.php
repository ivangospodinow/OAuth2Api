<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class IndexControllerTest extends WebTestCase
{
    public function testIndex()
    {
        // This calls KernelTestCase::bootKernel(), and creates a
        // "client" that is acting as the browser
        $client = static::createClient();

        // Request a specific page
        $client->request('GET', '/');
        $this->assertResponseIsSuccessful();
    }

    public function testUnauthorized()
    {
        // This calls KernelTestCase::bootKernel(), and creates a
        // "client" that is acting as the browser
        $client = static::createClient();

        // Request a specific page
        $client->request('GET', '/api/unauthorized');
        $this->assertResponseIsSuccessful();
    }
}
