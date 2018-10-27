<?php
declare(strict_types=1);

namespace App\Tests\Handler\API;

use App\Command\API\SearchFriendsCommand;
use App\Entity\User;
use App\Entity\UserToken;
use App\Handler\API\SearchFriendsHandler;
use App\Repository\UserRepository;
use PHPUnit\Framework\TestCase;
use \Mockery;

/**
 * Class SearchFriendsCommandTest
 * @package App\Tests\Command\API
 */
class SearchFriendsHandlerTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function testExecute()
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

        $searchFriendsHandler = new SearchFriendsHandler($userRepository);
        $searchFriendsHandler->handle($searchFriendsCommand);
        $result = $searchFriendsHandler->getResult();

        $this->assertEquals('FirstName Surname', $result[0]['fullName']);
        $this->assertEquals('public_token', $result[0]['userId']);
    }
}
