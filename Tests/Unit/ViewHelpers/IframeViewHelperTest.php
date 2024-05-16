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

namespace AndrasOtto\Csp\Tests\Unit\Utility;

use AndrasOtto\Csp\Exceptions\InvalidValueException;
use AndrasOtto\Csp\ViewHelpers\IframeViewHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class IframeViewHelperTest extends UnitTestCase
{

    /** @var IframeViewHelper  */
    protected $subject;
    /**
     * Setup global
     */
    #[\Override]
    public function setUp(): void
    {
        parent::setUp();
        $this->subject = GeneralUtility::makeInstance(IframeViewHelper::class);
        $renderingContext = GeneralUtility::makeInstance(RenderingContext::class);
        $this->subject->setRenderingContext($renderingContext);
    }

    /**
     * @test
     */
    public function throwsExceptionWithEmptySrc(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->subject->render();
    }

    /**
     * @test
     */
    public function rendersIframeTagCorrectly(): void
    {
        $iframeMarkup = $this->subject->render();
        self::assertEquals(
            '<iframe src="https://test.de" name="conf-test" class="test-class multiple" width="150" height="160" sandbox="allow-forms allow-popups" allowfullscreen="allowfullscreen" allowpaymentrequest="allowpaymentrequest"></iframe>',
            $iframeMarkup
        );
    }

    #[\Override]
    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->subject);
    }
}
