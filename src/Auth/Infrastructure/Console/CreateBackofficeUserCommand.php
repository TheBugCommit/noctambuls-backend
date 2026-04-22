<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Console;

use App\Auth\Application\SetUserPassword\SetUserPasswordHandler;
use App\Auth\Application\SetUserPassword\SetUserPasswordRequest;
use App\Auth\Application\ChangeUserRole\ChangeUserRoleHandler;
use App\Auth\Application\ChangeUserRole\ChangeUserRoleRequest;
use App\Auth\Domain\Entity\AuthUser;
use App\Auth\Domain\Repository\AuthUserRepositoryInterface;
use App\Auth\Domain\Service\PasswordHasherInterface;
use App\Auth\Domain\ValueObject\AuthUserId;
use App\Auth\Domain\ValueObject\Email;
use App\Auth\Domain\ValueObject\HashedPassword;
use App\Auth\Domain\ValueObject\Provider;
use App\Auth\Domain\ValueObject\Role;
use App\Shared\Domain\Service\TransactionManagerInterface;
use App\Shared\Domain\Service\UuidGeneratorInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:user:create-backoffice',
    description: 'Create a backoffice user (Reviewer or Admin) with email and password.',
)]
final class CreateBackofficeUserCommand extends Command
{
    public function __construct(
        private readonly AuthUserRepositoryInterface $userRepository,
        private readonly SetUserPasswordHandler      $passwordHandler,
        private readonly ChangeUserRoleHandler       $roleHandler,
        private readonly TransactionManagerInterface $transactionManager,
        private readonly UuidGeneratorInterface      $uuidGenerator, private readonly PasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Create Backoffice User');

        $email = $io->ask('Email address', null, static function (?string $v): string {
            if (!$v || !filter_var($v, FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException('Invalid email address.');
            }
            return $v;
        });

        $role = $io->choice('Role', [Role::REVIEWER->value, Role::ADMIN->value], Role::REVIEWER->value);

        $password = $io->askHidden('Password (min 8 chars)', static function (?string $v): string {
            if (!$v || strlen($v) < 8) {
                throw new \RuntimeException('Password must be at least 8 characters.');
            }
            return $v;
        });

        $emailVo = new Email($email);
        $existing = $this->userRepository->findByEmail($emailVo);

        if ($existing !== null) {
            $this->passwordHandler->handle(new SetUserPasswordRequest($existing->id()->value(), $password));
            $this->roleHandler->handle(new ChangeUserRoleRequest($existing->id()->value(), $role));
            $this->transactionManager->commit();
            $io->success(sprintf('Updated existing user %s with role %s.', $email, $role));
            return Command::SUCCESS;
        }

        $this->transactionManager->begin();
        try {
            $user = AuthUser::create(
                new AuthUserId($this->uuidGenerator->generate()),
                $emailVo,
                Provider::internal(),
                Role::from($role),
                new HashedPassword($this->passwordHasher->hash($password))
            );

            $this->userRepository->save($user);

            $this->transactionManager->commit();
            $io->success(sprintf('Created backoffice user %s with role %s.', $email, $role));
            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $this->transactionManager->rollback();
            $io->error('Failed to create/update user: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
