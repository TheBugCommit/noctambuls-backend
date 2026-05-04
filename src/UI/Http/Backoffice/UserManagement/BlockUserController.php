<?php

declare(strict_types=1);

namespace App\UI\Http\Backoffice\UserManagement;

use App\Auth\Application\BlockUser\BlockUserHandler;
use App\Auth\Application\BlockUser\BlockUserRequest;
use App\Auth\Domain\Exception\UserNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class BlockUserController extends AbstractController
{
    public function __construct(private readonly BlockUserHandler $handler)
    {
    }

    public function __invoke(Request $request, string $id): Response
    {
        if (!$this->isCsrfTokenValid('user_block_' . $id, $request->request->get('_csrf_token'))) {
            $this->addFlash('error', 'user_management.error.invalid_csrf');

            return $this->redirectToRoute('backoffice_users_list');
        }

        try {
            $this->handler->handle(new BlockUserRequest($id));
            $this->addFlash('success', 'user_management.flash.blocked');
        } catch (UserNotFoundException) {
            $this->addFlash('error', 'user_management.error.not_found');
        } catch (\Throwable) {
            $this->addFlash('error', 'user_management.error.unexpected');
        }

        return $this->redirectToRoute('backoffice_users_list');
    }
}
