<?php
declare(strict_types=1);

namespace App\Tests\NullObject\Repository;

use App\NullObject\Repository\NullMessageRepository;
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
