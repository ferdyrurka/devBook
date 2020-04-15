<?php
declare(strict_types=1);

namespace App\Tests\Repository\NullObject;

use App\Repository\NullObject\NullMessageRepository;
use PHPUnit\Framework\TestCase;

/**
 * Class NullMessageRepositoryTest
 * @package App\Tests\NullObject\Repository
 */
class NullMessageRepositoryTest extends TestCase
{
    public function testGetLastMessageByConversationId(): void
    {
        $nullMessageRepository = new NullMessageRepository();
        $message = $nullMessageRepository->getLastMessageByConversationId('');

        $this->assertEquals('Lack', $message->getMessage());
    }
}
