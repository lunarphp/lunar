<?php

declare(strict_types=1);

namespace Lunar\Tests\Upgrade\Unit\Rector\Orders;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RewriteOrderRefundCallRectorTest extends AbstractRectorTestCase
{
    #[DataProvider('provideData')]
    public function test(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    public static function provideData(): Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__.'/Fixture/RewriteOrderRefundCallRector');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__.'/config/order_refund.php';
    }
}
