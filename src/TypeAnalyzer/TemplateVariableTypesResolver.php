<?php

declare(strict_types=1);

namespace Symplify\TemplatePHPStanCompiler\TypeAnalyzer;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\Generic\GenericObjectType;
use Symplify\TemplatePHPStanCompiler\ValueObject\VariableAndType;

final class TemplateVariableTypesResolver
{
    /**
     * @return VariableAndType[]
     */
    public function resolveArray(Array_ $array, Scope $scope): array
    {
        $variableNamesToTypes = [];

        foreach ($array->items as $arrayItem) {
            if (! $arrayItem instanceof ArrayItem) {
                continue;
            }

            if ($arrayItem->key === null) {
                continue;
            }

            $arrayItemValue = $scope->getType($arrayItem->key);
            if (! $arrayItemValue instanceof ConstantStringType) {
                continue;
            }

            $keyName = $arrayItemValue->getValue();
            $variableType = $scope->getType($arrayItem->value);

            // unwrap generic object type
            if ($variableType instanceof GenericObjectType && isset($variableType->getTypes()[1])) {
                $variableType = new ArrayType($variableType->getTypes()[0], $variableType->getTypes()[1]);
            }

            $variableNamesToTypes[] = new VariableAndType($keyName, $variableType);
        }

        return $variableNamesToTypes;
    }
}
