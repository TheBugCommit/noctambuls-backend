<?php

declare(strict_types=1);

namespace App\UI\Http\Backoffice\UserManagement;

use App\Auth\Application\ListBackofficeUsers\ListBackofficeUsersHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class BackofficeUserListController extends AbstractController
{
    public function __construct(private readonly ListBackofficeUsersHandler $handler)
    {
    }

    public function __invoke(): Response
    {
        $response = $this->handler->handle();

        return $this->render('backoffice/user_management/backoffice_users.html.twig', [
            'users' => $response->users,
        ]);
    }
}
