<?php

declare(strict_types=1);

namespace App\UI\Http\Backoffice\UserManagement;

use App\Auth\Application\ListBackofficeUsers\ListBackofficeUsersHandler;
use App\Auth\Application\UpdateBackofficeUser\UpdateBackofficeUserHandler;
use App\Auth\Application\UpdateBackofficeUser\UpdateBackofficeUserRequest;
use App\Auth\Domain\Exception\UserAlreadyExistsException;
use App\Auth\Domain\Exception\UserNotFoundException;
use App\Auth\Domain\Repository\AuthUserRepositoryInterface;
use App\Auth\Domain\ValueObject\AuthUserId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class EditBackofficeUserController extends AbstractController
{
    public function __construct(
        private readonly UpdateBackofficeUserHandler $handler,
        private readonly AuthUserRepositoryInterface $userRepository,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $user = $this->userRepository->findById(new AuthUserId($id));
        if ($user === null) {
            $this->addFlash('error', 'user_management.error.not_found');

            return $this->redirectToRoute('backoffice_backoffice_users_list');
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('backoffice_user_edit_' . $id, $request->request->get('_csrf_token'))) {
                $this->addFlash('error', 'user_management.error.invalid_csrf');

                return $this->redirectToRoute('backoffice_backoffice_users_edit', ['id' => $id]);
            }

            try {
                $email    = $request->request->get('email') ?: null;
                $password = $request->request->get('password') ?: null;
                $role     = $request->request->get('role') ?: null;

                $this->handler->handle(new UpdateBackofficeUserRequest($id, $email, $password, $role));
                $this->addFlash('success', 'user_management.flash.backoffice_user_updated');

                return $this->redirectToRoute('backoffice_backoffice_users_list');
            } catch (UserAlreadyExistsException) {
                $this->addFlash('error', 'user_management.error.email_taken');
            } catch (UserNotFoundException) {
                $this->addFlash('error', 'user_management.error.not_found');
            } catch (\InvalidArgumentException $e) {
                $this->addFlash('error', $e->getMessage());
            } catch (\Throwable) {
                $this->addFlash('error', 'user_management.error.unexpected');
            }
        }

        return $this->render('backoffice/user_management/edit_backoffice_user.html.twig', [
            'user' => [
                'id'    => $user->id()->value(),
                'email' => $user->email()->value(),
                'role'  => $user->role()->value,
            ],
        ]);
    }
}
