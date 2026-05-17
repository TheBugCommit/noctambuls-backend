<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\Firebase;

use App\Shared\Domain\Service\PushNotificationSenderInterface;
use Google\Client;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class FirebasePushNotificationSender implements PushNotificationSenderInterface
{
    public function __construct(
        private Client              $googleClient,
        private string              $projectId,
        private string              $topic,
        private HttpClientInterface $http,
    ) {
    }

    public function send(string $title, string $body): void
    {
        $tokenData = $this->googleClient->fetchAccessTokenWithAssertion();
        $token = $tokenData['access_token'] ?? throw new \RuntimeException('Could not retrieve Firebase access token.');

        $this->http->request('POST', sprintf(
            'https://fcm.googleapis.com/v1/projects/%s/messages:send',
            $this->projectId,
        ), [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'message' => [
                    'topic'        => $this->topic,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                ],
            ],
        ]);
    }
}
