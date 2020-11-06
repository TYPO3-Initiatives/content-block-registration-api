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
})();

$configuration = Sci\SciApi\Service\ConfigurationService::configuration();

foreach ($configuration as $contentBlock) 
{
    /***************
     * Add content element PageTSConfig
     */
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('
        mod.wizards.newContentElement.wizardItems.' . ( $contentBlock['yaml']['group'] ?? 'common' ) . '  {
            elements {
                ' . $contentBlock['CType'] . ' {
                    iconIdentifier = ' . $contentBlock['CType'] . '
                    title = LLL:' . $contentBlock['EditorInterface.xlf'] . ':sci.counter.title
                    description = LLL:' . $contentBlock['EditorInterface.xlf'] . ':sci.counter.description
                    tt_content_defValues {
                        CType = ' . $contentBlock['CType'] . '
                    }
                }
            }
            show := addToList(' . $contentBlock['CType'] . ')
        }
    ');

    
    /***************
     * Register Icons
     */
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $iconRegistry->registerIcon(
        $contentBlock['CType'],
        $contentBlock['iconProviderClass'],
        ['source' => $contentBlock['icon'] ]
    );
}