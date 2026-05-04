<?php

declare(strict_types=1);

namespace App\UI\Http\Backoffice\UserManagement;

use App\Auth\Application\AnonymizeUser\AnonymizeUserHandler;
use App\Auth\Application\AnonymizeUser\AnonymizeUserRequest;
use App\Auth\Domain\Exception\UserAlreadyAnonymizedException;
use App\Auth\Domain\Exception\UserNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class DeleteAppUserController extends AbstractController
{
    public function __construct(private readonly AnonymizeUserHandler $handler)
    {
    }

    public function __invoke(Request $request, string $id): Response
    {
        if (!$this->isCsrfTokenValid('user_delete_' . $id, $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'user_management.error.invalid_csrf');

            return $this->redirectToRoute('backoffice_users_list');
        }

        try {
            $this->handler->handle(new AnonymizeUserRequest($id));
            $this->addFlash('success', 'user_management.flash.anonymized');
        } catch (UserAlreadyAnonymizedException) {
            $this->addFlash('error', 'user_management.error.already_anonymized');
        } catch (UserNotFoundException) {
            $this->addFlash('error', 'user_management.error.not_found');
        } catch (\Throwable) {
            $this->addFlash('error', 'user_management.error.unexpected');
        }

        return $this->redirectToRoute('backoffice_users_list');
    }
}
