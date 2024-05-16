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
use AndrasOtto\Csp\Utility\ScriptUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class ScriptUtilityTest extends UnitTestCase
{

    /**
     * Setup global
     */
    #[\Override]
    public function setUp(): void
    {
        ContentSecurityPolicyManager::resetBuilder();
        parent::setUp();
    }

    /**
     * @test
     */
    public function scriptTagCorrectlyAttachedToScriptCode(): void
    {
        $preparedScript = ScriptUtility::getValidScriptTag('   alert("Hello!");    ');
        self::assertEquals('<script>alert("Hello!");</script>', $preparedScript);
    }

    /**
     * @test
     */
    public function hashAddedCorrectly(): void
    {
        ScriptUtility::getValidScriptTag('var foo = "314"');
        $headers = ContentSecurityPolicyManager::extractHeaders();
        self::assertEquals(
            'Content-Security-Policy: script-src \'sha256-gPMJwWBMWDx0Cm7ZygJKZIU2vZpiYvzUQjl5Rh37hKs=\';',
            $headers
        );
    }

    #[\Override]
    public function tearDown(): void
    {
        parent::tearDown();
    }
}
