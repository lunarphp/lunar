<?php

declare(strict_types=1);

namespace Lunar\Tests\Upgrade\Unit\Rector\Models;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RewriteModelClassCallRectorTest extends AbstractRectorTestCase
{
    #[DataProvider('provideData')]
    public function test(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    public static function provideData(): Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__.'/Fixture/RewriteModelClassCallRector');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__.'/config/model_class.php';
    }
}
