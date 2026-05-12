<?php

declare(strict_types=1);

namespace App\Observation\Application\Mapper;

use App\Observation\Domain\ValueObject\ContactType;
use App\Observation\Domain\ValueObject\LocationSource;
use App\Observation\Domain\ValueObject\MoonPresence;
use App\Observation\Domain\ValueObject\RainLevel;
use App\Observation\Domain\ValueObject\SessionType;
use App\Observation\Domain\ValueObject\SkyCondition;
use App\Observation\Domain\ValueObject\TemperatureRange;
use App\Observation\Domain\ValueObject\VisibilityLevel;
use App\Observation\Domain\ValueObject\WindLevel;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CatalanLabelMapper
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function sessionType(SessionType $v): string
    {
        return $this->translator->trans(match ($v) {
            SessionType::Incidental => 'session.type.incidental',
            SessionType::Census     => 'session.type.census',
            SessionType::Station    => 'session.type.station',
        });
    }

    public function contactType(ContactType $v): string
    {
        return $this->translator->trans(match ($v) {
            ContactType::Visual    => 'enum.contact_type.visual',
            ContactType::Auditive  => 'enum.contact_type.auditive',
        });
    }

    public function temperatureRange(TemperatureRange $v): string
    {
        return $this->translator->trans(match ($v) {
            TemperatureRange::BelowZero      => 'enum.temperature.below_zero',
            TemperatureRange::ZeroToTen      => 'enum.temperature.zero_to_ten',
            TemperatureRange::TenToTwenty    => 'enum.temperature.ten_to_twenty',
            TemperatureRange::TwentyToThirty => 'enum.temperature.twenty_to_thirty',
            TemperatureRange::AboveThirty    => 'enum.temperature.above_thirty',
        });
    }

    public function windLevel(WindLevel $v): string
    {
        return $this->translator->trans(match ($v) {
            WindLevel::Calm     => 'enum.wind.calm',
            WindLevel::Breeze   => 'enum.wind.breeze',
            WindLevel::Moderate => 'enum.wind.moderate',
        });
    }

    public function skyCondition(SkyCondition $v): string
    {
        return $this->translator->trans(match ($v) {
            SkyCondition::Clear         => 'enum.sky.clear',
            SkyCondition::PartialCloudy => 'enum.sky.partial_cloudy',
            SkyCondition::Overcast      => 'enum.sky.overcast',
        });
    }

    public function rainLevel(RainLevel $v): string
    {
        return $this->translator->trans(match ($v) {
            RainLevel::None    => 'enum.rain.none',
            RainLevel::Drizzle => 'enum.rain.drizzle',
            RainLevel::Storm   => 'enum.rain.storm',
        });
    }

    public function visibilityLevel(VisibilityLevel $v): string
    {
        return $this->translator->trans(match ($v) {
            VisibilityLevel::Poor     => 'enum.visibility.poor',
            VisibilityLevel::Moderate => 'enum.visibility.moderate',
            VisibilityLevel::Good     => 'enum.visibility.good',
        });
    }

    public function moonPresence(MoonPresence $v): string
    {
        return $this->translator->trans(match ($v) {
            MoonPresence::Absent  => 'enum.moon.absent',
            MoonPresence::Present => 'enum.moon.present',
        });
    }

    public function locationSource(?LocationSource $v): string
    {
        if ($v === null) {
            return '';
        }

        return $this->translator->trans(match ($v) {
            LocationSource::GPS    => 'enum.location_source.gps',
            LocationSource::Manual => 'enum.location_source.manual',
        });
    }
}
