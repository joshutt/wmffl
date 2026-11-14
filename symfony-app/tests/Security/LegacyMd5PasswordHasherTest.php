<?php

namespace App\Tests\Security;

use App\Security\LegacyMd5PasswordHasher;
use PHPUnit\Framework\TestCase;

class LegacyMd5PasswordHasherTest extends TestCase
{
    private LegacyMd5PasswordHasher $hasher;

    protected function setUp(): void
    {
        $this->hasher = new LegacyMd5PasswordHasher();
    }

    public function testVerifiesRawMd5(): void
    {
        $this->assertTrue($this->hasher->verify(md5('secret'), 'secret'));
    }

    public function testVerifiesUppercaseStoredHash(): void
    {
        $this->assertTrue($this->hasher->verify(strtoupper(md5('secret')), 'secret'));
    }

    public function testVerifiesMysqliEscapedForm(): void
    {
        // Legacy login.php ran mysqli_real_escape_string() before md5().
        $stored = md5("it\\'s-a-test");

        $this->assertTrue($this->hasher->verify($stored, "it's-a-test"));
    }

    public function testVerifiesRawFormOfPasswordWithQuote(): void
    {
        // forgotpassword.php stored md5(raw).
        $this->assertTrue($this->hasher->verify(md5("it's-a-test"), "it's-a-test"));
    }

    public function testRejectsWrongPassword(): void
    {
        $this->assertFalse($this->hasher->verify(md5('secret'), 'Secret'));
    }

    public function testEmptyStoredHashNeverVerifies(): void
    {
        $this->assertFalse($this->hasher->verify('', ''));
        $this->assertFalse($this->hasher->verify('', 'anything'));
    }

    public function testAlwaysNeedsRehash(): void
    {
        $this->assertTrue($this->hasher->needsRehash(md5('x')));
    }

    public function testNeverCreatesHashes(): void
    {
        $this->expectException(\LogicException::class);
        $this->hasher->hash('x');
    }

    public function testEscapeMatchesMysqliRules(): void
    {
        $this->assertSame("a\\\\b\\'c\\\"d\\ne\\rf\\0g\\Z", LegacyMd5PasswordHasher::mysqliEscape("a\\b'c\"d\ne\rf\0g\x1a"));
    }
}
