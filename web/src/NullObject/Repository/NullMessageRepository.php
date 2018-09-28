<?php
declare(strict_types=1);

namespace App\NullObject\Repository;

use App\Entity\Message;

/**
 * Class NullMessageRepository
 */
class NullMessageRepository
{
    /**
     * @param string $conversationId
     * @return Message
     */
    public function getLastMessageByConversationId(string $conversationId): Message
    {
        $message = new Message();
        $message->setMessage('Lack');

        return $message;
    }
}
