<?php
declare(strict_types=1);

namespace App\Tests\Service\Validator;

use App\Service\Validator\ConversationIdValidator;
use PHPUnit\Framework\TestCase;


/**
 * Class MessageIdValidatorTest
 * @package App\Tests\Util
 */
class ConversationIdValidatorTest extends TestCase
{

    public function testValidate(): void
    {
        $messageIdValidator = new ConversationIdValidator();
        $this->assertFalse($messageIdValidator->validate('FAILED1234-'));
        $this->assertTrue($messageIdValidator->validate('193f246a-2e80-4c7c-a2ef-dcb1cc204cf5'));
    }
}
