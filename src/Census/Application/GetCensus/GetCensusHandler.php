<?php

declare(strict_types=1);

namespace App\Census\Application\GetCensus;

use App\Census\Application\Mapper\CensusResponseMapper;
use App\Census\Domain\Exception\CensusNotFoundException;
use App\Census\Domain\Repository\CensusRepositoryInterface;
use App\Census\Domain\Service\CensusLinkGeneratorInterface;
use App\Census\Domain\ValueObject\CensusId;
use App\Shared\Application\Service\QrCodeGeneratorInterface;

readonly class GetCensusHandler
{
    public function __construct(
        private CensusRepositoryInterface $censusRepository,
        private CensusLinkGeneratorInterface $linkGenerator,
        private QrCodeGeneratorInterface $qrGenerator,

    ) {
    }

    public function handle(GetCensusRequest $request): GetCensusResponse
    {
        $census = $this->censusRepository->findById(new CensusId($request->id));

        if ($census === null) {
            throw new CensusNotFoundException();
        }

        $deepLink = '';
        $qrBase64 = '';

        if($census->publishedAt() !== null) {
            $deepLink = $this->linkGenerator->generateFor($census);
            $qrBase64 = $this->qrGenerator->generateBase64($deepLink);
        }

        return CensusResponseMapper::toGetCensusResponse($census, $deepLink, $qrBase64);
    }
}
