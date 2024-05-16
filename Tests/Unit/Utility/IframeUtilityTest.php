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

use AndrasOtto\Csp\Utility\IframeUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class IframeUtilityTest extends UnitTestCase
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
    public function generateIframeTagFromConfigArrayMapsPropertiesCorrectly(): void
    {
        $conf = [
            'src' => 'https://test.de',
            'class' => 'test-class multiple',
            'name' => 'conf-test',
            'sandbox' => 'allow-forms, allow-popups',
            'allowFullScreen' => true,
            'allowPaymentRequest' => true,
            'width' => 150,
            'height' => 160
        ];

        $iframeMarkup = IframeUtility::generateIframeTagFromConfigArray($conf);
        self::assertEquals(
            '<iframe src="https://test.de" name="conf-test" class="test-class multiple" width="150" height="160" sandbox="allow-forms allow-popups" allowfullscreen="allowfullscreen" allowpaymentrequest="allowpaymentrequest"></iframe>',
            $iframeMarkup
        );
    }

    #[\Override]
    public function tearDown(): void
    {
        parent::tearDown();
    }
}
