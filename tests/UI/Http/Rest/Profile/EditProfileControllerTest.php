<?php

declare(strict_types=1);

namespace App\Tests\UI\Http\Rest\Profile;

use App\Auth\Infrastructure\Security\AuthUserSymfonyAdapter;
use App\Profile\Application\EditProfile\EditProfileHandler;
use App\Profile\Application\EditProfile\EditProfileRequest;
use App\Profile\Application\EditProfile\EditProfileResponse;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class EditProfileControllerTest extends WebTestCase
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

        $this->handlerMock = $this->createMock(EditProfileHandler::class);
        static::getContainer()->set(EditProfileHandler::class, $this->handlerMock);
    }

    public function test_returns_200_when_owner_updates_name(): void
    {
        $this->loginUser(self::PROFILE_UUID);

        $now = new \DateTimeImmutable('2026-01-01T00:00:00Z');
        $this->handlerMock->expects($this->once())
            ->method('handle')
            ->with($this->callback(fn(EditProfileRequest $req) =>
                $req->name === 'New Name' && $req->phone === null
            ))
            ->willReturn(new EditProfileResponse(
                id: self::PROFILE_UUID,
                name: 'New Name',
                phone: '+34600000000',
                ornithologistCode: '#OR-N-0001',
                createdAt: $now,
                updatedAt: $now,
            ));

        $this->patch(self::PROFILE_UUID, ['name' => 'New Name']);

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertEquals('New Name', $content['name']);
    }

    public function test_returns_200_when_owner_updates_phone(): void
    {
        $this->loginUser(self::PROFILE_UUID);

        $now = new \DateTimeImmutable('2026-01-01T00:00:00Z');
        $this->handlerMock->expects($this->once())
            ->method('handle')
            ->with($this->callback(fn(EditProfileRequest $req) =>
                $req->phone === '+34600000001' && $req->name === null
            ))
            ->willReturn(new EditProfileResponse(
                id: self::PROFILE_UUID,
                name: 'John Doe',
                phone: '+34600000001',
                ornithologistCode: '#OR-N-0001',
                createdAt: $now,
                updatedAt: $now,
            ));

        $this->patch(self::PROFILE_UUID, ['phone' => '+34600000001']);

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function test_returns_403_when_user_edits_another_users_profile(): void
    {
        $this->loginUser(self::OTHER_UUID);

        $this->handlerMock->expects($this->never())->method('handle');

        $this->patch(self::PROFILE_UUID, ['name' => 'Hack']);

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function test_returns_400_when_no_fields_provided(): void
    {
        $this->loginUser(self::PROFILE_UUID);

        $this->patch(self::PROFILE_UUID, []);

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertEquals('INVALID_ARGUMENT', $content['code']);
    }

    public function test_returns_400_when_name_is_empty_string(): void
    {
        $this->loginUser(self::PROFILE_UUID);

        $this->patch(self::PROFILE_UUID, ['name' => '']);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function test_returns_400_when_phone_is_empty_string(): void
    {
        $this->loginUser(self::PROFILE_UUID);

        $this->patch(self::PROFILE_UUID, ['phone' => '']);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    private function patch(string $id, array $body): void
    {
        $this->client->request(
            'PATCH',
            '/api/profile/' . $id,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($body),
        );
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
