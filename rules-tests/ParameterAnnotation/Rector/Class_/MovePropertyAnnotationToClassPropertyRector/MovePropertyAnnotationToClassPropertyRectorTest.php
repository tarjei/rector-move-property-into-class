<?php

declare(strict_types=1);

namespace Rector\Tests\ParameterAnnotation\Rector\Class_\MovePropertyAnnotationToClassPropertyRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use RectorPrefix202207\Symplify\SmartFileSystem\SmartFileInfo;

final class MovePropertyAnnotationToClassPropertyRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function testComplexType(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixture/complex_type.php.inc');
        $this->doTestFileInfo($fileInfo);
    }

    public function testSimple(): void
    {
        $fileInfo = new SmartFileInfo(__DIR__ . '/Fixture/some_class.php.inc');
        $this->doTestFileInfo($fileInfo);
    }

    /**
     * @return \Iterator<\Symplify\SmartFileSystem\SmartFileInfo>
     */
    public function provideData(): \Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/configured_rule.php';
    }
}
