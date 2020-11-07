<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function()
    {


        if (TYPO3_MODE === 'BE') {

            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
                'Sci.SciApi',
                'tools', // Make module a submodule of 'web'
                'wizard', // Submodule key
                '', // Position
                [
                    \Sci\SciApi\Backend\Controller\WizardController::class =>  'new, create',
                ],
                [
                    'access' => 'admin',
                    'icon'   => 'EXT:sci_api/Resources/Public/Icons/Extension.svg',
                    'labels' => 'LLL:EXT:sci_api/Resources/Private/Language/locallang_db.xlf',
                    'navigationComponentId' => '',
                    'inheritNavigationComponentFromMainModule' => false
                ]
            );

        }

    }
);