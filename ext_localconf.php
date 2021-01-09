<?php

defined('TYPO3_MODE') || die('Access denied.');

(static function ($_EXTKEY = 'contentblocks_reg_api') {
    // cache
    if (!is_array(
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][\Typo3Contentblocks\ContentblocksRegApi\Constants::CACHE]
    )) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][\Typo3Contentblocks\ContentblocksRegApi\Constants::CACHE] = [
            'groups' => ['system'],
            'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
        ];
    }

    // include lib.contentElement in order to have it available for the CBs to inherit from fluid styled content
    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('fluid_styled_content')) {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(
            "@import 'EXT:fluid_styled_content/Configuration/TypoScript/Helper/ContentElement.typoscript'"
        );
    }

    // include lib.contentElement in order to have it available for the CBs to inherit from bootstrap package
    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('bootstrap_package')) {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(
            "@import 'EXT:bootstrap_package/Configuration/TypoScript/ContentElement/Helper/ContentElement.typoscript'"
        );
    }

    // register custom typoscript setup
    if (strlen($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS'][$_EXTKEY]['additionalTypoScriptFile'] . '') > 0) {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(
            "@import 'EXT:" . $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS'][$_EXTKEY]['additionalTypoScriptFile'] . "'"
        );
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

        // TypoScript
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(
            TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                \Typo3Contentblocks\ContentblocksRegApi\Generator\TypoScriptGenerator::class
            )
                ->typoScriptForContentBlock($contentBlock)
        );
    }

    // Module: Wizard TypoScript
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(
        "@import 'EXT:contentblocks_reg_api/Configuration/TypoScript/setup.typoscript'"
    );
})();
