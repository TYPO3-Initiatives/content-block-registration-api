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
                    title = LLL:' . $contentBlock['EditorInterface.xlf'] . ':sci.' . $contentBlock['package'] . '.title
                    description = LLL:' . $contentBlock['EditorInterface.xlf'] . ':sci.' . $contentBlock['package'] . '.description
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


        /* Add typoscript dynamical */
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup('
        tt_content.' . $contentBlock['CType'] . ' = FLUIDTEMPLATE
        tt_content.' . $contentBlock['CType'] . ' {
            ################
            ### TEMPLATE ###
            ################
            templateName = Frontend
            templateRootPaths {
                0 = ' . $contentBlock['frontendTemplatePath'] . '
            }

            ##########################
            ### DATA PREPROCESSING ###
            ##########################
            dataProcessing {
                1509614342 = TYPO3\CMS\Frontend\DataProcessing\FilesProcessor
                1509614342 {
                    references.fieldName = background_image
                    as = backgroundImage
                }
                10 = Sci\SciApi\DataProcessing\FlexFormProcessor
            }
            

        }
        ');
    }
})();
