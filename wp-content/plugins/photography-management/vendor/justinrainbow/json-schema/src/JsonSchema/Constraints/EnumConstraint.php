<?php
namespace JsonSchema\Constraints;

use \JsonSchema\Entity\JsonPointer;

/**
 * The EnumConstraint Constraints, validates an element against a given set of possibilities
 *
 * @author Robert Schönthal <seroscho@googlemail.com>
 * @author Bruno Prieto Reis <bruno.p.reis@gmail.com>
 */
class EnumConstraint extends Constraint
{
    /**
     * {@inheritdoc}
     */
    public function check(&$element, $schema = null, $path = null, $i = null)
    {
        // Only validate enum if the attribute exists
        if ($element instanceof UndefinedConstraint && (!isset($schema->required) || !$schema->required)) {
            return;
        }
        $type = gettype($element);

        foreach ($schema->enum as $enum) {
            $enumType = gettype($enum);
            if ($this->factory->getConfig(self::CHECK_MODE_TYPE_CAST) && $type == 'array' && $enumType == 'object') {
                if ((object) $element == $enum) {
                    return;
                }
            }

            if ($type === gettype($enum)) {
                if ($type == 'object') {
                    if ($element == $enum) {
                        return;
                    }
                } elseif ($element === $enum) {
                    return;
                }
            }
        }

        $this->addError($path, 'Does not have a value in the enumeration ' . json_encode($schema->enum), 'enum', array('enum' => $schema->enum));
    }
}