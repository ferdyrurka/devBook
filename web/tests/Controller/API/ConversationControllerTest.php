<?php
declare(strict_types=1);

namespace App\Tests\Controller\API;

use App\Controller\API\ConversationController;
use App\Exception\UserNotFoundException;
use App\Service\CommandService;
use App\Command\API\GetConversationListCommand;
use App\Tests\WebTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use \Mockery;

/**
 * Class ConversationControllerTest
 * @package App\Tests\Controller\API
 */
class ConversationControllerTest extends WebTestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

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
        $this->guess->request('GET', '/api/get-conversation-list');
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $this->guess->getResponse()->getStatusCode());
    }

    public function testGetConversationListAction(): void
    {
        $this->user->request('GET', '/api/get-conversation-list');
        $this->assertEquals(Response::HTTP_OK, $this->user->getResponse()->getStatusCode());
    }

    public function testUserNotFound(): void
    {
        $conversationController = Mockery::mock(ConversationController::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $conversationController->shouldReceive('getUser')->once()->andReturnNull();

        $this->expectException(UserNotFoundException::class);
        $conversationController->getConversationListAction(
            Mockery::mock(CommandService::class),
            Mockery::mock(EventDispatcherInterface::class)
        );
    }
}
