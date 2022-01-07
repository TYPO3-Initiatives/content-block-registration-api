<?php

defined('TYPO3_MODE') || die('Access denied.');

(static function ($_EXTKEY = 'contentblocks_reg_api') {
    // cache
    if (!is_array(
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']
        [\Typo3Contentblocks\ContentblocksRegApi\Constants::CACHE]
    )) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']
        [\Typo3Contentblocks\ContentblocksRegApi\Constants::CACHE] = [
            'groups' => ['system'],
            'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
        ];
    }

    // Icons
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Core\Imaging\IconRegistry::class
    );
    $iconRegistry->registerIcon(
        'ext-contentblocks_reg_api',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:contentblocks_reg_api/Resources/Public/Icons/Extension.svg']
    );

    // TypoScript
    // TODO: find a better way to add individuall definitions
    $importTypoScriptTemplate = (string)\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)
            ->get('contentblocks_reg_api', 'contentBlockDefinition');
    if ( strlen('' . $importTypoScriptTemplate) > 2)
    {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(
            "@import '$importTypoScriptTemplate'"
        );
    }
    else {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(
            "@import 'EXT:contentblocks_reg_api/Configuration/TypoScript/setup.typoscript'"
        );
    }

    // contentBlocks
    $contentBlocks = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        Typo3Contentblocks\ContentblocksRegApi\Service\ConfigurationService::class
    )
        ->configuration();
    foreach ($contentBlocks as $contentBlock) {
        // PageTsConfig
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
            TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                \Typo3Contentblocks\ContentblocksRegApi\Generator\PageTsConfigGenerator::class
            )
                ->pageTsConfigForContentBlock($contentBlock)
        );

        // Icons
        $iconRegistry->registerIcon(
            $contentBlock['CType'],
            $contentBlock['iconProviderClass'],
            ['source' => $contentBlock['icon']]
        );

        // CB TypoScript
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(
            TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                \Typo3Contentblocks\ContentblocksRegApi\Generator\TypoScriptGenerator::class
            )
                ->typoScriptForContentBlock($contentBlock)
        );
    }

    // module TypoScript
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(
        "@import 'EXT:contentblocks_reg_api/Configuration/TypoScript/module/setup.typoscript'"
    );
})();

// Add Upgrade Wizard
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['contentblocksRegApi_flexformToDbColumnsUpdate'] = \Typo3Contentblocks\ContentblocksRegApi\Updates\FlexformToDbColumnsUpdate::class;
