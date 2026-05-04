<?php

declare(strict_types=1);

namespace App\UI\Http\Backoffice\UserManagement;

use App\Auth\Application\ListAppUsers\ListAppUsersHandler;
use App\Auth\Application\ListAppUsers\ListAppUsersRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class AppUserListController extends AbstractController
{
    private const int PAGE_SIZE = 20;

    public function __construct(private readonly ListAppUsersHandler $handler)
    {
    }

    public function __invoke(Request $request): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));

        $response = $this->handler->handle(new ListAppUsersRequest($page, self::PAGE_SIZE));

        return $this->render('backoffice/user_management/app_users.html.twig', [
            'users'      => $response->users,
            'total'      => $response->total,
            'page'       => $response->page,
            'limit'      => $response->limit,
            'totalPages' => (int) ceil($response->total / $response->limit),
        ]);
    }
}
