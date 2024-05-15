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

use AndrasOtto\Csp\Domain\Model\Iframe;
use AndrasOtto\Csp\Exceptions\InvalidValueException;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class IframeTest extends UnitTestCase
{

    /** @var Iframe */
    protected $iframe;

    /**
     * Setup global$
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function generateIframeWithSrcOnly()
    {
        $this->iframe = new Iframe('http://test.de');
        self::assertEquals('<iframe src="http://test.de"></iframe>', $this->iframe->generateHtmlTag());
    }

    /**
     * @test
     */
    public function missingSrcThrowsException()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Src must be set');
        $this->expectExceptionCode(1505656675);
        new Iframe('');
    }

    /**
     * @test
     */
    public function wrongHostInSrcThrowsException()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Host cannot be extracted from the src value "test.de"');
        $this->expectExceptionCode(1505632671);
        new Iframe('test.de');
    }

    /**
     * @test
     */
    public function classSetCorrectlyIfProvided()
    {
        $this->iframe = new Iframe('http://test.de', 'class');
        self::assertEquals('<iframe src="http://test.de" class="class"></iframe>', $this->iframe->generateHtmlTag());
    }

    /**
     * @test
     */
    public function nameSetCorrectlyIfProvided()
    {
        $this->iframe = new Iframe('http://test.de', '', 'test');
        self::assertEquals('<iframe src="http://test.de" name="test"></iframe>', $this->iframe->generateHtmlTag());
    }

    /**
     * @test
     */
    public function notAllowedSandboxValueThrowsException()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Not allowed value "test" for the attribute sandbox.');
        $this->expectExceptionCode(1505656673);
        new Iframe('http://test.de', '', '', 0, 0, 'test');
    }

    /**
     * @test
     */
    public function oneSanBoxValueSetCorrectlyIfProvided()
    {
        $this->iframe = new Iframe('http://test.de', '', '', 0, 0, 'allow-forms');
        self::assertEquals('<iframe src="http://test.de" sandbox="allow-forms"></iframe>', $this->iframe->generateHtmlTag());
    }

    /**
     * @test
     */
    public function multipleSanBoxValueSetCorrectlyIfProvided()
    {
        $this->iframe = new Iframe(
            'http://test.de',
            '',
            '',
            0,
            0,
            'allow-forms, allow-popups,     allow-scripts'
        );
        self::assertEquals(
            '<iframe src="http://test.de" sandbox="allow-forms allow-popups allow-scripts"></iframe>',
            $this->iframe->generateHtmlTag()
        );
    }

    /**
     * @test
     */
    public function negativeIntegerIgnoredAsWidth()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Width should be a positive integer or zero, "-100" given');
        $this->expectExceptionCode(1505632672);
        new Iframe('http://test.de', '', '', -100);
    }

    /**
     * @test
     */
    public function negativeIntegerIgnoredAsHeight()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Height should be a positive integer or zero, "-100" given');
        $this->expectExceptionCode(1505632672);
        new Iframe('http://test.de', '', '', 0, -100);
    }

    /**
     * @test
     */
    public function notIntegerIgnoredAsWidth()
    {
        $this->iframe = new Iframe('http://test.de', '', '', 'hundred');
        self::assertEquals('<iframe src="http://test.de"></iframe>', $this->iframe->generateHtmlTag());
    }

    /**
     * @test
     */
    public function notIntegerIgnoredAsHeight()
    {
        $this->iframe = new Iframe('http://test.de', '', '', 0, 'hundred');
        self::assertEquals('<iframe src="http://test.de"></iframe>', $this->iframe->generateHtmlTag());
    }

    /**
     * @test
     */
    public function correctIntegerAcceptedAsWidth()
    {
        $this->iframe = new Iframe('http://test.de', '', '', '150');
        self::assertEquals('<iframe src="http://test.de" width="150"></iframe>', $this->iframe->generateHtmlTag());
    }

    /**
     * @test
     */
    public function correctIntegerAcceptedAsHeight()
    {
        $this->iframe = new Iframe('http://test.de', '', '', 0, '111');
        self::assertEquals('<iframe src="http://test.de" height="111"></iframe>', $this->iframe->generateHtmlTag());
    }

    /**
     * @test
     */
    public function allowPaymentRequestCanSetCorrectly()
    {
        $this->iframe = new Iframe('http://test.de', '', '', 0, 0, '', true);
        self::assertEquals(
            '<iframe src="http://test.de" allowpaymentrequest="allowpaymentrequest"></iframe>',
            $this->iframe->generateHtmlTag()
        );
    }

    /**
     * @test
     */
    public function allowFullScreenCanSetCorrectly()
    {
        $this->iframe = new Iframe('http://test.de', '', '', 0, 0, '', 0, true);
        self::assertEquals(
            '<iframe src="http://test.de" allowfullscreen="allowfullscreen"></iframe>',
            $this->iframe->generateHtmlTag()
        );
    }

    /**
     * @test
     */
    public function allowPaymentRequestCanBeSet()
    {
        $this->iframe = new Iframe('http://test.de');
        $this->iframe->setAllowPaymentRequest('1');
        self::assertTrue($this->iframe->isAllowPaymentRequest());
    }

    /**
     * @test
     */
    public function allowFullScreenCanBeSet()
    {
        $this->iframe = new Iframe('http://test.de');
        $this->iframe->setAllowFullScreen(true);
        self::assertTrue($this->iframe->isAllowFullScreen());
    }

    /**
     * @test
     */
    public function srcCanBeChanged()
    {
        $this->iframe = new Iframe('https://www.test.de');
        $this->iframe->setSrc('http://www.test.de');
        self::assertEquals('http://www.test.de', $this->iframe->getSrc());
    }

    /**
     * @test
     */
    public function srcCannotBeChangedToAnInvalidValue()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Host cannot be extracted from the src value "test"');
        $this->expectExceptionCode(1505632671);
        $this->iframe = new Iframe('http://test.de');
        $this->iframe->setSrc('test');
    }

    /**
     * @test
     */
    public function sandboxCanBeChanged()
    {
        $this->iframe = new Iframe('https://www.test.de');
        $this->iframe->setSandbox('allow-popups');
        self::assertEquals(1, count($this->iframe->getSandbox()));
    }

    /**
     * @test
     */
    public function sandboxCannotChangedToInvalidValues()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Not allowed value "test" for the attribute sandbox.');
        $this->expectExceptionCode(1505656673);
        $this->iframe = new Iframe('http://test.de');
        $this->iframe->setSandbox('allow-popups,test');
    }

    /**
     * @test
     */
    public function heightCanBeChanged()
    {
        $this->iframe = new Iframe('https://www.test.de');
        $this->iframe->setHeight(11);
        self::assertEquals(11, $this->iframe->getHeight());
    }

    /**
     * @test
     */
    public function heightCannotBeChangedToAnInvalidValue()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Height should be a positive integer or zero, "-11" given');
        $this->expectExceptionCode(1505632672);
        $this->iframe = new Iframe('https://www.test.de');
        $this->iframe->setHeight(-11);
    }

    /**
     * @test
     */
    public function widthCanBeChanged()
    {
        $this->iframe = new Iframe('https://www.test.de');
        $this->iframe->setWidth(11);
        self::assertEquals(11, $this->iframe->getWidth());
    }

    /**
     * @test
     */
    public function widthCannotBeChangedToAnInvalidValue()
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Width should be a positive integer or zero, "-13" given');
        $this->expectExceptionCode(1505632672);
        $this->iframe = new Iframe('https://www.test.de');
        $this->iframe->setWidth(-13);
    }

    /**
     * @test
     */
    public function classCanBeChanged()
    {
        $this->iframe = new Iframe('https://www.test.de', 'test1');
        $this->iframe->setClass('test2');
        self::assertEquals('test2', $this->iframe->getClass());
    }

    /**
     * @test
     */
    public function nameCanBeChanged()
    {
        $this->iframe = new Iframe('https://www.test.de', '', 'test1');
        $this->iframe->setName('test2');
        self::assertEquals('test2', $this->iframe->getName());
    }

    /**
     * @test
     */
    public function dataAttributesCanBeAdded()
    {
        $this->iframe = new Iframe('http://test.de', '', '', 0, 0, '', 0, false, 'test1: 1; data-test2: 2');
        self::assertEquals(
            '<iframe src="http://test.de" data-test1="1" data-test2="2"></iframe>',
            $this->iframe->generateHtmlTag()
        );
    }

    /**
     * @test
     */
    public function dataAttributesCanBeChanged()
    {
        $this->iframe = new Iframe('http://test.de', '', '', 0, 0, '', 0, false, '');
        $this->iframe->setDataAttributes('data-test2: 2');
        self::assertEquals(
            '1',
            count($this->iframe->getDataAttributes())
        );
    }

    /**
     * @test
     */
    public function dataAttributesCanNotBeChangedToInvalidValue()
    {
        $this->iframe = new Iframe('http://test.de', '', '', 0, 0, '', 0, false, '');
        $this->iframe->setDataAttributes('<dat>a-t>><st2: 2');
        self::assertEquals([], $this->iframe->getDataAttributes());
    }

    /**
     * @test
     */
    public function parsingSrcFromHTMLWorks()
    {
        $html = '<iframe classs="test" src="https://player.test.video/id=?dasdas"></iframe>';
        $this->iframe = Iframe::parseSrcFromHtml($html);

        self::assertEquals('https://player.test.video/id=?dasdas', $this->iframe->getSrc());
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->iframe);
    }
}
