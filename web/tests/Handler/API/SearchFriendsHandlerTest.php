<?php
declare(strict_types=1);

namespace App\Tests\Handler\API;

use App\Command\API\SearchFriendsCommand;
use App\Entity\User;
use App\Entity\UserToken;
use App\Event\SearchFriendsEvent;
use App\Handler\API\SearchFriendsHandler;
use App\Repository\UserRepository;
use PHPUnit\Framework\TestCase;
use \Mockery;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class SearchFriendsCommandTest
 * @package App\Tests\Command\API
 */
class SearchFriendsHandlerTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * @var array
     */
    private $result;

    public function testExecute(): void
    {
        $userToken = Mockery::mock(UserToken::class);
        $userToken->shouldReceive('getPublicToken')->once()->andReturn('public_token');

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getFirstName')->once()->andReturn('FirstName');
        $user->shouldReceive('getSurname')->once()->andReturn('Surname');
        $user->shouldReceive('getUserTokenReferences')->once()->andReturn($userToken);

        $userRepository = Mockery::mock(UserRepository::class);
        $userRepository->shouldReceive('findByFirstNameOrSurname')
            ->withArgs(['&amp;&quot;&lt;&gt;', 1])->once()->andReturn([$user]);

        $searchFriendsCommand = new SearchFriendsCommand(1, '&"<>');

        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $eventDispatcher->shouldReceive('dispatch')
            ->withArgs(function (string $name, $event) {
                    if ($event instanceof SearchFriendsEvent && $name === SearchFriendsEvent::NAME) {
                        $this->result = $event->getUsers();
                        return true;
                    }

                    return false;
                }
        );

        $searchFriendsHandler = new SearchFriendsHandler($userRepository, $eventDispatcher);
        $searchFriendsHandler->handle($searchFriendsCommand);

        $this->assertEquals('FirstName Surname', $this->result[0]['fullName']);
        $this->assertEquals('public_token', $this->result[0]['userId']);
    }
}
