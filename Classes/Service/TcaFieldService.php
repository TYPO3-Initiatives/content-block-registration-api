<?php

declare(strict_types=1);

/*
 * This file is part of the package typo3-contentblocks/contentblocks-reg-api.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Typo3Contentblocks\ContentblocksRegApi\Service;

use TYPO3\CMS\Core\SingletonInterface;
use Typo3Contentblocks\ContentblocksRegApi\Constants;
use Typo3Contentblocks\ContentblocksRegApi\Service\DataService;


/* Class TcaFieldService
 * Manage to get the TCA configuration for each field.
 *
 */
class TcaFieldService implements SingletonInterface
{

    /**
     * @var DataService
     */
    protected $dataService;


    public function __construct(DataService $dataService)
    {
        $this->dataService = $dataService;
    }

    /*
     * Method getMatchedTcaConfig
     * Returns the matching TCA configuration as it is, as an array.
     * Supports the dynamically generated TCA.
     */
    public function getMatchedTcaConfig(array $contentBlock, array $field) :array
    {

        switch ($field['type']) {
            case 'Checkbox':
                return $this->getCheckboxFieldTca($contentBlock, $field);
            case 'Collection':
                return $this->getCollectionFieldTca($contentBlock, $field);
            case 'Color':
                return $this->getInputFieldTca($contentBlock, $field);
            case 'Date':
                return $this->getInputFieldTca($contentBlock, $field);
            case 'DateTime':
                return $this->getInputFieldTca($contentBlock, $field);
            case 'Email':
                return $this->getInputFieldTca($contentBlock, $field);
            case 'Image':
                return $this->getImageFieldTca($contentBlock, $field);
            case 'Integer':
               return $this->getInputFieldTca($contentBlock, $field);
            case 'Money':
                return $this->getInputFieldTca($contentBlock, $field);
            case 'MultiSelect':
                return $this->getSelectFieldTca($contentBlock, $field);
            case 'Number':
                return $this->getInputFieldTca($contentBlock, $field);
            case 'Percent':
                return $this->getInputFieldTca($contentBlock, $field);
            case 'Radiobox':
                return $this->getCheckboxFieldTca($contentBlock, $field);
            case 'Select':
                return $this->getSelectFieldTca($contentBlock, $field);
            case 'Tel':
                return $this->getInputFieldTca($contentBlock, $field);
            case 'Text':
                return $this->getInputFieldTca($contentBlock, $field);
            case 'Textarea':
                return $this->getTextareaFieldTca($contentBlock, $field);
            case 'TextMultiline':
                return $this->getTextareaFieldTca($contentBlock, $field);
            case 'Time':
                return $this->getInputFieldTca($contentBlock, $field);
            case 'Toggle':
                return $this->getCheckboxFieldTca($contentBlock, $field);
            case 'Url':
                return $this->getInputFieldTca($contentBlock, $field);
            default:
                return []; // TODO: throw exception not supported field type (column type).
        }

        return []; // in case of fire, keep calm.
    }

