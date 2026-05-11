<?php

declare(strict_types=1);

namespace App\Tests\UI\Http\Rest\Observation;

use App\Auth\Domain\Entity\AuthUser;
use App\Auth\Domain\ValueObject\AuthUserId;
use App\Auth\Domain\ValueObject\Email;
use App\Auth\Domain\ValueObject\HashedPassword;
use App\Auth\Domain\ValueObject\Provider;
use App\Auth\Domain\ValueObject\Role;
use App\Auth\Infrastructure\Security\AuthUserSymfonyAdapter;
use App\Observation\Application\GetParticipatedSessions\GetParticipatedSessionsHandler;
use App\Observation\Application\GetParticipatedSessions\GetParticipatedSessionsRequest;
use App\Observation\Application\GetParticipatedSessions\GetParticipatedSessionsResponse;
use App\Profile\Application\GetProfile\GetProfileHandler;
use App\Profile\Application\GetProfile\GetProfileRequest;
use App\Profile\Application\GetProfile\GetProfileResponse;
use App\Profile\Domain\Exception\ProfileNotFoundException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ParticipatedSessionsControllerTest extends WebTestCase
{
    private $client;
    private $sessionsHandlerMock;
    private $profileHandlerMock;

    protected static function getKernelClass(): string
    {
        return \App\Kernel::class;
    }

    protected function setUp(): void
    {
        $this->client = static::createClient();

        // Fem Mocks dels dos Handlers (Recorda que dg/bypass-finals s'encarrega de permetre-ho)
        $this->sessionsHandlerMock = $this->createMock(GetParticipatedSessionsHandler::class);
        $this->profileHandlerMock = $this->createMock(GetProfileHandler::class);

        static::getContainer()->set(GetParticipatedSessionsHandler::class, $this->sessionsHandlerMock);
        static::getContainer()->set(GetProfileHandler::class, $this->profileHandlerMock);
    }

    public function test_it_returns_participated_sessions_with_default_pagination(): void
    {
        $userId = '550e8400-e29b-41d4-a716-446655440123';
        $this->loginUser($userId);

        $this->profileHandlerMock->expects($this->once())
            ->method('handle')
            ->with($this->callback(fn(GetProfileRequest $req) => $req->id === $userId))
            ->willReturn(new GetProfileResponse(
                id: $userId,
                name: 'Test',
                phone: '611451461',
                ornithologistCode: '#OR-N-0001',
                createdAt: new \DateTimeImmutable()
            ));

        $this->sessionsHandlerMock->expects($this->once())
            ->method('handle')
            ->with($this->callback(fn(GetParticipatedSessionsRequest $req) => $req->ornithologistCode === '#OR-N-0001' &&
                $req->offset === 0 &&
                $req->limit === 20
            ))
            ->willReturn(new GetParticipatedSessionsResponse(
                sessions: [['id' => 'session-1']],
                meta: ['totalItems' => 1, 'currentPage' => 1, 'totalPages' => 1, 'hasMore' => false]
            ));

        $this->client->request('GET', '/api/sessions/participated');

        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertCount(1, $content['sessions']);
        $this->assertEquals('session-1', $content['sessions'][0]['id']);
    }

    public function test_it_applies_custom_pagination_parameters_and_limits_max(): void
    {
        $userId = '550e8400-e29b-41d4-a716-446655440123';
        $this->loginUser($userId);

        $this->profileHandlerMock->method('handle')
            ->willReturn(new GetProfileResponse('id', 'authId', 'Name', 'Last', new \DateTimeImmutable()));

        $this->sessionsHandlerMock->expects($this->once())
            ->method('handle')
            ->with($this->callback(fn(GetParticipatedSessionsRequest $req) => $req->offset === 50 &&
                $req->limit === 100
            ))
            ->willReturn(new GetParticipatedSessionsResponse([], []));

        $this->client->request('GET', '/api/sessions/participated?offset=50&limit=500');

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function test_it_returns_404_if_profile_is_not_found(): void
    {
        $userId = '550e8400-e29b-41d4-a716-446655440999';
        $this->loginUser($userId);

        $this->profileHandlerMock->expects($this->once())
            ->method('handle')
            ->willThrowException(new ProfileNotFoundException('Profile not found'));

        $this->sessionsHandlerMock->expects($this->never())->method('handle');

        $this->client->request('GET', '/api/sessions/participated');

        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertEquals('NOT_FOUND', $content['code']);
    }

    private function loginUser(string $userId): void
    {
        $user = new AuthUserSymfonyAdapter(
            AuthUser::create(
                new AuthUserId($userId),
                new Email('test@example.com'),
                Provider::google(),
            ),
        );

        $this->client->loginUser($user, 'api');
    }
}
