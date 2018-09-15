<?php
declare(strict_types=1);

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as WebTestCaseFramework;
use Symfony\Component\BrowserKit\Client;

/**
 * Class WebTestCase
 * @package App\Tests
 */
class WebTestCase extends WebTestCaseFramework
{

    private const SERVER_PARAMETER = [
        'HTTP_HOST' => '127.0.0.6',
        'HTTP_USER_AGENT' => 'TESTS USER',
    ];

    /**
     * @return Client
     */
    public function createClientGuess(): Client
    {
        $client = self::createClient();
        $client->setServerParameters(self::SERVER_PARAMETER);

        return $client;
    }

    /**
     * @return Client
     */
    public function createClientUser(): Client
    {
        $client = self::createClient();
        $client->setServerParameters(self::SERVER_PARAMETER);

        $crawler = $client->request('GET', '/');

        $buttonCrawlerNode = $crawler->selectButton('sign_in_form[signIn]');

        $formData = [
            'sign_in_form[username]' => 'kontakt@lukaszstaniszewski.pl',
            'sign_in_form[password]' => 'admin1234'
        ];

        $form = $buttonCrawlerNode->form($formData);

        $client->submit($form);

        return $client;
    }
}
