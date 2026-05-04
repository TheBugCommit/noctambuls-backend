<?php

declare(strict_types=1);

namespace App\UI\Http\Backoffice\UserManagement;

use App\Auth\Application\UnblockUser\UnblockUserHandler;
use App\Auth\Application\UnblockUser\UnblockUserRequest;
use App\Auth\Domain\Exception\UserNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class UnblockUserController extends AbstractController
{
    public function __construct(private readonly UnblockUserHandler $handler)
    {
    }

    public function __invoke(Request $request, string $id): Response
    {
        if (!$this->isCsrfTokenValid('user_unblock_' . $id, $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'user_management.error.invalid_csrf');

            return $this->redirectToRoute('backoffice_users_list');
        }

        try {
            $this->handler->handle(new UnblockUserRequest($id));
            $this->addFlash('success', 'user_management.flash.unblocked');
        } catch (UserNotFoundException) {
            $this->addFlash('error', 'user_management.error.not_found');
        } catch (\Throwable) {
            $this->addFlash('error', 'user_management.error.unexpected');
        }

        return $this->redirectToRoute('backoffice_users_list');
    }
}
