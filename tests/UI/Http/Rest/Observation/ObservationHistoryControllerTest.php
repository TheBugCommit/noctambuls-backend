<?php

declare(strict_types=1);

namespace App\Tests\UI\Http\Rest\Observation;

use App\Auth\Domain\Entity\AuthUser;
use App\Auth\Domain\ValueObject\AuthUserId;
use App\Auth\Domain\ValueObject\Email;
use App\Auth\Domain\ValueObject\Provider; // Ajusta si Provider és un Enum en un altre namespace
use App\Auth\Infrastructure\Security\AuthUserSymfonyAdapter;
use App\Observation\Application\GetObservationHistory\GetObservationHistoryHandler;
use App\Observation\Application\GetObservationHistory\GetObservationHistoryRequest;
use App\Observation\Application\GetObservationHistory\GetObservationHistoryResponse;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ObservationHistoryControllerTest extends WebTestCase
{
    private $client;
    private $handlerMock;


    protected function setUp(): void
    {
        $this->client = static::createClient();

        $this->handlerMock = $this->createMock(GetObservationHistoryHandler::class);

        static::getContainer()->set(GetObservationHistoryHandler::class, $this->handlerMock);
    }

    public function test_it_returns_observation_history_with_default_pagination(): void
    {
        $userId = '550e8400-e29b-41d4-a716-446655440123';
        $this->loginUser($userId);

        $this->handlerMock->expects($this->once())
            ->method('handle')
            ->with($this->callback(fn (GetObservationHistoryRequest $req) =>
                $req->annotatorId === $userId &&
                $req->offset === 0 &&
                $req->limit === 20
            ))
            ->willReturn(new GetObservationHistoryResponse(
                sessions: [['id' => '550e8400-e29b-41d4-a716-446655440001']],
                meta: ['totalItems' => 1, 'currentPage' => 1, 'totalPages' => 1, 'hasMore' => false]
            ));

        $this->client->request('GET', '/api/observations/history');

        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertCount(1, $content['sessions']);
        $this->assertEquals('550e8400-e29b-41d4-a716-446655440001', $content['sessions'][0]['id']);
    }

    public function test_it_applies_custom_pagination_parameters_and_enforces_boundaries(): void
    {
        $userId = '550e8400-e29b-41d4-a716-446655440123';
        $this->loginUser($userId);

        $this->handlerMock->expects($this->once())
            ->method('handle')
            ->with($this->callback(fn (GetObservationHistoryRequest $req) =>
                $req->annotatorId === $userId &&
                $req->offset === 0 &&
                $req->limit === 100
            ))
            ->willReturn(new GetObservationHistoryResponse([], []));

        $this->client->request('GET', '/api/observations/history?offset=-10&limit=500');

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
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
