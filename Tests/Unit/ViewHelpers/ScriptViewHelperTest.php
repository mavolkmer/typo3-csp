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

use AndrasOtto\Csp\Service\ContentSecurityPolicyManager;
use AndrasOtto\Csp\ViewHelpers\ScriptViewHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class ScriptViewHelperTest extends UnitTestCase
{

    /** @var ScriptViewHelper  */
    protected $subject;
    /**
     * Setup global
     */
    #[\Override]
    public function setUp(): void
    {
        parent::setUp();
        $this->subject = GeneralUtility::makeInstance(ScriptViewHelper::class);
        $renderingContext = GeneralUtility::makeInstance(RenderingContext::class);
        $this->subject->setRenderingContext($renderingContext);
        ContentSecurityPolicyManager::resetBuilder();
    }

    /**
     * @test
     */
    public function rendersEmptyScriptTag(): void
    {
        $closure = fn() => '';

        $this->subject->setRenderChildrenClosure($closure);

        $scriptMarkup = $this->subject->render();

        self::assertEquals(
            '',
            $scriptMarkup
        );
    }

    /**
     * @test
     */
    public function rendersScriptTagCorrectly(): void
    {
        $closure = fn() => '
                alert(\'test\');
            ';

        $this->subject->setRenderChildrenClosure($closure);

        $scriptMarkup = $this->subject->render();

        self::assertEquals(
            "<script>alert('test');</script>",
            $scriptMarkup
        );
    }

    #[\Override]
    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->subject);
    }
}
