<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
$pluginSignature = 'csp_iframeplugin';

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue(
    $pluginSignature,
    'FILE:EXT:csp/Configuration/FlexForms/flexform_iframe.xml'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin('AndrasOtto.csp', 'IframePlugin', 'LLL:EXT:csp/Resources/Private/Language/backend.xlf:plugin.iframe.title', 'LLL:EXT:csp/Resources/Private/Language/backend.xlf:plugin.iframe.description');
