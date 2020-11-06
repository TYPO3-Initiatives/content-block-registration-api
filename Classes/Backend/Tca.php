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
            $GLOBALS['TCA']['tt_content']['columns']['content_block']['config']['ds'][ $contentBlock['CType'] ] = self::flexFormTemplate($flexFormFieldsConfig);
            // exit;
        }
    }

    /** parse single field */
    private static function createField($field, $cType)
    {
        if (!is_array($field)) {
            return '';
        } // if no array given, return
        elseif ($field['type'] === 'Collection') {
            $fieldsConfig = '';
            foreach ($field['properties']['fields'] as $CollectionField) {
                $fieldsConfig = $fieldsConfig . self::createField($CollectionField, $cType);
            }
            return $fieldsConfig;
        } else {
            switch ($field['type']) {
                case 'Text':
                    return self::createInputField($field, $cType);
                case 'TextMultiline':
                    return self::createTextarea($field, $cType);
                case 'Link':
                    return self::createTypoLink($field, $cType);
                default:
                    return '';
            }
        }
    }

    /** create typolink */
    private static function createTypoLink($field, $cType)
    {
        debug($field);
        return '
        <' . $field['identifier'] . '>
            <TCEforms>
                <label>TODO: Fill in the right name - Identifier: ' . $field['identifier'] . '</label>
                <config>
                    <type>input</type>
                    <size>' . ($field['properties']['size']  > 0 ? $field['properties']['size'] : '30') . '</size>
                    <eval>' . ($field['properties']['required'] === true ? 'required, ' : '') . 'trim</eval>
                    <softref>typolink,typolink_tag,images,url</softref>
                    <wizards>
                        <_PADDING>2</_PADDING>
                        <link>
                            <type>popup</type>
                            <title>Link</title>
                            <module>
                                <name>wizard_element_browser</name>
                                <urlParameters>
                                    <mode>wizard</mode>
                                </urlParameters>
                            </module>
                            <icon>link_popup.gif</icon>
                            <script>browse_links.php?mode=wizard</script>
                            <params>
                                <!--<blindLinkOptions>page,file,folder,url,spec</blindLinkOptions>-->
                            </params>
                            <JSopenParams>height=500,width=500,status=0,menubar=0,scrollbars=1</JSopenParams>
                        </link>
                    </wizards>
                </config>
            </TCEforms>
        </' . $field['identifier'] . '>
        ';
    }

    /** create textfield */
    private static function createInputField($field, $cType)
    {
        debug($field);
        return '
        <' . $field['identifier'] . '>
            <TCEforms>
                <label>TODO: Fill in the right name - Identifier: ' . $field['identifier'] . '</label>
                <config>
                    <type>input</type>
                    <size>' . ($field['properties']['size'] > 0 ? $field['properties']['size'] : '20') . '</size>
                    <max>' . ($field['properties']['max']  > 0 ? $field['properties']['max'] : '700') . '</max>
                    <eval>' . ($field['properties']['required'] === true ? 'required, ' : '') . 'trim</eval>
                </config>
            </TCEforms>
        </' . $field['identifier'] . '>
        ';
    }

    /** create textfield */
    private static function createTextarea($field, $cType)
    {
        debug($field);
        return '
        <' . $field['identifier'] . '>
            <TCEforms>
                <label>TODO: Fill in the right name - Identifier: ' . $field['identifier'] . '</label>
                <config>
                    <type>text</type>
                    <cols>' . ($field['properties']['cols'] === true ? $field['properties']['cols'] : '24') . '</cols>
                    <rows>' . ($field['properties']['rows'] === true ? $field['properties']['rows'] : '3') . '</rows>
                    <eval>' . ($field['properties']['required'] === true ? 'required, ' : '') . 'trim</eval>
                </config>
            </TCEforms>
        </' . $field['identifier'] . '>
        ';
    }

    protected static function flexFormTemplate($fieldConfig)
    {
        return '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
<T3DataStructure>
    <meta>
        <langDisable>1</langDisable>
    </meta>
    <sheets>
        <sDEF>
            <ROOT>
                <TCEforms>
                    <sheetTitle>FLEX FORM Text</sheetTitle>
                </TCEforms>
                <type>array</type>
                <el>
' . $fieldConfig . '
                </el>
            </ROOT>
        </sDEF>
    </sheets>
</T3DataStructure>
';
    }
}
