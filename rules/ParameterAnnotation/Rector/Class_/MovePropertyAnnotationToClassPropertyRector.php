<?php

declare(strict_types=1);

namespace Rector\ParameterAnnotation\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PHPStan\PhpDocParser\Ast\PhpDoc\InvalidTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\Type\ArrayType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\UseImportsResolver;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\ParameterAnnotation\Rector\Class_\MovePropertyAnnotationToClassPropertyRector\MovePropertyAnnotationToClassPropertyRectorTest
 */
final class MovePropertyAnnotationToClassPropertyRector extends AbstractRector
{
    /**
     * @readonly
     */
    private PHPStanStaticTypeMapper $phpStanStaticTypeMapper;

    /**
     * @readonly
     *
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;

    private UseImportsResolver $useImportsResolver;

    private $imports = [];

    public function __construct(PhpDocTagRemover $phpDocTagRemover, PHPStanStaticTypeMapper $phpStanStaticTypeMapper, UseImportsResolver $useImportsResolver)
    {
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->phpStanStaticTypeMapper = $phpStanStaticTypeMapper;
        $this->useImportsResolver = $useImportsResolver;
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [\PhpParser\Node\Stmt\Class_::class];
    }

    /**
     * @param \PhpParser\Node\Stmt\Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ((new ClassAnalyzer($this->nodeNameResolver))->isAnonymousClass($node)) {
            return null;
        }

        $uses = $this->useImportsResolver->resolveForNode($node);
        foreach ($uses as $use) {
            if (Stmt\Use_::TYPE_NORMAL === $use->type) {
                foreach ($use->uses as $useuse) {
                    $this->imports[$useuse->getAlias()->name] = $useuse->name->toString();
                }
            } else {
                printf('Skipping import: %s', $use->type);
            }
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $annotationToRemove = 'property';

        if (!$phpDocInfo->hasByName($annotationToRemove)) {
            return $node;
        }

        $propertyTags = $phpDocInfo->getTagsByName($annotationToRemove);
        $key = 0;
        /* @var PhpDocTagNode $value */
        foreach ($propertyTags as $phpDocTagNode) {
            $val = $phpDocTagNode->value;
            $docBlockType = null;

            if ($val instanceof InvalidTagValueNode) {
                //echo 'INVALID: ' . $val->value . "\n";
                $parts = preg_split('/ /', $val->value, -1, \PREG_SPLIT_NO_EMPTY);
                if (2 !== \count($parts)) {
                    throw new \Exception('Invalid parts: ' . json_encode($parts));
                }
                [$typeString, $name] = $parts;

                if (strpos($typeString, '|')) {
                    $typeStrings = preg_split('/\\|/', $typeString, -1, \PREG_SPLIT_NO_EMPTY);
                    $types = [];
                    foreach ($typeStrings as $typeString) {
                        $mappedType = $this->mapToType($typeString);
                        if (null !== $mappedType) {
                            $types[] = $mappedType;
                        }
                    }
                    if (\count($types) > 1) {
                        $docBlockType = new UnionType($types);
                        $type = null;
                    } else {
                        $type = $types[0] ?? null;
                    }
                } else {
                    $type = $this->mapToType($typeString);
                }
            } else {
                $type = $this->mapToType($val->type->name);
                $name = $val->propertyName;
            }

            if (0 === strpos($name, '$')) {
                $name = substr($name, 1);
            }

            $existingProperty = $node->getProperty($name);
            if ($existingProperty) {
                echo "Skipping existing property: $name\n";
                continue;
            }

            $newClassProperty = $this->nodeFactory->createPrivatePropertyFromNameAndType($name, $type);

            if ($docBlockType) {
                $phpDocTagNode = $this->phpDocInfoFactory->createFromNodeOrEmpty($newClassProperty);
                $varType = $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($docBlockType, TypeKind::PROPERTY);
                $varTagValueNode = new VarTagValueNode($varType, $name, '');
                $phpDocTagNode->addTagValueNode($varTagValueNode);
            }

            $node->stmts = $this->insertBefore($node->stmts, $newClassProperty, $key);
            ++$key;
        }
        $this->phpDocTagRemover->removeByName($phpDocInfo, $annotationToRemove);
        $phpDocInfo->removeByType($annotationToRemove);

        if ($phpDocInfo->hasChanged()) {
            return $node;
        }

        return $node;
    }

    /**
     * @param Stmt[] $stmts
     *
     * @return Stmt[]
     */
    private function insertBefore(array $stmts, Stmt $stmt, int $key): array
    {
        array_splice($stmts, $key, 0, [$stmt]);

        return $stmts;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('"move @property to private $property"', [
            new CodeSample(
                <<<'CODE_SAMPLE'
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
CODE_SAMPLE
                , <<<'CODE_SAMPLE'
namespace Test;

/**
 * User: tarjei
 * Date: 28.07.2022 / 09:55
  */
class MinimalClass
{
    private $foo;
    public $bar;

    public function __construct()
    {
    }

}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @param $typeString
     * @param array $types
     * @param $typeStrings
     *
     * @return array
     */
    private function mapToType(string $typeString): ?\PHPStan\Type\Type
    {
        if ('string' == $typeString) {
            return new StringType();
        } elseif ('int' == $typeString || 'integer' == $typeString) {
            return new IntegerType();
        } elseif ('null' == $typeString) {
            return new NullType();
        } elseif ('mixed' == $typeString) {
            return null;
        } elseif ('array' == $typeString) {
            return new ArrayType();
        } elseif ('object' == $typeString) {
            return null;
        } else {
            if (false !== strpos($typeString, '\\')) {
                if (0 === strpos($typeString, '\\')) {
                    $typeString = substr($typeString, 1);
                }
                $objectType = new ObjectType($typeString);
            } else {
                if (!($this->imports[$typeString] ?? false)) {
                    throw new \Exception("Type not in imports: $typeString");
                }
                $objectType = new ShortenedObjectType($typeString, $this->imports[$typeString]);
            }
            echo "Object type from $typeString\n";

            return $objectType;
        }

        throw new \Exception("Unhandled type: $typeString");
    }
}