    /*********************
     *  INPUT FIELD CONFIG
     */
    protected function getInputFieldTca(array $contentBlock, array $field): array
    {
        $config = [
            'type' => 'input',
            'size' => (($field['properties']['size']) ? $field['properties']['size'] : 30),
        ];

        // Add basic TCA stuff to the config
        $config = $this->setConfigBasics($config,$field);

        $evalFields = ((isset($config['eval']) ? $config['eval'] : '')); // save values from config basics
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
        if ($field['type'] === 'Range' || $field['type'] === 'Percent') {
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

        $config['eval'] = $evalFields;

        if ($field['type'] === 'Url') {
            $config['renderType'] = 'inputLink';
            if (isset($field['properties']['linkPopup'])){
                $config['fieldControl'] = [];
                $config['fieldControl']['linkPopup'] = [];
                $config['fieldControl']['linkPopup']['options'] = $field['properties']['linkPopup'];
            }
        }

        if ($field['type'] === 'Date' || $field['type'] === 'DateTime' || $field['type'] === 'Time') {
            $config['renderType'] = 'inputDateTime';
            // $config['dbType'] = 'datetime';
            // While handling with datetime objects, those fields must be set to a handleable value.
            $config['default'] = ((!isset($config['default'])) ? strtotime('now') : $config['default'] );
        }

        if (is_array($field['properties']['range'])) {
            $config['range'] = $field['properties']['range'];
            if ($field['type'] === 'Date' || $field['type'] === 'DateTime') {
                if (isset($config['range']['lower'])) {
                    $config['range']['lower'] = ((strtotime($config['range']['lower'])) ? strtotime($config['range']['lower']) : 0);
                }
                if (isset($config['range']['upper'])) {
                    $config['range']['upper'] = ((strtotime($config['range']['upper'])) ? strtotime($config['range']['upper']) : 0);
                }
            }
            if ($field['type'] === 'Time') {
                if(isset($field['properties']['default'])){
                    $config['default'] = ((is_int($field['properties']['default'])) ? $field['properties']['default'] : strtotime('1970-01-01 ' . $field['properties']['default']));
                }
                if (isset($config['range']['lower']) && strlen('' . $config['range']['lower']) < 9) {
                    $config['range']['lower'] = ((strtotime($config['range']['lower'])) ? strtotime('1970-01-01 ' . $config['range']['lower']) : 0);
                }
                if (isset($config['range']['upper']) && strlen('' . $config['range']['upper']) < 9) {
                    $config['range']['upper'] = ((strtotime($config['range']['upper'])) ? strtotime('1970-01-01 ' . $config['range']['upper']) : 0);
                }
            }
        }

        if ($field['type'] === 'Percent' && is_array($field['properties']['slider'])) {
            $config['slider'] = $field['properties']['slider'];
        } elseif ($field['type'] === 'Color') {
            $config['renderType'] = 'colorpicker';
        }
        if (is_array($field['properties']['valuePicker']['items'])) {
            $tempPickerItems = [];
            foreach ($field['properties']['valuePicker']['items'] as $key => $name) {
                $tempPickerItems[] = [$name, $key];
            }
            $config['valuePicker']['items'] = $tempPickerItems;
        }

        if (isset($field['properties']['autocomplete'])){
            $config['autocomplete'] = $field['properties']['autocomplete'];
        }
        if (isset($field['properties']['displayAge'])){
            $config['displayAge'] = $field['properties']['displayAge'];
        }

        return [
            'exclude' => 1,
            'label' => 'LLL:' . $contentBlock['EditorInterfaceXlf'] . ':' . $contentBlock['vendor']
                        . '.' . $contentBlock['package'] . '.' . $field['_identifier'] . '.label',
            'description' => 'LLL:' . $contentBlock['EditorInterfaceXlf'] . ':' . $contentBlock['vendor']
            . '.' . $contentBlock['package'] . '.' . $field['_identifier'] . '.description',
            'config' => $config,
        ];
    }

    /*********************
     *  TEXTAREA FIELD CONFIG
     */
    protected function getTextareaFieldTca(array $contentBlock, array $field): array
    {
        $config = [
            'type' => 'text',
        ];

        // Add basic TCA stuff to the config
        $config = $this->setConfigBasics($config,$field);

        if (isset($field['properties']['cols'])){
            $config['cols'] = $field['properties']['cols'];
        }
        if (isset($field['properties']['enableRichtext'])){
            $config['enableRichtext'] = $field['properties']['enableRichtext'];
        }
        if (isset($field['properties']['richtextConfiguration'])){
            $config['richtextConfiguration'] = $field['properties']['richtextConfiguration'];
        }
        if (isset($field['properties']['rows'])){
            $config['rows'] = $field['properties']['rows'];
        }

        return [
            'exclude' => 1,
            'label' => 'LLL:' . $contentBlock['EditorInterfaceXlf'] . ':' . $contentBlock['vendor']
                        . '.' . $contentBlock['package'] . '.' . $field['_identifier'] . '.label',
            'description' => 'LLL:' . $contentBlock['EditorInterfaceXlf'] . ':' . $contentBlock['vendor']
            . '.' . $contentBlock['package'] . '.' . $field['_identifier'] . '.description',
            'config' => $config,
        ];
    }

    /*********************
     *  CHECKBOX FIELD CONFIG
     */
    protected function getCheckboxFieldTca(array $contentBlock, array $field): array
    {
        $config = [
            'type' => 'check',
        ];

        // Add basic TCA stuff to the config
        $config = $this->setConfigBasics($config,$field);

        if ($field['type'] =='Radiobox') {
            $config['type'] = 'radio';
        }
        if ($field['type'] =='Toggle') {
            $config['renderType'] = 'checkboxToggle';
        }
        if (isset($field['properties']['cols'])){
            $config['cols'] = $field['properties']['cols'];
        }

        if (isset($field['properties']['items'])){
            $items = [];
            foreach ($field['properties']['items'] as $key => $value) {
                $items[] = [ $value, $key];
            }
            if ($field['type'] =='Toggle' && isset($field['properties']['invertStateDisplay']) && $field['properties']['invertStateDisplay'] === true ) {
                $items['invertStateDisplay'] = true;
            }
            $config['items'] = $items;
        }
        elseif ($field['type'] =='Toggle' && isset($field['properties']['invertStateDisplay']) && $field['properties']['invertStateDisplay'] === true ) {
            $config['items'] = [
                [
                   0 => '',
                   1 => '',
                   'invertStateDisplay' => true
                ]
             ];
        }
        else {
            $config['items'] = [
                [
                    0 => '',
                    1 => ''
                ]
            ];
        }

        return [
            'exclude' => 1,
            'label' => 'LLL:' . $contentBlock['EditorInterfaceXlf'] . ':' . $contentBlock['vendor']
                        . '.' . $contentBlock['package'] . '.' . $field['_identifier'] . '.label',
            'description' => 'LLL:' . $contentBlock['EditorInterfaceXlf'] . ':' . $contentBlock['vendor']
            . '.' . $contentBlock['package'] . '.' . $field['_identifier'] . '.description',
            'config' => $config,
        ];
    }

    /*********************
     *  IMAGE FIELD CONFIG
     */
    protected function getImageFieldTca(array $contentBlock, array $field): array
    {
        $config = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
            $field['_identifier'],
            [
                'appearance' => [
                   'createNewRelationLinkTitle' => 'LLL:EXT:cms/locallang_ttc.xlf:images.addFileReference'
                ],
                // custom configuration for displaying fields in the overlay/reference table
                // to use the image overlay palette instead of the basic overlay palette
                'overrideChildTca' => [
                    'types' => [
                        '0' => [
                            'showitem' => '
                                --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette'
                        ],
                        \TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => [
                            'showitem' => '
                                --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette'
                        ],
                        \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
                            'showitem' => '
                                --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette'
                        ],
                    ],
                ],
            ],
            $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
        );

