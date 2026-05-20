<?php

namespace App\UI\Http\Public;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PrivacyController extends AbstractController
{
    public function __invoke(Request $request): Response
    {
        return $this->render('public/pages/privacy.html.twig');
    }
}
