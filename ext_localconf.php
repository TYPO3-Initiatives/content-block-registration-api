<?php

defined('TYPO3_MODE') || die('Access denied.');

(static function ($_EXTKEY = 'sci_api') {
    if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][\Sci\SciApi\Constants::CACHE])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][\Sci\SciApi\Constants::CACHE] = [
            'groups' => ['system'],
        ];
    }

    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][\Sci\SciApi\Constants::CACHE]['frontend'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][\Sci\SciApi\Constants::CACHE]['frontend'] =
            \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class;
    }

    $contentBlocks = Sci\SciApi\Service\ConfigurationService::configuration();

    foreach ($contentBlocks as $contentBlock) {
        /***************
         * Add content element PageTSConfig
         */
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
            '
        mod.wizards.newContentElement.wizardItems.common  {
            elements {
                ' . $contentBlock['CType'] . ' {
                    iconIdentifier = ' . $contentBlock['CType'] . '
                    title = SEE TSCONFING TITLE
                    description = SEE TSCONFING DESCRIPTION
                    tt_content_defValues {
                        CType = ' . $contentBlock['CType'] . '
                    }
                }
            }
            show := addToList(' . $contentBlock['CType'] . ')
        }
    '
        );

        // Backend Preview PageTS
        if ($contentBlock['EditorPreview.html']) {
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
                '
            mod.web_layout.tt_content.preview.' . $contentBlock['CType'] . ' = ' . $contentBlock['EditorPreview.html']
            );
        }

        /***************
         * Register Icons
         */
        $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Imaging\IconRegistry::class
        );
        $iconRegistry->registerIcon(
            $contentBlock['CType'],
            $contentBlock['iconProviderClass'],
            ['source' => $contentBlock['icon']]
        );
    }
})();
