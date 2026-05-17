<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\Persistence;

use App\Notification\Domain\Entity\NotificationTemplate;
use App\Notification\Domain\Repository\NotificationTemplateRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class NotificationTemplateRepository implements NotificationTemplateRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function findByEvent(string $event): ?NotificationTemplate
    {
        return $this->em->getRepository(NotificationTemplate::class)->findOneBy(['event' => $event]);
    }

    public function findAll(): array
    {
        return $this->em->getRepository(NotificationTemplate::class)->findAll();
    }

    public function save(NotificationTemplate $template): void
    {
        $this->em->persist($template);
    }
}
