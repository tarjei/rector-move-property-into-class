<?php

namespace Rector\Tests\ParameterAnnotation\Rector\Class_\MovePropertyAnnotationToClassPropertyRector\Fixture;

namespace Test;


/**
 * User: tarjei
 * Date: 28.07.2022 / 09:55
 * @property string $foo
  */
class MinimalClass
{

    public $bar;

    public function __construct()
    {
    }

}

?>
-----
<?php

namespace Rector\Tests\ParameterAnnotation\Rector\Class_\MovePropertyAnnotationToClassPropertyRector\Fixture;

namespace Test;


/**
 * User: tarjei
 * Date: 28.07.2022 / 09:55
 */
class MinimalClass
{

    private string $foo;
    public $bar;

    public function __construct()
    {
    }

}

?>
