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

namespace AndrasOtto\Csp\Tests\Unit\Controller;

use AndrasOtto\Csp\Controller\IframeController;
use AndrasOtto\Csp\Tests\Unit\AbstractUnitTest;

class IframeControllerTest extends AbstractUnitTest
{

    /** @var IframeController  */
    protected $subject;

    /**
     * Setup global
     */
    #[\Override]
    public function setUp(): void
    {
        parent::setUp();
        $this->subject = new IframeController();
    }

    /**
     * @param array $settings
     */
    protected function createMockWithSettings($settings = [])
    {
        $reflectionClass = new \ReflectionClass(IframeController::class);

        $reflectionProperty = $reflectionClass->getProperty('settings');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->subject, $settings);
    }

    /**
     * @test
     */
    public function renderActionExists(): void
    {
        $this->createMockWithSettings();
        $this->subject->renderAction();
    }

    /**
     * @test
     */
    public function returnsCorrectIframeTag(): void
    {
        $this->createMockWithSettings(['iframe' => ['src' => 'https://www.test.com']]);
        $iframeMarkup = $this->subject->renderAction();
        self::assertEquals(
            '<iframe src="https://www.test.com"></iframe>',
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
