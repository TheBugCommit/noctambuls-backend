<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\BirdCatalog;

use App\BirdCatalog\Application\GetCatalogVersion\GetCatalogVersionHandler;
use App\BirdCatalog\Application\GetCatalogVersion\GetCatalogVersionResponse;

final readonly class GetCatalogVersionController
{
    public function __construct(
        private GetCatalogVersionHandler $handler,
    ) {
    }

    public function __invoke(): GetCatalogVersionResponse
    {
        return $this->handler->handle();
    }
}
