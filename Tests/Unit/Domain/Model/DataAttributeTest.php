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

namespace AndrasOtto\Csp\Tests\Unit\Model;

use AndrasOtto\Csp\Domain\Model\DataAttribute;
use AndrasOtto\Csp\Exceptions\InvalidValueException;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class DataAttributeTest extends UnitTestCase
{

    /**
     * Setup global
     */
    #[\Override]
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function createsValidDataAttribute(): void
    {
        new DataAttribute('test', 'test');
    }

    /**
     * @test
     */
    public function semicolonsAreNotAllowedInName(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Name should be a valid xml name, must not start with "xml" and semicolons are not allowed, "a;b" given');
        $this->expectExceptionCode(15057512312);

        new DataAttribute('a;b', 'test');
    }

    /**
     * @test
     */
    public function xmlIsNotAllowedAtTheBeginning(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Name should be a valid xml name, must not start with "xml" and semicolons are not allowed, "xml-test" given');
        $this->expectExceptionCode(15057512312);

        new DataAttribute('xml-test', 'test');
    }

    /**
     * @test
     */
    public function xmlAllowedIfNotAtTheBeginning(): void
    {
        new DataAttribute('test-xml', 'test');
    }

    /**
     * @test
     */
    public function nameShouldBeValidXmlName(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Name should be a valid xml name, must not start with "xml" and semicolons are not allowed, "a<b>c" given');
        $this->expectExceptionCode(15057512312);

        new DataAttribute('a<b>c', 'test');
    }

    /**
     * @test
     */
    public function capitalLettersInNameAreIgnored(): void
    {
        $dataAttribute = new DataAttribute('test', 'test');
        self::assertEquals(
            'data-test',
            $dataAttribute->getName()
        );
    }

    /**
     * @test
     */
    public function nameEnsuredBySet(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Name should be a valid xml name, must not start with "xml" and semicolons are not allowed, "a<b>c" given');
        $this->expectExceptionCode(15057512312);
        $dataAttribute = new DataAttribute('test', 'test');
        $dataAttribute->setName('a<b>c');
    }

    /**
     * @test
     */
    public function nameCanChangedAfterCreatingTheObject(): void
    {
        $dataAttribute = new DataAttribute('test1', 'test');
        $dataAttribute->setName('test2');
        self::assertEquals(
            'data-test2',
            $dataAttribute->getName()
        );
    }

    /**
     * @test
     */
    public function whitespaceCharactersAreNotAllowedInName(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage("Name should be a valid xml name, must not start with \"xml\" and semicolons are not allowed, \"test \t\n test\" given");
        $this->expectExceptionCode(15057512312);
        new DataAttribute("test \t\n test", 'test');
    }
    /**
     * @test
     */
    public function dataPrefixAddedOnlyIfNotAlreadyPrefixed(): void
    {
        $dataAttribute = new DataAttribute('data-test', 'test');
        self::assertEquals(
            'data-test',
            $dataAttribute->getName()
        );
    }

    /**
     * @test
     */
    public function valueHtmlCharactersAreEscapedInConstructor(): void
    {
        $dataAttribute = new DataAttribute('data-test', '/><script>alert(\'ok\');</script>');
        self::assertEquals(
            '/&gt;&lt;script&gt;alert(\'ok\');&lt;/script&gt;',
            $dataAttribute->getValue()
        );
    }

    /**
     * @test
     */
    public function valueHtmlCharactersAreEscapedBySet(): void
    {
        $dataAttribute = new DataAttribute('data-test');
        $dataAttribute->setValue('/><script>alert(\'ok\');</script>');
        self::assertEquals(
            '/&gt;&lt;script&gt;alert(\'ok\');&lt;/script&gt;',
            $dataAttribute->getValue()
        );
    }

    /**
     * @test
     */
    public function generateDataAttributeFromDefinitionCanGenerateValidAttribute(): void
    {
        $dataAttribute = DataAttribute::generateAttributeFromString('attr1: value1');
        self::assertEquals(
            'data-attr1',
            $dataAttribute->getName()
        );
        self::assertEquals(
            'value1',
            $dataAttribute->getValue()
        );
    }

    /**
     * @test
     */
    public function generateDataAttributeFromDefinitionForEmptyReturnsNull(): void
    {
        $dataAttribute = DataAttribute::generateAttributeFromString('');
        self::assertNull($dataAttribute);
    }

    /**
     * @test
     */
    public function acceptSecondSeparatorAsValidValue(): void
    {
        $dataAttribute = DataAttribute::generateAttributeFromString('attr1: value1:value2');

        self::assertEquals(
            'value1:value2',
            $dataAttribute->getValue()
        );
    }

    /**
     * @test
     */
    public function generateDataAttributesFromDefinitionCanGenerateValidAttributes(): void
    {
        $dataAttributes = DataAttribute::generateAttributesFromString('attr1: value1; attr2');
        self::assertEquals(
            2,
            count($dataAttributes)
        );
    }

    /**
     * @test
     */
    public function generateDataAttributesFromDefinitionIgnoresEmptyNames(): void
    {
        $dataAttributes = DataAttribute::generateAttributesFromString('attr1: value1;;  ;      ; data-attr2 ');
        self::assertEquals(
            2,
            count($dataAttributes)
        );
        /** @var DataAttribute $dataAttribute */
        $dataAttribute = $dataAttributes[1];

        self::assertEquals('data-attr2', $dataAttribute->getName());
    }

    #[\Override]
    public function tearDown(): void
    {
        parent::tearDown();
    }
}
