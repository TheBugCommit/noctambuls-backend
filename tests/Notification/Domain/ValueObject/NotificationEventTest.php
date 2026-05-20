<?php

declare(strict_types=1);

namespace App\Tests\Notification\Domain\ValueObject;

use App\Notification\Domain\ValueObject\NotificationEvent;
use PHPUnit\Framework\TestCase;

final class NotificationEventTest extends TestCase
{
    public function test_published_case_has_value_census_published(): void
    {
        self::assertSame('census.published', NotificationEvent::PUBLISHED->value);
    }

    public function test_date_changed_case_has_value_census_date_changed(): void
    {
        self::assertSame('census.date_changed', NotificationEvent::DATE_CHANGED->value);
    }

    public function test_cancelled_case_has_value_census_cancelled(): void
    {
        self::assertSame('census.cancelled', NotificationEvent::CANCELLED->value);
    }

    public function test_can_be_resolved_from_string_using_from(): void
    {
        self::assertSame(NotificationEvent::PUBLISHED, NotificationEvent::from('census.published'));
        self::assertSame(NotificationEvent::DATE_CHANGED, NotificationEvent::from('census.date_changed'));
        self::assertSame(NotificationEvent::CANCELLED, NotificationEvent::from('census.cancelled'));
    }
}
