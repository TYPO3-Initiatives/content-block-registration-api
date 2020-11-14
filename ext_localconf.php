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

    // contentBlocks
    $contentBlocks = Typo3Contentblocks\ContentblocksRegApi\Service\ConfigurationService::configuration();
    foreach ($contentBlocks as $contentBlock) {
        // PageTsConfig
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
            \Typo3Contentblocks\ContentblocksRegApi\Generator\PageTsConfigGenerator::pageTsConfigForContentBlock(
                $contentBlock
            )
        );

        // Icons
        $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Imaging\IconRegistry::class
        );
        $iconRegistry->registerIcon(
            $contentBlock['CType'],
            $contentBlock['iconProviderClass'],
            ['source' => $contentBlock['icon']]
        );

        // TypoScript
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(
            \Typo3Contentblocks\ContentblocksRegApi\Generator\TypoScriptGenerator::typoScriptForContentBlock(
                $contentBlock
            )
        );
    }

    // Module: Wizard TypoScript
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(
        "@import 'EXT:contentblocks_reg_api/Configuration/TypoScript/setup.typoscript'"
    );
})();
