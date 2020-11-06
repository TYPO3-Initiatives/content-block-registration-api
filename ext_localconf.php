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
