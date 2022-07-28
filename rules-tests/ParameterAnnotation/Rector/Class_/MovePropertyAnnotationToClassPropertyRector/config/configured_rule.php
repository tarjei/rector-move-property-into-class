<?php

declare(strict_types=1);

namespace RectorPrefix202207;

use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(\Rector\ParameterAnnotation\Rector\Class_\MovePropertyAnnotationToClassPropertyRector::class);
};
