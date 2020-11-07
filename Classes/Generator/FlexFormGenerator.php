<?php

declare(strict_types=1);

/*
 * This file is part of the package sci/sci-api.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Sci\SciApi\Generator;

class FlexFormGenerator
{

    /** create typolink */
    public static function createTypoLink($field)
    {
        return '
        <' . $field['identifier'] . '>
            <TCEforms>
                <label>TODO: Fill in the right name - Identifier: ' . $field['identifier'] . '</label>
                <config>
                    <type>input</type>
                    <size>' . ($field['properties']['size'] > 0 ? $field['properties']['size'] : '30') . '</size>
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
    public static function createInputField($field)
    {
        return '
        <' . $field['identifier'] . '>
            <TCEforms>
                <label>TODO: Fill in the right name - Identifier: ' . $field['identifier'] . '</label>
                <config>
                    <type>input</type>
                    <size>' . ($field['properties']['size'] > 0 ? $field['properties']['size'] : '20') . '</size>
                    <max>' . ($field['properties']['max'] > 0 ? $field['properties']['max'] : '700') . '</max>
                    <eval>' . ($field['properties']['required'] === true ? 'required, ' : '') . 'trim</eval>
                </config>
            </TCEforms>
        </' . $field['identifier'] . '>
        ';
    }


    /** create picture */
    public static function createImageField($field)
    {
        return '
        <' . $field['identifier'] . '>
            <TCEforms>
                <label>TODO: Fill in the right name - Identifier: ' . $field['identifier'] . '</label>
                <config>

                    <type>inline</type>
                    <minItems>' . ($field['properties']['minItems']  > 0 ? $field['properties']['minItems'] : '0') . '</minItems>
                    <maxItems>' . ($field['properties']['maxItems']  > 0 ? $field['properties']['maxItems'] : '1') . '</maxItems>
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
                            <dragdrop>0</dragdrop>
                            <sort>1</sort>
                            <hide>0</hide>
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
                    </overrideChildTca>
                </config>
            </TCEforms>
        </' . $field['identifier'] . '>
        ';
    }

    /** create textfield */
    public static function createTextarea($field)
    {
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
}
