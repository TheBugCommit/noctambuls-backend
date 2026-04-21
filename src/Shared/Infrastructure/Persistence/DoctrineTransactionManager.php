<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Persistence;

use App\Shared\Domain\Service\TransactionManagerInterface;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineTransactionManager implements TransactionManagerInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    /**
     * @throws Exception
     */
    public function begin(): void
    {
        $this->em->getConnection()->beginTransaction();
    }

    /**
     * @throws Exception
     */
    public function commit(): void
    {
        $this->em->flush();
        $this->em->getConnection()->commit();
    }

    /**
     * @throws Exception
     */
    public function rollback(): void
    {
        $this->em->getConnection()->rollBack();
        $this->em->clear();
    }
}
