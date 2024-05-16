<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace AndrasOtto\Csp\Domain\Model;

use AndrasOtto\Csp\Exceptions\InvalidValueException;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class DataAttribute extends AbstractEntity
{
    public const NAME_PREFIX = 'data-';

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $value = '';

    /**
     * DataAttribute constructor.
     * @param string $name
     * @param string $value
     */
    public function __construct($name, $value = '')
    {
        $this->ensureName($name);
        $this->ensureValue($value);
    }

    /**
     * Checks and set the name if it is a valid html5 data attribute name is.
     *
     * @param string $name
     * @throws InvalidValueException
     */
    protected function ensureName($name)
    {
        //No capital letters are allowed.
        $name = strtolower($name);

        //Trims the given name, whitespaces are not allowed
        $name = trim($name);

        if ($name
            && $this->isValidXmlName($name)
            && $this->isNotStartWithXML($name)
            && $this->isNotContaionSemicolon($name)
        ) {
            $this->name = htmlspecialchars($name);
        } elseif ($name) {
            throw new InvalidValueException(
                sprintf(
                    'Name should be a valid xml name, must not start with "xml" and semicolons are not allowed, "%s" given',
                    $name
                ),
                15057512312
            );
        }
    }

    /**
     * Data attributes names are not allowed to start with xml
     * source: https://developer.mozilla.org/en-US/docs/Web/HTML/Global_attributes/data-*
     *
     * @param $name
     * @return bool
     */
    private function isNotStartWithXML($name)
    {
        return !str_starts_with((string) $name, 'xml');
    }

    /**
     * Attribute names must ot contain semicolons.
     * source: https://developer.mozilla.org/en-US/docs/Web/HTML/Global_attributes/data-*
     *
     * @param $name
     * @return bool
     */
    private function isNotContaionSemicolon($name)
    {
        return !preg_match('/;/', (string) $name);
    }

    /**
     * Checks if the name is a valid xml name
     * source: https://developer.mozilla.org/en-US/docs/Web/HTML/Global_attributes/data-*
     *
     * @param $name
     * @return bool
     */
    private function isValidXmlName($name)
    {
        try {
            new \DOMElement(":$name");
            return true;
        } catch (\DOMException) {
            return false;
        }
    }

    /**
     * Sets the value if it does not contain semicolon
     *
     * @param string $value
     * @throws InvalidValueException
     */
    protected function ensureValue($value)
    {
        if ($value) {
            $this->value = trim(htmlspecialchars($value));
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        if (!str_starts_with($this->name, 'data-')) {
            return self::NAME_PREFIX . $this->name;
        }
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->ensureName($name);
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->ensureValue($value);
    }

    /**
     * Generate a single attribute from a single definition
     *
     * Valid formats
     * attr1: value1, value2
     * attr1: value1,
     * attr1
     *
     * @param string $attributeDefinition
     * @return DataAttribute
     * @throws InvalidValueException
     */
    public static function generateAttributeFromString($attributeDefinition)
    {
        $definitionParts = preg_split('/:/', $attributeDefinition, 2);

        $partCount = count($definitionParts);

        if ($partCount > 0 && $partCount <= 2) {
            if (trim($definitionParts[0])) {
                if (isset($definitionParts[1])) {
                    return new DataAttribute($definitionParts[0], $definitionParts[1]);
                }
                return new DataAttribute($definitionParts[0]);
            }
        }
        //name is empty
        return null;
    }

    /**
     * Generates DataAttribute from a string definition
     *
     * Format:
     *
     * attr1: value1, value2; attr2: value3; attr3
     *
     * @param string $definition
     * @return array
     */
    public static function generateAttributesFromString($definition)
    {
        $attributes = [];
        $attributeDefinitions = preg_split('/;/', $definition);
        foreach ($attributeDefinitions as $attributeDefinition) {
            if ($attribute = self::generateAttributeFromString($attributeDefinition)) {
                $attributes[] = $attribute;
            }
        }
        return $attributes;
    }
}
