<?php

declare(strict_types=1);

namespace Lunar\Tests\Upgrade\Unit\Rector\SellingPolicy;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RenameSellingPolicyRectorTest extends AbstractRectorTestCase
{
    #[DataProvider('provideData')]
    public function test(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    public static function provideData(): Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__.'/Fixture/RenameSellingPolicy');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__.'/config/rename_selling_policy.php';
    }
}
