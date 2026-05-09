<?php

declare(strict_types=1);

namespace App\UI\Http\Backoffice\UserManagement;

use App\Auth\Application\DeleteBackofficeUser\DeleteBackofficeUserHandler;
use App\Auth\Application\DeleteBackofficeUser\DeleteBackofficeUserRequest;
use App\Auth\Domain\Exception\UserNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class DeleteBackofficeUserController extends AbstractController
{
    public function __construct(private readonly DeleteBackofficeUserHandler $handler)
    {
    }

    public function __invoke(Request $request, string $id): Response
    {
        if (!$this->isCsrfTokenValid('backoffice_user_delete_' . $id, $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'user_management.error.invalid_csrf');

            return $this->redirectToRoute('backoffice_backoffice_users_list');
        }

        try {
            $this->handler->handle(new DeleteBackofficeUserRequest($id));
            $this->addFlash('success', 'user_management.flash.backoffice_user_deleted');
        } catch (UserNotFoundException) {
            $this->addFlash('error', 'user_management.error.not_found');
        } catch (\Throwable) {
            $this->addFlash('error', 'user_management.error.unexpected');
        }

        return $this->redirectToRoute('backoffice_backoffice_users_list');
    }
}
