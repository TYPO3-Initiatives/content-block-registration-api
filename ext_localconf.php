<?php

defined('TYPO3_MODE') || die('Access denied.');

(static function ($_EXTKEY = 'contentblocks_reg_api') {
    if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][\Typo3Contentblocks\ContentblocksRegApi\Constants::CACHE])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][\Typo3Contentblocks\ContentblocksRegApi\Constants::CACHE] = [
            'groups' => ['system'],
        ];
    }

    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][\Typo3Contentblocks\ContentblocksRegApi\Constants::CACHE]['frontend'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][\Typo3Contentblocks\ContentblocksRegApi\Constants::CACHE]['frontend'] =
            \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class;
    }

    $contentBlocks = Typo3Contentblocks\ContentblocksRegApi\Service\ConfigurationService::configuration();

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
                    title = LLL:' . $contentBlock['EditorInterface.xlf'] . ':' . $contentBlock['vendor'] . '.' . $contentBlock['package'] . '.title
                    description = LLL:' . $contentBlock['EditorInterface.xlf'] . ':' . $contentBlock['vendor'] . '.' . $contentBlock['package'] . '.description
                    tt_content_defValues {
                        CType = ' . $contentBlock['CType'] . '
                    }
                }
            }
            show := addToList(' . $contentBlock['CType'] . ')
        }
    '
        );

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


        /* Add typoscript dynamical */
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup('
        tt_content.' . $contentBlock['CType'] . ' =< lib.contentElement
        tt_content.' . $contentBlock['CType'] . ' = FLUIDTEMPLATE
        tt_content.' . $contentBlock['CType'] . '{
            templateName = Frontend
            templateRootPaths {
                5 = ' . $contentBlock['frontendTemplatePath'] . '
            }
            partialRootPaths {
                5 = ' . $contentBlock['frontendTemplatePath'] . '
            }
            layoutRootPaths {
                5 = ' . $contentBlock['frontendTemplatePath'] . '
            }
            dataProcessing {
                10 = Typo3Contentblocks\ContentblocksRegApi\DataProcessing\FlexFormProcessor
            }
        }
        ');
    }

    /** Wizard start **/
    /* Add typoscript setup for wizard */
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup('
    module.tx_contentblocksregapi {
        view {
            templateRootPaths.0 = EXT:contentblocks_reg_api/Resources/Private/Wizard/Templates/
            partialRootPaths.0 = EXT:contentblocks_reg_api/Resources/Private/Wizard/Partials/
            layoutRootPaths.0 = EXT:contentblocks_reg_api/Resources/Private/Wizard/Layouts/
        }
        persistence {
            storagePid = 0
        }
    }
    ');
    /** Wizard end **/
})();
