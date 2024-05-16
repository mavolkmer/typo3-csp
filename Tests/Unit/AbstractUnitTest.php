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

namespace AndrasOtto\Csp\Tests\Unit;

use TYPO3\CMS\Core\Cache\Backend\NullBackend;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class AbstractUnitTest extends UnitTestCase
{

    /**
     * Setup global
     */
    #[\Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->turnOffCaches();
    }

    /**
     * Sets the NullBackend Cache class to some caches to turn them off.
     */
    private function turnOffCaches(): void
    {
        /** @var CacheManager $cacheManager */
        $cacheManager = GeneralUtility::makeInstance(CacheManager::class);

        $cacheConfig['extbase_object'] = [
            'frontend' => VariableFrontend::class,
            'backend' => NullBackend::class,
            'options' => [
                'defaultLifetime' => 0,
            ],
            'groups' => ['system']
        ];

        $cacheConfig['cache_hash'] = [
            'frontend' => VariableFrontend::class,
            'backend' => NullBackend::class,
            'options' => [
                'defaultLifetime' => 0,
            ],
            'groups' => ['system']
        ];

        $cacheManager->setCacheConfigurations($cacheConfig);
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected function setUpFakeTsfe($enabled = 0)
    {
        $tsfe = new TypoScriptFrontendController([], 0, 0);
        $tsfe->tmpl = new \stdClass();
        $tsfe->config['config']['csp.'] = [
            'enabled' => $enabled
        ];
        $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.typoscript')->getSetupArray()['plugin.']['tx_csp.']['settings.']['presets.'] = [
            'googleAnalytics' => [
                'enabled' => 1,
                'rules.' => [
                    'script' => 'www.google-analytics.com stats.g.doubleclick.net https://stats.g.doubleclick.net',
                    'img' => 'www.google-analytics.com stats.g.doubleclick.net https://stats.g.doubleclick.net'
                ]
            ],
            'vimeo' => [
                'enabled' => 0,
                'rules.' => [
                    'frame' => '*.vimeo.com *.vimeocdn.com'
                ]
            ]
        ];
        $GLOBALS['TSFE'] = $tsfe;
        return $tsfe;
    }

    /**
     * @test
     */
    public function dummyTest()
    {
    }

    #[\Override]
    public function tearDown(): void
    {
        parent::tearDown();
    }
}