        if (isset($field['properties']['required']) && $field['properties']['required'] === true){
            $config['eval'] = 'required';
        }
        if (isset($field['properties']['maxItems'])){
            $config['maxitems'] = $field['properties']['maxItems'];
        }
        if (isset($field['properties']['minItems'])){
            $config['minitems'] = $field['properties']['minItems'];
        }

        return [
            'exclude' => 1,
            'label' => 'LLL:' . $contentBlock['EditorInterfaceXlf'] . ':' . $contentBlock['vendor']
                        . '.' . $contentBlock['package'] . '.' . $field['_identifier'] . '.label',
            'description' => 'LLL:' . $contentBlock['EditorInterfaceXlf'] . ':' . $contentBlock['vendor']
            . '.' . $contentBlock['package'] . '.' . $field['_identifier'] . '.description',
            'config' => $config,
        ];

    }

    /*********************
     *  COLLECTION FIELD CONFIG
     */
    protected function getCollectionFieldTca(array $contentBlock, array $field): array
    {
        // get the fields in the collections
        $fieldsConfig = '';
        if (isset($field['properties']['fields']) && count($field['properties']['fields']) > 0) {
            foreach ($field['properties']['fields'] as $collectionField) {
                $identifier = $this->dataService->uniqueColumnName($contentBlock['key'], $collectionField['_identifier']);
                $fieldsConfig .= (($fieldsConfig === '') ? $identifier : ',' . $identifier);
            }
            $fieldsConfig .= ', --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access, --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.visibility;visibility, --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access, --palette--;;hiddenLanguagePalette,';
        }

        $uniqueFieldIdentifier = $this->dataService->uniqueColumnName($contentBlock['key'], $field['_identifier']);
        $config = [
            'type' => 'inline',
            'foreign_table' => Constants::COLLECTION_FOREIGN_TABLE,
            'foreign_field' => Constants::COLLECTION_FOREIGN_FIELD,
            'foreign_table_field' => Constants::COLLECTION_FOREIGN_TABLE_FIELD,
            'foreign_match_fields' => [
                Constants::COLLECTION_FOREIGN_MATCH_FIELD => $uniqueFieldIdentifier,
            ],
            'appearance' => [
                'collapseAll' => 1,
                'expandSingle' => 1,
                'useSortable' => 1,
                'enabledControls' => [
                    'delete' => 1,
                    'dragdrop' => 1,
                    'new' => 1,
                    'hide' => 1,
                    'info' => 1,
                    'localize' => 1,
                ],
            ],
            'overrideChildTca' => [
                'types' => [
                    '1' => [
                        'showitem' => $fieldsConfig,
                    ],
                ],
            ],
        ];

        if (isset($field['properties']['maxItems'])){
            $config['maxitems'] = $field['properties']['maxItems'];
        }
        if (isset($field['properties']['minItems'])){
            $config['minitems'] = $field['properties']['minItems'];
        }
        if (isset($field['properties']['useAsLabel']) && is_array($field['properties']['fields']) ){
            $labelField = array_column($field['properties']['fields'], null, 'identifier');
            $labelField = $labelField[ $field['properties']['useAsLabel'] ];

            if (
                strlen('' . $labelField['identifier']) > 0
                && strpos('Color, Date, DateTime, Email, Integer, Money, Number, Percent, Tel, Text, Textarea, Time, Url', $labelField['type'])
            ){
                $labelFieldIdentifier = $this->dataService->uniqueColumnName($contentBlock['key'], $labelField['_identifier']);
                $config['foreign_label'] = $labelFieldIdentifier;
            }
        }

        return [
            'exclude' => 1,
            'label' => 'LLL:' . $contentBlock['EditorInterfaceXlf'] . ':' . $contentBlock['vendor']
                        . '.' . $contentBlock['package'] . '.' . $field['_identifier'] . '.label',
            'description' => 'LLL:' . $contentBlock['EditorInterfaceXlf'] . ':' . $contentBlock['vendor']
            . '.' . $contentBlock['package'] . '.' . $field['_identifier'] . '.description',
            'config' => $config,
        ];
    }

    /*********************
     *  SELECT FIELD CONFIG
     */
    protected function getSelectFieldTca(array $contentBlock, array $field): array
    {
        $config = [
            'type' => 'select',
            'renderType' => 'selectSingle',
        ];

        if ($field['type'] == 'MultiSelect'){
            $config['renderType'] = 'selectMultipleSideBySide';

            if (isset($field['properties']['size'])){ // Size only supportet by MultiSelect
                $config['size'] = $field['properties']['size'];
            }
        }

        // Add basic TCA stuff to the config
        $config = $this->setConfigBasics($config, $field);

        if (isset($field['properties']['items'])){
            $items = [];
            foreach ($field['properties']['items'] as $key => $value) {
                $items[] = [ $value, $key];
            }
            if ($field['type'] =='Toggle' && isset($field['properties']['invertStateDisplay']) && $field['properties']['invertStateDisplay'] === true ) {
                $items['invertStateDisplay'] = true;
            }
            $config['items'] = $items;
        }

        if (isset($field['properties']['maxItems'])){
            $config['maxitems'] = $field['properties']['maxItems'];
        }
        if (isset($field['properties']['minItems'])){
            $config['minitems'] = $field['properties']['minItems'];
        } elseif ($field['type'] == 'MultiSelect' && $field['properties']['required']) {
            $config['minitems'] = 1;
        }

        return [
            'exclude' => 1,
            'label' => 'LLL:' . $contentBlock['EditorInterfaceXlf'] . ':' . $contentBlock['vendor']
                        . '.' . $contentBlock['package'] . '.' . $field['_identifier'] . '.label',
            'description' => 'LLL:' . $contentBlock['EditorInterfaceXlf'] . ':' . $contentBlock['vendor']
            . '.' . $contentBlock['package'] . '.' . $field['_identifier'] . '.description',
            'config' => $config,
        ];
    }

    /************************
     * CONFIG HELPER: reduce redundant code by extracting to a "do basic config stuff" method.
     */
    protected function setConfigBasics(array $config, array $field): array
    {
        if (isset($field['properties']['max'])){
            $config['max'] = $field['properties']['max'];
        }
        if (isset($field['properties']['placeholder'])){
            $config['placeholder'] = $field['properties']['placeholder'];
        }
        if (isset($field['properties']['default'])){
            $config['default'] = $field['properties']['default'];
            if ($config['default'] === '@now') {
                $config['default'] = strtotime('now');
            }
        }

        $evalFields = ($field['properties']['required'] === true ? 'required' : '') .
            ($field['properties']['trim'] === true && $field['properties']['required'] === true ? ',  ' : '') .
            ($field['properties']['trim'] === true ? 'trim ' : '');

        if (strlen(''.$evalFields) > 1) {
            $config['eval'] = $evalFields;
        }

        return $config;
    }


}
