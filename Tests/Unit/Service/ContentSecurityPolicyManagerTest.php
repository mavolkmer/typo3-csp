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

namespace AndrasOtto\Csp\Tests\Unit\Service;

use AndrasOtto\Csp\Exceptions\InvalidClassException;
use AndrasOtto\Csp\Service\ContentSecurityPolicyHeaderBuilderInterface;
use AndrasOtto\Csp\Service\ContentSecurityPolicyManager;
use AndrasOtto\Csp\Tests\Unit\AbstractUnitTest;
use TYPO3\CMS\Backend\FrontendBackendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class ContentSecurityPolicyManagerTest extends AbstractUnitTest
{

    /**
     * Setup global
     */
    #[\Override]
    public function setUp(): void
    {
        parent::setUp();
        ContentSecurityPolicyManager::resetBuilder();
    }

    /**
     * @return TypoScriptFrontendController
     */
    private function setUpFakeBeUserAuthentication($admPanelActive): void
    {
        $beUser = new FrontendBackendUserAuthentication();

        $beUser->extAdminConfig = ['hide' => false];
        $beUser->extAdmEnabled = true;

        $GLOBALS['TSFE']->config['config']['admPanel'] = $admPanelActive;

        $GLOBALS['BE_USER'] = $beUser;
    }

    /**
     * @test
     */
    public function contentSecurityPolicyBuilderInstanceCreated(): void
    {
        $builder = ContentSecurityPolicyManager::getBuilder();
        self::assertInstanceOf(ContentSecurityPolicyHeaderBuilderInterface::class, $builder);
    }

    /**
     * @test
     */
    public function invalidClassExceptionIfBuilderInterfaceNotImplemented(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['csp']['ContentSecurityPolicyHeaderBuilder'] =
           \AndrasOtto\Csp\Tests\Unit\Service\ContentSecurityPolicyManagerTest::class;

        $this->expectException(InvalidClassException::class);
        $this->expectExceptionMessage('The class "AndrasOtto\\Csp\\Tests\\Unit\\Service\\ContentSecurityPolicyManagerTest" must implement the interface ContentSecurityPolicyHeaderBuilderInterface');
        $this->expectExceptionCode(1505944587);
        ContentSecurityPolicyManager::resetBuilder();
    }

    /**
     * @test
     */
    public function sameBuilderClassUsed(): void
    {
        $builder1 = ContentSecurityPolicyManager::getBuilder();
        $builder2 = ContentSecurityPolicyManager::getBuilder();
        self::assertSame($builder2, $builder1);
    }

    /**
     * @test
     */
    public function resetBuilderCreatesNewBuilder(): void
    {
        $builder1 = ContentSecurityPolicyManager::getBuilder();
        ContentSecurityPolicyManager::resetBuilder();
        $builder2 = ContentSecurityPolicyManager::getBuilder();
        self::assertNotSame($builder2, $builder1);
    }

    /**
     * @test
     */
    public function extractHeadersReturnsEmptyStringByDefault(): void
    {
        ContentSecurityPolicyManager::resetBuilder();
        $headers = ContentSecurityPolicyManager::extractHeaders();

        self::assertSame('', $headers);
    }

    /**
     * @test
     */
    public function addTypoScriptSettingsDoesNothingIfDisabled(): void
    {
        $tsfe = $this->setUpFakeTsfe();

        ContentSecurityPolicyManager::addTypoScriptSettings($tsfe);
        $headers = ContentSecurityPolicyManager::extractHeaders();

        self::assertSame('', $headers);
    }

    /**
     * @test
     */
    public function addTypoScriptSettingsAddsCorrectPresets(): void
    {
        $tsfe = $this->setUpFakeTsfe();
        $this->setUpFakeBeUserAuthentication(false);

        $tsfe->config['config']['csp.']['enabled'] = 1;

        ContentSecurityPolicyManager::addTypoScriptSettings($tsfe);
        $headers = ContentSecurityPolicyManager::extractHeaders();

        self::assertSame(
            'Content-Security-Policy: script-src www.google-analytics.com stats.g.doubleclick.net '
            . 'https://stats.g.doubleclick.net; img-src www.google-analytics.com '
            . 'stats.g.doubleclick.net https://stats.g.doubleclick.net;',
            $headers
        );
    }

    /**
     * @test
     */
    public function addTypoScriptSettingsAddsAdditionalDomains(): void
    {
        $tsfe = $this->setUpFakeTsfe();
        $this->setUpFakeBeUserAuthentication(false);
        $tsfe->config['config']['csp.']['enabled'] = 1;

        $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.typoscript')->getSetupArray()['plugin.']['tx_csp.']['settings.']['additionalSources.'] = [
            'script' => [
                '0' => 'self',
                '10' => 'www.test.de'
            ]
        ];

        ContentSecurityPolicyManager::addTypoScriptSettings($tsfe);
        $headers = ContentSecurityPolicyManager::extractHeaders();

        self::assertSame(
            'Content-Security-Policy: script-src \'self\' www.test.de www.google-analytics.com stats.g.doubleclick.net '
            . 'https://stats.g.doubleclick.net; img-src www.google-analytics.com '
            . 'stats.g.doubleclick.net https://stats.g.doubleclick.net;',
            $headers
        );
    }
    /**
    * @test
    */
    public function reportOnlyModeGeneratesDefaultUriIfReportUriNotSet(): void
    {
        $tsfe = $this->setUpFakeTsfe();
        $this->setUpFakeBeUserAuthentication(false);
        $tsfe->config['config']['csp.']['enabled'] = 1;

        $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.typoscript')->getSetupArray()['plugin.']['tx_csp.']['settings.']['additionalSources.'] = [
            'script' => [
                '0' => 'self',
                '10' => 'www.test.de'
            ]
        ];

        $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.typoscript')->getSetupArray()['plugin.']['tx_csp.']['settings.']['reportOnly'] = 1;

        ContentSecurityPolicyManager::addTypoScriptSettings($tsfe);
        $headers = ContentSecurityPolicyManager::extractHeaders();

        self::assertSame(
            'Content-Security-Policy-Report-Only: script-src \'self\' www.test.de www.google-analytics.com stats.g.doubleclick.net '
            . 'https://stats.g.doubleclick.net; img-src www.google-analytics.com '
            . 'stats.g.doubleclick.net https://stats.g.doubleclick.net; '
            . 'report-uri /typo3conf/ext/csp/Resources/Public/report.php;',
            $headers
        );
    }

    /**
     * @test
     */
    public function correctUriRegisteredIfReportUriSet(): void
    {
        $tsfe = $this->setUpFakeTsfe();
        $this->setUpFakeBeUserAuthentication(false);
        $tsfe->config['config']['csp.']['enabled'] = 1;

        $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.typoscript')->getSetupArray()['plugin.']['tx_csp.']['settings.']['report-uri'] = '/test/';

        ContentSecurityPolicyManager::addTypoScriptSettings($tsfe);
        $headers = ContentSecurityPolicyManager::extractHeaders();

        self::assertSame(
            'Content-Security-Policy: script-src www.google-analytics.com stats.g.doubleclick.net '
            . 'https://stats.g.doubleclick.net; img-src www.google-analytics.com '
            . 'stats.g.doubleclick.net https://stats.g.doubleclick.net; '
            . 'report-uri /test/;',
            $headers
        );
    }

    #[\Override]
    public function tearDown(): void
    {
        parent::tearDown();
    }
}
