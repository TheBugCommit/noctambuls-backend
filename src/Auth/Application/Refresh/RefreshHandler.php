<?php
declare(strict_types=1);

namespace App\Auth\Application\Refresh;

use App\Auth\Domain\Entity\RefreshToken;
use App\Auth\Domain\Exception\InvalidRefreshTokenException;
use App\Auth\Domain\Exception\UserNotFoundException;
use App\Auth\Domain\Repository\AuthUserRepositoryInterface;
use App\Auth\Domain\Repository\RefreshTokenRepositoryInterface;
use App\Auth\Domain\Service\JwtTokenGeneratorInterface;
use App\Auth\Domain\ValueObject\RefreshTokenId;
use App\Auth\Domain\ValueObject\TokenString;
use App\Shared\Domain\Service\TransactionManagerInterface;
use App\Shared\Domain\Service\UuidGeneratorInterface;

final readonly class RefreshHandler
{
    public function __construct(
        private RefreshTokenRepositoryInterface $refreshTokenRepository,
        private AuthUserRepositoryInterface     $userRepository,
        private JwtTokenGeneratorInterface      $jwtGenerator,
        private TransactionManagerInterface     $transactionManager,
        private UuidGeneratorInterface          $uuidGenerator,
    ) {}

    public function handle(RefreshRequest $request): RefreshResponse
    {
        $tokenVo = new TokenString($request->refreshToken);
        $refreshToken = $this->refreshTokenRepository->findByToken($tokenVo);

        if ($refreshToken === null || $refreshToken->isExpired()) {
            if ($refreshToken !== null) {
                $this->transactionManager->begin();
                try {
                    $this->refreshTokenRepository->delete($refreshToken);
                    $this->transactionManager->commit();
                } catch (\Throwable $e) {
                    $this->transactionManager->rollback();
                }
            }
            throw new InvalidRefreshTokenException();
        }

        $user = $this->userRepository->findById($refreshToken->userId());
        if ($user === null) {
            throw new UserNotFoundException();
        }

        $this->transactionManager->begin();
        try {
            $this->refreshTokenRepository->delete($refreshToken);

            $newTokenStringVo = TokenString::generate();
            $newRefreshToken = RefreshToken::create(
                new RefreshTokenId($this->uuidGenerator->generate()),
                $newTokenStringVo,
                $user->id()
            );

            $this->refreshTokenRepository->save($newRefreshToken);

            $response = new RefreshResponse(
                $this->jwtGenerator->generate($user),
                $newTokenStringVo->value()
            );

            $this->transactionManager->commit();
            return $response;

        } catch (\Throwable $exception) {
            $this->transactionManager->rollback();
            throw $exception;
        }
    }
}
