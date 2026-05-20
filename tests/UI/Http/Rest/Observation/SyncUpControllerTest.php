<?php

declare(strict_types=1);

namespace App\Tests\UI\Http\Rest\Observation;

use App\Auth\Infrastructure\Security\AuthUserSymfonyAdapter;
use App\Observation\Application\SyncUp\SyncUpHandler;
use App\Observation\Application\SyncUp\SyncUpResponse;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SyncUpControllerTest extends WebTestCase
{
    private $client;
    private $handlerMock;
    private string $apiKey;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->apiKey = (string) getenv('API_KEY');

        // Creem un Mock del Handler per controlar-ne la resposta
        $this->handlerMock = $this->createMock(SyncUpHandler::class);

        // Injectem el Mock al contenidor de serveis de Symfony (Test Container)
        static::getContainer()->set(SyncUpHandler::class, $this->handlerMock);
    }

    public function test_it_returns_400_bad_request_on_invalid_json_payload(): void
    {
        $this->loginUser();

        // Enviem un body invàlid (sense l'array de 'sessions')
        $this->client->request('POST', '/api/sync/observations', [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_X-API-KEY' => $this->apiKey], json_encode(['wrong_key' => true]));

        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        // Comprovem que l'ApiExceptionListener ha formatat l'error
        $content = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('message', $content);
        $this->assertArrayHasKey('code', $content);
        // Segons el teu Listener, un status 400 es mapeja a INVALID_ARGUMENT
        $this->assertEquals('INVALID_ARGUMENT', $content['code']);
        $this->assertEquals('Bad Request', $content['message']); // Response::$statusTexts[400]
    }

    public function test_it_returns_413_payload_too_large_when_exceeding_100_sessions(): void
    {
        $this->loginUser();

        // Creem 101 sessions falses
        $sessions = array_fill(0, 101, ['id' => 'dummy']);

        $this->client->request('POST', '/api/sync/observations', [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_X-API-KEY' => $this->apiKey], json_encode(['sessions' => $sessions]));

        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_REQUEST_ENTITY_TOO_LARGE, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);

        // Segons el teu Listener, com que 413 no està a l'array ERROR_TYPE_TO_STATUS,
        // fa el fallback a INTERNAL_SERVER_ERROR i posa el text HTTP corresponent.
        $this->assertEquals('INTERNAL_SERVER_ERROR', $content['code']);
        $this->assertEquals('Content Too Large', $content['message']);
    }

    public function test_it_returns_failed_uuids_when_3_sessions_are_invalid(): void
    {
        $this->loginUser();

        $uuid1 = '11111111-e29b-41d4-a716-446655440000';
        $uuid2 = '22222222-e29b-41d4-a716-446655440000';
        $uuid3 = '33333333-e29b-41d4-a716-446655440000';

        $sessionsPayload = [
            ['id' => $uuid1, 'data' => 'invalid'],
            ['id' => $uuid2, 'data' => 'invalid'],
            ['id' => $uuid3, 'data' => 'invalid'],
        ];

        // Configurem el Mock del Handler perquè retorni la resposta del Partial Success
        // (Cap sincronitzada, 3 fallades)
        $this->handlerMock->expects($this->once())
            ->method('handle')
            ->willReturn(new SyncUpResponse(
                synced: [],
                failed: [
                    ['sessionId' => $uuid1, 'reason' => 'invalid_data'],
                    ['sessionId' => $uuid2, 'reason' => 'missing_listening_point'],
                    ['sessionId' => $uuid3, 'reason' => 'duplicate_session_for_census'],
                ]
            ));

        $this->client->request('POST', '/api/sync/observations', [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_X-API-KEY' => $this->apiKey], json_encode(['sessions' => $sessionsPayload]));

        $response = $this->client->getResponse();

        // La petició HTTP és correcta (200 OK), el error és "de domini" dins el payload
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);

        // Verifiquem que l'estructura de dades conté les 3 sessions amb els seus UUID
        $this->assertEmpty($content['synced']);
        $this->assertCount(3, $content['failed']);

        $this->assertEquals($uuid1, $content['failed'][0]['sessionId']);
        $this->assertEquals('invalid_data', $content['failed'][0]['reason']);

        $this->assertEquals($uuid2, $content['failed'][1]['sessionId']);
        $this->assertEquals('missing_listening_point', $content['failed'][1]['reason']);

        $this->assertEquals($uuid3, $content['failed'][2]['sessionId']);
        $this->assertEquals('duplicate_session_for_census', $content['failed'][2]['reason']);
    }

    /**
     * Helper per simular un usuari autenticat al firewall de Symfony.
     */
    private function loginUser(): void
    {
        // Has de configurar aquest Mock segons com tinguis el teu AuthUserSymfonyAdapter
        $userMock = $this->createMock(AuthUserSymfonyAdapter::class);
        $userMock->method('getAuthUserId')->willReturn('550e8400-e29b-41d4-a716-446655440123');
        $userMock->method('getRoles')->willReturn(['ROLE_USER']);

        // 'api' o 'main', posa el nom del teu firewall on està la ruta /api/sync/observations
        $this->client->loginUser($userMock, 'api');
    }
}
