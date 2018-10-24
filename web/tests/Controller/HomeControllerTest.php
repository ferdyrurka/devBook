<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Tests\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class HomeControllerTest
 * @package App\Tests\Controller
 */
class HomeControllerTest extends WebTestCase
{
    private $guess;
    private $user;

    public function setUp(): void
    {
        $this->guess = $this->createClientGuess();
        $this->user = $this->createClientUser();
        parent::setUp();
    }

    public function testPermission(): void
    {
        $this->user->request('GET', '/');
        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->user->getResponse()->getStatusCode());
    }

    public function testIndexAction(): void
    {
        $this->guess->request('GET', '/');
        $this->assertEquals(Response::HTTP_OK, $this->guess->getResponse()->getStatusCode());
    }

    public function testRegistration(): void
    {
        $crawler = $this->guess->request('GET', '/');
        $this->assertEquals(Response::HTTP_OK, $this->guess->getResponse()->getStatusCode());

        $buttonCrawlerNode = $crawler->selectButton('sign_up_form[RegisterAccount]');

        $formData = [
            'sign_up_form[username]' => 'admin@lukaszstaniszewski.pl',
            'sign_up_form[firstName]' => 'FirstNameUser',
            'sign_up_form[surname]' => 'SurnameUser',
            'sign_up_form[plainPassword][first]' => 'admin1234',
            'sign_up_form[plainPassword][second]' => 'admin1234',
            'sign_up_form[dateBirth][year]' => 1999,
            'sign_up_form[dateBirth][month]' => 2,
            'sign_up_form[dateBirth][day]' => 17
        ];

        $form = $buttonCrawlerNode->form($formData);

        $this->guess->submit($form);

        $em = self::bootKernel();
        $em = $em->getContainer()->get('doctrine')->getManager();



        $this->assertNotNull(
            $user = $em->getRepository(User::class)->findOneBy(['username' => 'admin@lukaszstaniszewski.pl'])
        );

        $em->remove($user);
        $em->flush();
    }
}
