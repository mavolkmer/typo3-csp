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
    #[\Override]
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function generateIframeWithSrcOnly(): void
    {
        $this->iframe = new Iframe('http://test.de');
        self::assertEquals('<iframe src="http://test.de"></iframe>', $this->iframe->generateHtmlTag());
    }

    /**
     * @test
     */
    public function missingSrcThrowsException(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Src must be set');
        $this->expectExceptionCode(1505656675);
        new Iframe('');
    }

    /**
     * @test
     */
    public function wrongHostInSrcThrowsException(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Host cannot be extracted from the src value "test.de"');
        $this->expectExceptionCode(1505632671);
        new Iframe('test.de');
    }

    /**
     * @test
     */
    public function classSetCorrectlyIfProvided(): void
    {
        $this->iframe = new Iframe('http://test.de', 'class');
        self::assertEquals('<iframe src="http://test.de" class="class"></iframe>', $this->iframe->generateHtmlTag());
    }

    /**
     * @test
     */
    public function nameSetCorrectlyIfProvided(): void
    {
        $this->iframe = new Iframe('http://test.de', '', 'test');
        self::assertEquals('<iframe src="http://test.de" name="test"></iframe>', $this->iframe->generateHtmlTag());
    }

    /**
     * @test
     */
    public function notAllowedSandboxValueThrowsException(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Not allowed value "test" for the attribute sandbox.');
        $this->expectExceptionCode(1505656673);
        new Iframe('http://test.de', '', '', 0, 0, 'test');
    }

    /**
     * @test
     */
    public function oneSanBoxValueSetCorrectlyIfProvided(): void
    {
        $this->iframe = new Iframe('http://test.de', '', '', 0, 0, 'allow-forms');
        self::assertEquals('<iframe src="http://test.de" sandbox="allow-forms"></iframe>', $this->iframe->generateHtmlTag());
    }

    /**
     * @test
     */
    public function multipleSanBoxValueSetCorrectlyIfProvided(): void
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
    public function negativeIntegerIgnoredAsWidth(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Width should be a positive integer or zero, "-100" given');
        $this->expectExceptionCode(1505632672);
        new Iframe('http://test.de', '', '', -100);
    }

    /**
     * @test
     */
    public function negativeIntegerIgnoredAsHeight(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Height should be a positive integer or zero, "-100" given');
        $this->expectExceptionCode(1505632672);
        new Iframe('http://test.de', '', '', 0, -100);
    }

    /**
     * @test
     */
    public function notIntegerIgnoredAsWidth(): void
    {
        $this->iframe = new Iframe('http://test.de', '', '', 'hundred');
        self::assertEquals('<iframe src="http://test.de"></iframe>', $this->iframe->generateHtmlTag());
    }

    /**
     * @test
     */
    public function notIntegerIgnoredAsHeight(): void
    {
        $this->iframe = new Iframe('http://test.de', '', '', 0, 'hundred');
        self::assertEquals('<iframe src="http://test.de"></iframe>', $this->iframe->generateHtmlTag());
    }

    /**
     * @test
     */
    public function correctIntegerAcceptedAsWidth(): void
    {
        $this->iframe = new Iframe('http://test.de', '', '', '150');
        self::assertEquals('<iframe src="http://test.de" width="150"></iframe>', $this->iframe->generateHtmlTag());
    }

    /**
     * @test
     */
    public function correctIntegerAcceptedAsHeight(): void
    {
        $this->iframe = new Iframe('http://test.de', '', '', 0, '111');
        self::assertEquals('<iframe src="http://test.de" height="111"></iframe>', $this->iframe->generateHtmlTag());
    }

    /**
     * @test
     */
    public function allowPaymentRequestCanSetCorrectly(): void
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
    public function allowFullScreenCanSetCorrectly(): void
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
    public function allowPaymentRequestCanBeSet(): void
    {
        $this->iframe = new Iframe('http://test.de');
        $this->iframe->setAllowPaymentRequest('1');
        self::assertTrue($this->iframe->isAllowPaymentRequest());
    }

    /**
     * @test
     */
    public function allowFullScreenCanBeSet(): void
    {
        $this->iframe = new Iframe('http://test.de');
        $this->iframe->setAllowFullScreen(true);
        self::assertTrue($this->iframe->isAllowFullScreen());
    }

    /**
     * @test
     */
    public function srcCanBeChanged(): void
    {
        $this->iframe = new Iframe('https://www.test.de');
        $this->iframe->setSrc('http://www.test.de');
        self::assertEquals('http://www.test.de', $this->iframe->getSrc());
    }

    /**
     * @test
     */
    public function srcCannotBeChangedToAnInvalidValue(): void
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
    public function sandboxCanBeChanged(): void
    {
        $this->iframe = new Iframe('https://www.test.de');
        $this->iframe->setSandbox('allow-popups');
        self::assertEquals(1, count($this->iframe->getSandbox()));
    }

    /**
     * @test
     */
    public function sandboxCannotChangedToInvalidValues(): void
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
    public function heightCanBeChanged(): void
    {
        $this->iframe = new Iframe('https://www.test.de');
        $this->iframe->setHeight(11);
        self::assertEquals(11, $this->iframe->getHeight());
    }

    /**
     * @test
     */
    public function heightCannotBeChangedToAnInvalidValue(): void
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
    public function widthCanBeChanged(): void
    {
        $this->iframe = new Iframe('https://www.test.de');
        $this->iframe->setWidth(11);
        self::assertEquals(11, $this->iframe->getWidth());
    }

    /**
     * @test
     */
    public function widthCannotBeChangedToAnInvalidValue(): void
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
    public function classCanBeChanged(): void
    {
        $this->iframe = new Iframe('https://www.test.de', 'test1');
        $this->iframe->setClass('test2');
        self::assertEquals('test2', $this->iframe->getClass());
    }

    /**
     * @test
     */
    public function nameCanBeChanged(): void
    {
        $this->iframe = new Iframe('https://www.test.de', '', 'test1');
        $this->iframe->setName('test2');
        self::assertEquals('test2', $this->iframe->getName());
    }

    /**
     * @test
     */
    public function dataAttributesCanBeAdded(): void
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
    public function dataAttributesCanBeChanged(): void
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
    public function dataAttributesCanNotBeChangedToInvalidValue(): void
    {
        $this->iframe = new Iframe('http://test.de', '', '', 0, 0, '', 0, false, '');
        $this->iframe->setDataAttributes('<dat>a-t>><st2: 2');
        self::assertEquals([], $this->iframe->getDataAttributes());
    }

    /**
     * @test
     */
    public function parsingSrcFromHTMLWorks(): void
    {
        $html = '<iframe classs="test" src="https://player.test.video/id=?dasdas"></iframe>';
        $this->iframe = Iframe::parseSrcFromHtml($html);

        self::assertEquals('https://player.test.video/id=?dasdas', $this->iframe->getSrc());
    }

    #[\Override]
    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->iframe);
    }
}
