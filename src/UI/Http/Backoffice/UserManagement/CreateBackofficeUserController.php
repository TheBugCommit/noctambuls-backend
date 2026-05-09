<?php

declare(strict_types=1);

namespace App\UI\Http\Backoffice\UserManagement;

use App\Auth\Application\CreateBackofficeUser\CreateBackofficeUserHandler;
use App\Auth\Application\CreateBackofficeUser\CreateBackofficeUserRequest;
use App\Auth\Domain\Exception\UserAlreadyExistsException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CreateBackofficeUserController extends AbstractController
{
    public function __construct(private readonly CreateBackofficeUserHandler $handler)
    {
    }

    public function __invoke(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('backoffice_user_create', $request->request->get('_csrf_token'))) {
                $this->addFlash('error', 'user_management.error.invalid_csrf');

                return $this->redirectToRoute('backoffice_backoffice_users_create');
            }

            try {
                $this->handler->handle(new CreateBackofficeUserRequest(
                    email: (string) $request->request->get('email', ''),
                    role: (string) $request->request->get('role', ''),
                    plainPassword: (string) $request->request->get('password', ''),
                ));
                $this->addFlash('success', 'user_management.flash.backoffice_user_created');

                return $this->redirectToRoute('backoffice_backoffice_users_list');
            } catch (UserAlreadyExistsException) {
                $this->addFlash('error', 'user_management.error.email_taken');
            } catch (\InvalidArgumentException $e) {
                $this->addFlash('error', $e->getMessage());
            } catch (\Throwable) {
                $this->addFlash('error', 'user_management.error.unexpected');
            }
        }

        return $this->render('backoffice/user_management/create_backoffice_user.html.twig');
    }
}
