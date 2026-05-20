<?php

declare(strict_types=1);

namespace App\Tests\Shared\Infrastructure\Service;

use App\Shared\Domain\ValueObject\ImagePath;
use App\Shared\Infrastructure\Service\LocalFileRemover;
use PHPUnit\Framework\TestCase;

final class LocalFileRemoverTest extends TestCase
{
    private string $tempFile = '';

    protected function tearDown(): void
    {
        if ($this->tempFile !== '' && file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
    }

    public function test_remove_deletes_existing_file(): void
    {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'noctambuls_test_');
        $remover = new LocalFileRemover('');

        $remover->remove(new ImagePath($this->tempFile));

        self::assertFalse(file_exists($this->tempFile));
    }

    public function test_remove_does_nothing_when_file_does_not_exist(): void
    {
        $remover = new LocalFileRemover('');

        $remover->remove(new ImagePath('/nonexistent_noctambuls_path_xyz'));

        $this->expectNotToPerformAssertions();
    }

    public function test_remove_does_nothing_when_path_is_a_directory(): void
    {
        $dir = sys_get_temp_dir();
        $remover = new LocalFileRemover('');

        $remover->remove(new ImagePath($dir));

        self::assertTrue(is_dir($dir));
    }
}
