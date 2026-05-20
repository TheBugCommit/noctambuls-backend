<?php

declare(strict_types=1);

namespace App\Tests\Notification\Domain\Entity;

use App\Notification\Domain\Entity\NotificationTemplate;
use PHPUnit\Framework\TestCase;

final class NotificationTemplateTest extends TestCase
{
    public function test_constructor_sets_event_title_and_body(): void
    {
        $template = new NotificationTemplate('census.published', 'My Title', 'My Body');

        self::assertSame('census.published', $template->event());
        self::assertSame('My Title', $template->title());
        self::assertSame('My Body', $template->body());
    }

    public function test_event_getter_returns_event(): void
    {
        $template = new NotificationTemplate('census.cancelled', 'T', 'B');

        self::assertSame('census.cancelled', $template->event());
    }

    public function test_update_changes_title_and_body(): void
    {
        $template = new NotificationTemplate('census.published', 'Old Title', 'Old Body');

        $template->update('New Title', 'New Body');

        self::assertSame('New Title', $template->title());
        self::assertSame('New Body', $template->body());
    }

    public function test_update_does_not_change_event(): void
    {
        $template = new NotificationTemplate('census.date_changed', 'T', 'B');

        $template->update('New Title', 'New Body');

        self::assertSame('census.date_changed', $template->event());
    }
}
