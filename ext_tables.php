<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3_MODE') || die('Access denied.');


call_user_func(
    function()
    {


        if (TYPO3_MODE === 'BE') {

            ExtensionUtility::registerModule(
                'Typo3Contentblocks.ContentblocksRegApi',
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

            /***************
             * Allow Custom Records on Standard Pages
             */
            ExtensionManagementUtility::allowTableOnStandardPages('tx_contentblocks_reg_api_collection');

        }

    }
);
