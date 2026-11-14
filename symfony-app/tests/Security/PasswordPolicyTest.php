<?php

namespace App\Tests\Security;

use App\Security\PasswordPolicy;
use PHPUnit\Framework\TestCase;

class PasswordPolicyTest extends TestCase
{
    public function testElevenCharactersRejected(): void
    {
        $this->assertStringContainsString('at least 12', PasswordPolicy::validate('elevenchars', 'elevenchars'));
    }

    public function testTwelveCharactersAccepted(): void
    {
        $this->assertNull(PasswordPolicy::validate('twelve-chars', 'twelve-chars'));
    }

    public function testMultibyteCountsCharactersNotBytes(): void
    {
        $this->assertNotNull(PasswordPolicy::validate(str_repeat('é', 11), str_repeat('é', 11)));
        $this->assertNull(PasswordPolicy::validate(str_repeat('é', 12), str_repeat('é', 12)));
    }

    public function testMismatchRejected(): void
    {
        $this->assertStringContainsString('did not match', PasswordPolicy::validate('twelve-chars', 'twelve-charz'));
    }
}
