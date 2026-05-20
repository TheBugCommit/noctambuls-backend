<?php

declare(strict_types=1);

namespace App\Tests\UI\Http\Rest\Profile;

use App\Auth\Infrastructure\Security\AuthUserSymfonyAdapter;
use App\Profile\Application\GetProfile\GetProfileHandler;
use App\Profile\Application\GetProfile\GetProfileResponse;
use App\Profile\Domain\Exception\ProfileNotFoundException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetProfileControllerTest extends WebTestCase
{
    private const string PROFILE_UUID = '550e8400-e29b-41d4-a716-446655440001';
    private const string OTHER_UUID   = '550e8400-e29b-41d4-a716-446655440002';

    private $client;
    private $handlerMock;
    private string $apiKey;

    protected function setUp(): void
    {
        static::ensureKernelShutdown();
        $this->apiKey = (string) $_ENV['API_KEY'];
        $this->client = static::createClient([], ['HTTP_X-API-KEY' => $this->apiKey]);

        $this->handlerMock = $this->createMock(GetProfileHandler::class);
        static::getContainer()->set(GetProfileHandler::class, $this->handlerMock);
    }

    public function test_returns_200_with_profile_data_when_owner_requests_own_profile(): void
    {
        $this->loginUser(self::PROFILE_UUID);

        $now = new \DateTimeImmutable('2026-01-01T00:00:00Z');
        $this->handlerMock->expects($this->once())
            ->method('handle')
            ->willReturn(new GetProfileResponse(
                id: self::PROFILE_UUID,
                name: 'John Doe',
                phone: '+34600000000',
                ornithologistCode: '#OR-N-0001',
                createdAt: $now,
                updatedAt: $now,
            ));

        $this->client->request('GET', '/api/profile/' . self::PROFILE_UUID);

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertEquals(self::PROFILE_UUID, $content['id']);
        $this->assertEquals('John Doe', $content['name']);
    }

    public function test_returns_403_when_user_requests_another_users_profile(): void
    {
        $this->loginUser(self::OTHER_UUID);

        $this->handlerMock->expects($this->never())->method('handle');

        $this->client->request('GET', '/api/profile/' . self::PROFILE_UUID);

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function test_returns_404_when_profile_not_found(): void
    {
        $this->loginUser(self::PROFILE_UUID);

        $this->handlerMock->method('handle')
            ->willThrowException(new ProfileNotFoundException());

        $this->client->request('GET', '/api/profile/' . self::PROFILE_UUID);

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertEquals('NOT_FOUND', $content['code']);
    }

    private function loginUser(string $userId): void
    {
        $userMock = $this->createMock(AuthUserSymfonyAdapter::class);
        $userMock->method('getAuthUserId')->willReturn($userId);
        $userMock->method('getRoles')->willReturn(['ROLE_USER']);
        $userMock->method('getUserIdentifier')->willReturn('test@example.com');

        $this->client->loginUser($userMock, 'api');
    }
}
