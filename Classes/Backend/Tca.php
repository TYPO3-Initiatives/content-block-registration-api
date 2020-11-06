<?php
declare(strict_types=1);

/*
 * This file is part of the package sci/sci-api.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Sci\SciApi\Backend;

use Sci\SciApi\Service\ConfigurationService;
use Sci\SciApi\Generator\FlexFormGenerator;

class Tca
{

    /**
     * Create the TCA config for all Content Blocks
     **/
    public static function getTca()
    {
        $configuration = ConfigurationService::configuration();

        foreach ($configuration as $contentBlock) {
            /***************
             * Add Content Element
             */
            if (!is_array($GLOBALS['TCA']['tt_content']['types'][$contentBlock['CType']])) {
                $GLOBALS['TCA']['tt_content']['types'][$contentBlock['CType']] = [];
            }

            /***************
             * Assign Icon
             */
            $GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes'][$contentBlock['CType']] = $contentBlock['CType'];

            /***************
             * Add content element to selector list
             */
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem(
                'tt_content',
                'CType',
                [
                    'Content Element Text',
                    $contentBlock['CType'],
                    $contentBlock['CType'],
                ],
                'header',
                'after'
            );

            /***************
             * Configure element type
             */
            $GLOBALS['TCA']['tt_content']['types'][$contentBlock['CType']] = array_replace_recursive(
                $GLOBALS['TCA']['tt_content']['types'][$contentBlock['CType']],
                [
                    'showitem' => '
                        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                            --palette--;;general,
                            --palette--;;headers,
                            content_block,
                        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,
                            --palette--;;frames,
                            --palette--;;appearanceLinks,
                        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                            --palette--;;language,
                        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                            --palette--;;hidden,
                            --palette--;;access,
                        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,
                            categories,
                        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
                            rowDescription,
                        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,
                    ',
                ]
            );

            $flexFormFieldsConfig = '';

            foreach ($contentBlock['yaml']['fields'] as $field) {
                $flexFormFieldsConfig = $flexFormFieldsConfig . self::createField($field, $contentBlock['CType']);
            }

            /***************
             * Add flexForms for content element configuration
             */
            // var_dump($GLOBALS['TCA']['tt_content']['columns']['content_block']['config']['ds']['default']);
            $GLOBALS['TCA']['tt_content']['columns']['content_block']['config']['ds'][ $contentBlock['CType'] ] = FlexFormGenerator::flexFormTemplate($flexFormFieldsConfig);
            // exit;
        }
    }

    /** parse single field */
    private static function createField($field)
    {
        if (!is_array($field)) {
            return '';
        } // if no array given, return
        elseif ($field['type'] === 'Collection') {
            $fieldsConfig = '';
            foreach ($field['properties']['fields'] as $CollectionField) {
                $fieldsConfig = $fieldsConfig . self::createField($CollectionField);
            }
            return $fieldsConfig;
        } else {
            switch ($field['type']) {
                case 'Text':
                    return FlexFormGenerator::createInputField($field);
                case 'TextMultiline':
                    return FlexFormGenerator::createTextarea($field);
                case 'Link':
                    return FlexFormGenerator::createTypoLink($field);
                default:
                    return '';
            }
        }
    }
}
