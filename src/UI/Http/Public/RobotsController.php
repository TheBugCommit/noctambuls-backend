<?php

namespace App\UI\Http\Public;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RobotsController extends AbstractController
{
    public function __invoke(Request $request): Response
    {
        $urls = [];
        $urls[] = $this->generateUrl('public_terms');
        $urls[] = $this->generateUrl('public_privacy');
        $urls[] = $this->generateUrl('public_help');

        $content = "User-agent: *\n";
        foreach ($urls as $url) {
            $content .= "Allow: " . $url . "\n";
        }
        $content .= "Disallow: /\n";

        return new Response($content, Response::HTTP_OK, [
            'Content-Type' => 'text/plain',
        ]);
    }
}
