<?php

declare(strict_types=1);

namespace App\Tests\Census\Infrastructure\Service;

use App\Census\Domain\Entity\Census;
use App\Census\Domain\ValueObject\CensusDate;
use App\Census\Domain\ValueObject\CensusId;
use App\Census\Domain\ValueObject\CensusLocationName;
use App\Census\Domain\ValueObject\CensusTime;
use App\Census\Domain\ValueObject\CensusTitle;
use App\Census\Infrastructure\Service\JwtCensusLinkGenerator;
use App\Shared\Domain\ValueObject\Latitude;
use App\Shared\Domain\ValueObject\Longitude;
use PHPUnit\Framework\TestCase;

final class JwtCensusLinkGeneratorTest extends TestCase
{
    private const string UUID       = '550e8400-e29b-41d4-a716-446655440000';
    private const string PASSPHRASE = 'test_passphrase';

    private string $tmpKeyFile;

    protected function setUp(): void
    {
        $keypair = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        openssl_pkey_export($keypair, $privateKeyPem, self::PASSPHRASE);

        $this->tmpKeyFile = tempnam(sys_get_temp_dir(), 'jwt_test_');
        file_put_contents($this->tmpKeyFile, $privateKeyPem);

        $_SERVER['HTTP_HOST'] = 'example.com';
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tmpKeyFile)) {
            unlink($this->tmpKeyFile);
        }

        unset($_SERVER['HTTPS'], $_SERVER['HTTP_HOST']);
    }

    private function createCensus(): Census
    {
        return Census::create(
            new CensusId(self::UUID),
            new CensusTitle('Test Census'),
            new CensusDate('2026-06-15'),
            new CensusTime('10:00'),
            new CensusLocationName('Test Location'),
            new Latitude(41.0),
            new Longitude(1.0),
            null,
        );
    }

    public function test_generateFor_returns_url_with_jwt_token(): void
    {
        $_SERVER['HTTPS'] = 'on';

        $generator = new JwtCensusLinkGenerator($this->tmpKeyFile, self::PASSPHRASE);
        $url       = $generator->generateFor($this->createCensus());

        self::assertStringStartsWith('https://example.com/census/join?token=', $url);

        $token  = substr($url, strpos($url, 'token=') + 6);
        $parts  = explode('.', $token);
        self::assertCount(3, $parts, 'JWT must have 3 parts separated by dots');
    }

    public function test_generateFor_uses_http_when_no_https(): void
    {
        unset($_SERVER['HTTPS']);

        $generator = new JwtCensusLinkGenerator($this->tmpKeyFile, self::PASSPHRASE);
        $url       = $generator->generateFor($this->createCensus());

        self::assertStringStartsWith('http://example.com/census/join?token=', $url);
    }

    public function test_generateFor_throws_when_private_key_file_not_found(): void
    {
        $generator = new JwtCensusLinkGenerator('/non/existent/path.pem', self::PASSPHRASE);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cant read private KEY');

        $generator->generateFor($this->createCensus());
    }

    public function test_generateFor_throws_when_passphrase_is_wrong(): void
    {
        $generator = new JwtCensusLinkGenerator($this->tmpKeyFile, 'wrong_passphrase');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cant descifrate private KEY');

        $generator->generateFor($this->createCensus());
    }
}
