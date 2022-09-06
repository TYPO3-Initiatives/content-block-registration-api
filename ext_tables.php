<?php

defined('TYPO3') || die('Access denied.');

call_user_func(
    static function () {
        $contentBlocksSettings = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)->get('contentblocks_reg_api');
        $showBackendModule = isset($contentBlocksSettings['showBackendModule']) ? (bool)$contentBlocksSettings['showBackendModule'] : true;

        if ($showBackendModule) {
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
                'ContentblocksRegApi',
                'tools', // Make module a submodule of 'web'
                'wizard', // Submodule key
                '', // Position
                [
                    \Typo3Contentblocks\ContentblocksRegApi\Backend\Controller\WizardController::class =>  'new, create',
                ],
                [
                    'access' => 'admin',
                    'icon'   => 'EXT:contentblocks_reg_api/Resources/Public/Icons/Extension.svg',
                    'labels' => 'LLL:EXT:contentblocks_reg_api/Resources/Private/Language/locallang_db.xlf',
                    'navigationComponentId' => '',
                    'inheritNavigationComponentFromMainModule' => false
                ]
            );
        }

        /**
         * Allow Custom Records on Standard Pages
         */
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_contentblocks_reg_api_collection');
    }
);
