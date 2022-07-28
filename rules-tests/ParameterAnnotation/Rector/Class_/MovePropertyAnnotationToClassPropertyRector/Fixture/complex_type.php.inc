<?php

namespace Rector\Tests\ParameterAnnotation\Rector\Class_\MovePropertyAnnotationToClassPropertyRector\Fixture;

namespace Test;


use Fpg\ForumBundle\Model\ForumManager;
use Fpg\ModelBundle\Validation\RequestValidator;

/**
 * User: tarjei
 * Date: 28.07.2022 / 09:55
 * @property RequestValidator validator
 * @property \PHPUnit\Framework\MockObject\MockObject commentManager
 * @property \Fpg\NotificationsBundle\Model\Mailgun\Creators\MessageCreator creator
 * @property mixed inboxManager
 * @property ForumManager|\PHPUnit\Framework\MockObject\MockObject forumManager
 * @property mixed $existingProperty;

 * @property int simple
 */
class MinimalClass
{

    public $bar;
    private $existingProperty;

    public function __construct()
    {
    }

}

?>
-----
<?php

namespace Rector\Tests\ParameterAnnotation\Rector\Class_\MovePropertyAnnotationToClassPropertyRector\Fixture;

namespace Test;


use Fpg\ForumBundle\Model\ForumManager;
use Fpg\ModelBundle\Validation\RequestValidator;

/**
 * User: tarjei
 * Date: 28.07.2022 / 09:55
 */
class MinimalClass
{

    private \Fpg\ModelBundle\Validation\RequestValidator $validator;
    private \PHPUnit\Framework\MockObject\MockObject $commentManager;
    private \Fpg\NotificationsBundle\Model\Mailgun\Creators\MessageCreator $creator;
    private $inboxManager;
    /**
     * @var ForumManager|\PHPUnit\Framework\MockObject\MockObject forumManager
     */
    private $forumManager;
    private int $simple;
    public $bar;
    private $existingProperty;

    public function __construct()
    {
    }

}

?>
