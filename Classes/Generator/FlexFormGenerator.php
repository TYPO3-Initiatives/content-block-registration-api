<?php

declare(strict_types=1);

/*
 * This file is part of the package typo3-contentblocks/contentblocks-reg-api.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Typo3Contentblocks\ContentblocksRegApi\Generator;

use TYPO3\CMS\Core\Resource\File;

class FlexFormGenerator
{

    /** create typolink */
    public static function createTypoLink($field, $contentBlock) // typo3-contentblocks.slider-local.slides.label
    {
        $blindLinkOption = 'page,url,mail,spec,file,folder,telephone';
        if (is_array($field['properties']['linkTypes'])) {
            foreach ($field['properties']['linkTypes'] as $allowedField) {
                $blindLinkOption = str_replace(str_replace('external', 'url', $allowedField), '', $blindLinkOption);
            }
        } else {
            $blindLinkOption = '';
        }

        $blindLinkFields = 'target,title,class,params';
        if (is_array($field['properties']['fieldTypes'])) {
            foreach ($field['properties']['fieldTypes'] as $allowedField) {
                $blindLinkFields = str_replace($allowedField, '', $blindLinkFields);
            }
        } else {
            $blindLinkFields = '';
        }

        return '
        <' . $field['identifier'] . '>
            <TCEforms>
                <label>LLL:' . $contentBlock['EditorInterface.xlf'] . ':' . $contentBlock['vendor']
            . '.' . $contentBlock['package'] . '.' . $field['identifier'] . '.label</label>
                <description>LLL:' . $contentBlock['EditorInterface.xlf'] . ':' . $contentBlock['vendor']
            . '.' . $contentBlock['package'] . '.' . $field['identifier'] . '.description</description>
                <config>
                    <type>input</type>
                    <renderType>inputLink</renderType>
                    <size>' . ($field['properties']['size'] > 0 ? $field['properties']['size'] : '30') . '</size>
                    <eval>' . ($field['properties']['required'] === true ? 'required, ' : '') . 'trim</eval>
                    <fieldControl>
                        <linkPopup>
                            <options>
                                <blindLinkOptions>' . $blindLinkOption . '</blindLinkOptions>
                                <blindLinkFields>' . $blindLinkFields . '</blindLinkFields>
                            </options>
                        </linkPopup>
                    </fieldControl>
                </config>
            </TCEforms>
        </' . $field['identifier'] . '>
        ';
    }

    /** create textfield */
    public static function createInputField($field, $contentBlock)
    {
        $items = '';

        $evalFields = ($field['properties']['required'] === true ? 'required' : '') . ($field['properties']['trim'] === true && $field['properties']['required'] === true ? ',  ' : '') . ($field['properties']['trim'] === true ? 'trim ' : '');
        if ($field['type'] === 'Email') {
            $evalFields = $evalFields . (strlen($evalFields) > 0 ? ', ' : '') . 'email';
        }
        if ($field['type'] === 'Integer') {
            $evalFields = $evalFields . (strlen($evalFields) > 0 ? ', ' : '') . 'int';
        }
        if ($field['type'] === 'Money') {
            $evalFields = $evalFields . (strlen($evalFields) > 0 ? ', ' : '') . 'double2';
        }
        if ($field['type'] === 'Number') {
            $evalFields = $evalFields . (strlen($evalFields) > 0 ? ', ' : '') . 'num';
        }
        if ($field['type'] === 'Password') {
            $evalFields = $evalFields . (strlen($evalFields) > 0 ? ', ' : '') . 'password';
        }
        if ($field['type'] === 'Range') {
            $evalFields = $evalFields . (strlen($evalFields) > 0 ? ', ' : '') . 'trim,int';
        }
        if ($field['type'] === 'Tel') {
            $evalFields = $evalFields . (strlen($evalFields) > 0 ? ', ' : '') . 'alphanum';
        }
        if ($field['type'] === 'Date') {
            $evalFields = $evalFields . (strlen($evalFields) > 0 ? ', ' : '') . 'date';
        }
        if ($field['type'] === 'DateTime') {
            $evalFields = $evalFields . (strlen($evalFields) > 0 ? ', ' : '') . 'datetime';
        }
        if ($field['type'] === 'Time') {
            $evalFields = $evalFields . (strlen($evalFields) > 0 ? ', ' : '') . 'time';
        }

        $additionlConfig = '';
        if (is_array($field['properties']['range'])) {
            $additionlConfig .= '
            <range>
                <lower>' . floatval(
                    ($field['properties']['range']['lower'] !== ''
                        ? $field['properties']['range']['lower']
                        : '0'
                    )
                ) . '</lower>
                <upper>' . floatval(
                    ($field['properties']['range']['upper'] !== ''
                        ? $field['properties']['range']['upper']
                        : '100'
                    )
                ) . '</upper>
            </range>';
        }
        if ($field['type'] === 'Percent') {
            $additionlConfig .= $additionlConfig . '
            <slider>
                <step>' . ($field['properties']['slider']['step'] !== '' ? $field['properties']['slider']['step'] : '1') . '</step>
                <width>' . ($field['properties']['slider']['width'] !== '' ? $field['properties']['slider']['width'] : '100') . '</width>
            </slider>
            ';
        } elseif ($field['type'] === 'Color') {
            $additionlConfig .= '<renderType>colorpicker</renderType>';

            if (is_array($field['properties']['valuePicker']['items'])) {
                $counter = 0;
                $items = '<items type="array">';
                foreach ($field['properties']['valuePicker']['items'] as $key => $value) {
                    $items .= '
                    <numIndex index="' . $counter . '" type="array">
                        <numIndex index="0">' . $value . '</numIndex>
                        <numIndex index="1">' . $key . '</numIndex>
                    </numIndex>';
                    $counter++;
                }
                $items .= '</items>';
            }
        } elseif ($field['type'] === 'Date' || $field['type'] === 'DateTime' || $field['type'] === 'Time') {
            $additionlConfig .= '<renderType>inputDateTime</renderType>';
        }

        if ($field['properties']['displayAge']) {
            $additionlConfig .= '<displayAge>true</displayAge>';
        }

        return '
        <' . $field['identifier'] . '>
            <TCEforms>
                <label>LLL:' . $contentBlock['EditorInterface.xlf'] . ':' . $contentBlock['vendor']
            . '.' . $contentBlock['package'] . '.' . $field['identifier'] . '.label</label>
                <description>LLL:' . $contentBlock['EditorInterface.xlf'] . ':' . $contentBlock['vendor']
            . '.' . $contentBlock['package'] . '.' . $field['identifier'] . '.description</description>
                <config>
                    <type>input</type>
                    <size>' . ($field['properties']['size'] > 0 ? $field['properties']['size'] : '20') . '</size>
                    <max>' . ($field['properties']['max'] > 0 ? $field['properties']['max'] : '700') . '</max>
                    <eval>' . $evalFields . '</eval>
                    <placeholder>' . $field['properties']['placeholder'] . '</placeholder>
                    <default>' . $field['properties']['default'] . '</default>
                    <autocomplete>' . ($field['properties']['autocomplete'] === true ? 'true ' : 'false') . '</autocomplete>
                    ' . $additionlConfig . '
                    ' . $items . '
                </config>
            </TCEforms>
        </' . $field['identifier'] . '>
        ';
    }

    /** create picture */
    public static function createImageField($field, $contentBlock)
    {
        return '
        <' . $field['identifier'] . '>
            <TCEforms>
                <label>LLL:' . $contentBlock['EditorInterface.xlf'] . ':' . $contentBlock['vendor']
            . '.' . $contentBlock['package'] . '.' . $field['identifier'] . '.label</label>
                <description>LLL:' . $contentBlock['EditorInterface.xlf'] . ':' . $contentBlock['vendor']
            . '.' . $contentBlock['package'] . '.' . $field['identifier'] . '.description</description>
                <config>

                    <type>inline</type>
                    <minItems>' . ($field['properties']['minItems'] > 0 ? $field['properties']['minItems'] : '1') . '</minItems>
                    <maxItems>' . ($field['properties']['maxItems'] > 0 ? $field['properties']['maxItems'] : '1') . '</maxItems>
                    <eval>' . ($field['properties']['required'] === true ? 'required' : '') . '</eval>
                    <foreign_table>sys_file_reference</foreign_table>
                    <foreign_table_field>tablenames</foreign_table_field>
                    <foreign_label>uid_local</foreign_label>
                    <foreign_sortby>sorting_foreign</foreign_sortby>
                    <foreign_field>uid_foreign</foreign_field>
                    <foreign_selector>uid_local</foreign_selector>
                    <foreign_match_fields>
                        <fieldname>' . $field['identifier'] . '</fieldname> <!-- This is the field name -->
                    </foreign_match_fields>
                    <appearance type="array">
                        <newRecordLinkAddTitle>1</newRecordLinkAddTitle>
                        <headerThumbnail>
                            <field>uid_local</field>
                            <height>64</height>
                            <width>64</width>
                        </headerThumbnail>
                        <enabledControls>
                            <info>1</info>
                            <new>0</new>
                            <dragdrop>1</dragdrop>
                            <sort>1</sort>
                            <hide>1</hide>
                            <delete>1</delete>
                            <localize>1</localize>
                        </enabledControls>
                        <createNewRelationLinkTitle>LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:images.addFileReference</createNewRelationLinkTitle>
                    </appearance>
                    <behaviour>
                        <localizationMode>select</localizationMode>
                        <localizeChildrenAtParentLocalization>1</localizeChildrenAtParentLocalization>
                    </behaviour>
                    <overrideChildTca>
                        <columns type="array">
                            <uid_local type="array">
                                <config type="array">
                                    <appearance type="array">
                                        <elementBrowserType>file</elementBrowserType>
                                        <elementBrowserAllowed>jpg,png,svg,jpeg,gif</elementBrowserAllowed>
                                    </appearance>
                                </config>
                            </uid_local>
                        </columns>
                        <types type="array">
                            <numIndex index="' . File::FILETYPE_UNKNOWN . '" type="array">
                                <showitem>
                                    --palette--;;imageoverlayPalette, --palette--;;filePalette
                                </showitem>
                            </numIndex>
                            <numIndex index="' . File::FILETYPE_TEXT . '" type="array">
                                <showitem>
                                    --palette--;;imageoverlayPalette, --palette--;;filePalette
                                </showitem>
                            </numIndex>
                            <numIndex index="' . File::FILETYPE_IMAGE . '" type="array">
                                <showitem>
                                    --palette--;;imageoverlayPalette, --palette--;;filePalette
                                </showitem>
                            </numIndex>
                        </types>
                    </overrideChildTca>
                </config>
            </TCEforms>
        </' . $field['identifier'] . '>
        ';
    }

    /** create textfield */
    public static function createTextarea($field, $contentBlock)
    {
        return '
        <' . $field['identifier'] . '>
            <TCEforms>
                <label>LLL:' . $contentBlock['EditorInterface.xlf'] . ':' . $contentBlock['vendor']
            . '.' . $contentBlock['package'] . '.' . $field['identifier'] . '.label</label>
                <description>LLL:' . $contentBlock['EditorInterface.xlf'] . ':' . $contentBlock['vendor']
            . '.' . $contentBlock['package'] . '.' . $field['identifier'] . '.description</description>
                <config>
                    <type>text</type>
                    <cols>' . ($field['properties']['cols'] === true ? $field['properties']['cols'] : '24') . '</cols>
                    <rows>' . ($field['properties']['rows'] === true ? $field['properties']['rows'] : '3') . '</rows>
                    ' . ($field['properties']['enableRichtext'] === true ? '<enableRichtext>true</enableRichtext>' : '') . '
                    ' . (
            strlen(
                $field['properties']['richtextConfiguration'] . ''
            ) > 0
                ? '<richtextConfiguration>' . $field['properties']['richtextConfiguration'] . '</richtextConfiguration>'
                : '') . '
                    <eval>' . ($field['properties']['required'] === true ? 'required' : '') . ($field['properties']['trim'] === true && $field['properties']['required'] === true ? ',  ' : '') . ($field['properties']['trim'] === true ? 'trim ' : '') . '</eval>
                    <placeholder>' . $field['properties']['placeholder'] . '</placeholder>
                    <default>' . $field['properties']['default'] . '</default>
                </config>
            </TCEforms>
        </' . $field['identifier'] . '>
        ';
    }

    /** create section */
    public static function createSection($field, $contentBlock)
    {
        // Section wrapping
        $fieldsConfig = '<' . $field['identifier'] . '> 
                            <title>LLL:' . $contentBlock['EditorInterface.xlf'] . ':' . $contentBlock['vendor']
                            . '.' . $contentBlock['package'] . '.' . $field['identifier'] . '.label</title> 
                            <type>array</type> 
                            <section>1</section> 
                            <el>
                                <container_' . $field['identifier'] . '>
                                    <type>array</type>
                                    <title>LLL:' . $contentBlock['EditorInterface.xlf'] . ':' . $contentBlock['vendor']
                                    . '.' . $contentBlock['package'] . '.' . $field['identifier'] . '.label</title>
                                    <el>';

        // field renderings
        foreach ($field['properties']['fields'] as $collectionField) {
            if ($collectionField['type'] === 'Collection') {
                $fieldsConfig = $fieldsConfig . self::createField($collectionField, $contentBlock);
            } 
            elseif ($collectionField['type'] === 'Image' || $collectionField['type'] === 'Icon'){
                $fieldsConfig .= self::errorMessageToFlexform($collectionField['type'], $collectionField['identifier']);
            }
            else {
                $fieldsConfig .= self::createField($collectionField, $contentBlock);
            }
        }
        
        // End section wrapping
        $fieldsConfig .= '</el>
                        </container_' . $field['identifier'] . '>
                    </el>
                </' . $field['identifier'] . '>';

        return $fieldsConfig;
    }

    /** create selection, checkboxes */
    public static function createSelections($field, $contentBlock)
    {
        if (is_array($field['properties']['items'])) {
            $counter = 0;
            $items = '<items type="array">';
            foreach ($field['properties']['items'] as $key => $value) {
                $items .= '
                <numIndex index="' . $counter . '" type="array">
                    <numIndex index="0">' . $value . '</numIndex>
                    <numIndex index="1">' . $key . '</numIndex>
                </numIndex>';
                $counter++;
            }
            $items .= '</items>';
        }

        $type = 'select';
        if ($field['type'] === 'Checkbox') {
            $type = 'check';
        }
        if ($field['type'] === 'Radiobox') {
            $type = 'radio';
        }

        $additionlConfig = '';
        if ($field['type'] === 'Select') {
            $additionlConfig = '<renderType>selectSingle</renderType>';
        }
        if ($field['type'] === 'MultiSelect') {
            $additionlConfig = '<renderType>selectMultipleSideBySide</renderType>';
        }

        if ($field['properties']['cols']) {
            $additionlConfig .= '<cols>' . $field['properties']['cols'] . '</cols>';
        }
        if ($field['properties']['maxItems']) {
            $additionlConfig .= '<maxitems>' . $field['properties']['maxItems'] . '</maxitems>';
        }

        return '
        <' . $field['identifier'] . '>
            <TCEforms>
                <label>LLL:' . $contentBlock['EditorInterface.xlf'] . ':' . $contentBlock['vendor']
            . '.' . $contentBlock['package'] . '.' . $field['identifier'] . '.label</label>
                <description>LLL:' . $contentBlock['EditorInterface.xlf'] . ':' . $contentBlock['vendor']
            . '.' . $contentBlock['package'] . '.' . $field['identifier'] . '.description</description>
                <config>
                    <type>' . $type . '</type>
                    <default>' . $field['properties']['default'] . '</default>
                    ' . $items . '
                    ' . $additionlConfig . '
                </config>
            </TCEforms>
        </' . $field['identifier'] . '>
        ';
    }

    /** create flexform */
    public static function createFlexform($contentBlock)
    {
        $flexFormFieldsConfig = '';

        foreach ($contentBlock['yaml']['fields'] as $field) {
            $flexFormFieldsConfig = $flexFormFieldsConfig . self::createField($field, $contentBlock);
        }

        /***************
         * Add flexForms for content element configuration
         */
        $GLOBALS['TCA']['tt_content']['columns']['content_block']['config']['ds'][$contentBlock['CType']] = self::flexFormTemplate($flexFormFieldsConfig);
    }

    /** generate Flexform wrapping structure */
    public static function flexFormTemplate($fieldConfig)
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

    /** parse single field */
    private static function createField($field, $contentBlock)
    {
        if (!is_array($field)) {
            return '';
        } // if no array given, return
        elseif ($field['type'] === 'Collection') {
            return self::createSection($field, $contentBlock);
        } else {
            switch ($field['type']) {
                case 'Email':
                case 'Integer':
                case 'Money':
                case 'Number':
                case 'Percent':
                case 'Text':
                case 'Password':
                case 'Range':
                case 'Tel':
                case 'Color':
                case 'Date':
                case 'DateTime':
                case 'Time':
                    return FlexFormGenerator::createInputField($field, $contentBlock);
                case 'Textarea':
                case 'TextMultiline':
                    return FlexFormGenerator::createTextarea($field, $contentBlock);
                case 'Link':
                case 'Url':
                    return FlexFormGenerator::createTypoLink($field, $contentBlock);
                case 'Image':
                case 'Icon':
                    return FlexFormGenerator::createImageField($field, $contentBlock);
                case 'Select':
                case 'Checkbox':
                case 'MultiSelect':
                case 'Radiobox':
                    return FlexFormGenerator::createSelections($field, $contentBlock);
                default:
                    return '';
            }
        }
    }
    /* Give some usefull hints to the user, if we actually do not support it */
    private static function errorMessageToFlexform($message, $identifier){
        $messageContent='
        <' .$identifier .'>
                <TCEforms>
                    <label>Error on field with identifier ' .$identifier .'</label>
                    <description>';

        switch ($message) {
            case 'Image':
                $messageContent .= 'Actually it is not allowed or not technical possible to use images inside a collection, since we render a collection as a flexform section.
                                We know that issue and working hard to fix it.';
                break;
            case 'Icon':
                $messageContent .= 'Actually it is not allowed or not technical possible to use images/icons inside a collection, since we render a collection as a flexform section.
                                We know that issue and working hard to fix it.';
                break;
            
            default:
                $messageContent .= 'Something went wrong with your field, but anyone forgot to create a usefull error message on that.';
                break;
        }

        $messageContent .= '</description>  </TCEforms>  </' .$identifier .'>';
        return $messageContent;
    }
}

