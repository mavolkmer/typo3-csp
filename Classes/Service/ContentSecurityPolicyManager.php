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

namespace AndrasOtto\Csp\Service;

use AndrasOtto\Csp\Constants\Directives;
use AndrasOtto\Csp\Exceptions\InvalidClassException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class ContentSecurityPolicyManager implements SingletonInterface
{
    const DIRECTIVE_POSTFIX = '-src';

    const SCRIPT_MODE_HASH = 0;
    const SCRIPT_MODE_NONCE = 1;

    private static $reportScriptPath = 'Resources/Public/report.php';

    /**
     * Nonce for the actual rendering.
     * It will be generated only once pro request.
     *
     * @var string
     */
    private static $nonce = '';

    /**
     * @var  ContentSecurityPolicyHeaderBuilder
     */
    private static $headerBuilder = null;

    /**
     * Holds the extension configuration
     *
     * @var array
     */
    private static $extensionConfiguration = [];

    /**
     * It is true if the extension config was loaded already
     *
     * @var bool
     */
    private static $extensionConfigurationLoaded = false;

    /**
     * Returns a ContentSecurityPolicyHeaer Builder instance
     *
     * @return ContentSecurityPolicyHeaderBuilder
     */
    public static function getBuilder()
    {
        if (!self::$headerBuilder) {
            self::$headerBuilder = self::createNewBuilderInstance();
        }
        return self::$headerBuilder;
    }

    /**
     * Returns a nonce. It is generated only once by each request.
     *
     * @return string
     */
    public static function getNonce()
    {
        //If nonce is not generated yet, generate it first
        if (!self::$nonce) {
            self::$nonce = base64_encode(random_bytes(32));
        }
        return self::$nonce;
    }

    /**
     * Returns the extensionConfiguration as an array.
     *
     * @return array
     */
    protected static function getExtensionConfiguration()
    {
        if (!self::$extensionConfigurationLoaded) {
            /** @var ObjectManager $objectManager */
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

            /** @var ExtensionConfiguration $configurationUtility */
            $configurationUtility = $objectManager->get(ExtensionConfiguration::class);
            self::$extensionConfiguration = $configurationUtility->get('csp');
            self::$extensionConfigurationLoaded = true;
        }
        return self::$extensionConfiguration;
    }

    /**
     * Is Nonce enabled (true) or the default Hash method should be used (false)
     *
     * @return bool
     */
    public static function isNonceModeEnabled()
    {
        $extConfig = self::getExtensionConfiguration();
        return isset($extConfig['scriptMethod']) && $extConfig['scriptMethod'] == self::SCRIPT_MODE_NONCE;
    }

    /**
     * Creates a ContentSecurityPolicyHeaderBuilderInterface instance through a reference
     * in the $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['csp']['ContentSecurityPolicyHeaderBuilder']
     *
     * @return ContentSecurityPolicyHeaderBuilderInterface
     * @throws InvalidClassException
     */
    private static function createNewBuilderInstance()
    {
        $className = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['csp']['ContentSecurityPolicyHeaderBuilder'] ??
            ContentSecurityPolicyHeaderBuilder::class;

        /** @var ContentSecurityPolicyHeaderBuilderInterface $instance */
        $instance = GeneralUtility::makeInstance($className);

        if (!($instance instanceof ContentSecurityPolicyHeaderBuilderInterface)) {
            throw new InvalidClassException(
                sprintf(
                    'The class "%s" must implement the interface ContentSecurityPolicyHeaderBuilderInterface',
                    $className
                ),
                1505944587
            );
        }

        return $instance;
    }

    /**
     * Resets the header builder to a new instance
     */
    public static function resetBuilder()
    {
        self::$headerBuilder = self::createNewBuilderInstance();
    }

    /**
     * Resets the extension configuration
     *
     * @internal
     */
    public static function reloadConfig()
    {
        self::$extensionConfigurationLoaded = false;
        self::getExtensionConfiguration();
    }

    /**
     * @param TypoScriptFrontendController $tsfe
     */
    public static function addTypoScriptSettings($tsfe)
    {
        $enabled = (bool)($tsfe->config['config']['csp.']['enabled'] ?? false);

        if ($enabled) {
            $builder = self::getBuilder();

            $config = $tsfe->tmpl->setup['plugin.']['tx_csp.']['settings.'];

            if (isset($config['additionalSources.'])) {
                foreach ($config['additionalSources.'] as $directive => $sources) {
                    foreach ($sources as $source) {
                        $builder->addSourceExpression(rtrim($directive, '.') . self::DIRECTIVE_POSTFIX, $source);
                    }
                }
            }
            if (isset($config['presets.'])
                && is_array($config['presets.'])) {
                foreach ($config['presets.'] as $preSet) {
                    $preSetEnabled = (bool)($preSet['enabled'] ?? false);
                    if ($preSetEnabled
                        && isset($preSet['rules.'])
                    ) {
                        foreach ($preSet['rules.'] as $directive => $source) {
                            $builder->addSourceExpression($directive . self::DIRECTIVE_POSTFIX, $source);
                        }
                    }
                }
            }
            if (isset($config['reportOnly']) &&
                $config['reportOnly']) {
                $builder->useReportingMode();

                if (!isset($config['report-uri'])
                    || !$config['report-uri']) {
                    $absoluteReportScriptPath =
                        ExtensionManagementUtility::extPath('csp') . self::$reportScriptPath;

                    $relativeReportScriptPath =
                        PathUtility::getRelativePath(PATH_site . TYPO3_mainDir, $absoluteReportScriptPath);

                    if ($relativeReportScriptPath) {
                        //Make an absolute path from the site root to the script
                        $relativeReportScriptPath = rtrim(ltrim($relativeReportScriptPath, '.'), '/');

                        $builder->addSourceExpression(
                            Directives::REPORT_URI,
                            $relativeReportScriptPath
                        );
                    }
                }
            }
            if (isset($config['report-uri'])
                && $config['report-uri']) {
                $builder->addSourceExpression(Directives::REPORT_URI, htmlspecialchars($config['report-uri']));
            }
        }
    }

    /**
     * @return string
     */
    public static function extractHeaders()
    {
        $responseHeader = '';
        $headers = self::getBuilder()->getHeader();
        if (count($headers) > 1) {
            $name = $headers['name'] ?? '';
            $value = $headers['value'] ?? '';

            $responseHeader = $name . ': ' . $value;
        }

        return $responseHeader;
    }
}
