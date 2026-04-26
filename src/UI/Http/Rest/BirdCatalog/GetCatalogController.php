<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\BirdCatalog;

use App\BirdCatalog\Application\GetCatalog\GetCatalogHandler;
use App\BirdCatalog\Application\GetCatalog\GetCatalogResponse;

final readonly class GetCatalogController
{
    public function __construct(
        private GetCatalogHandler $handler,
    ) {
    }

    public function __invoke(): GetCatalogResponse
    {
        return $this->handler->handle();
    }
}
