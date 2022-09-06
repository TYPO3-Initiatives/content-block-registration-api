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

/**
 * Class YamlToSqlTranslationService
 * Manage to get the right SQL definition for each contentblock defined field in the yaml file.
 */
class YamlToSqlTranslationService implements SingletonInterface
{
    /**
     * Method getSQL
     * Main function to get SQL statement due to the given $field.
     * $field needs to have a identifier and a supported type.
     *
     * To generate a unique field name, we also need the contentblock identifier $cbIdentifier.
     * The result of the field name is going to be 'bc_contentblockidentifier_fieldidentifier'.
     *
     * TODO: Translation from type to SQL should be managed in a yaml configuration.
     * This this yaml configuration should be overwritable by integrators.
     *
     * @param string $uniqueColumnName
     * @return string SQL statement
     */
    public function getSQL(string $uniqueColumnName, string $type): string
    {
        if (strlen('' . $uniqueColumnName) < 1 || strlen('' . $type) < 1) {
            return ''; // TODO: Throw exception not enough information to get SQL definition to create a column.
        }

        // Unique name for the column
        $uniqueColumnName = "`$uniqueColumnName`";

        // return values:
        $returnInt = "$uniqueColumnName int(11) DEFAULT '0' NOT NULL";
        $returnVarchar = "$uniqueColumnName VARCHAR(255) DEFAULT '' NOT NULL";

        switch ($type) {
            case 'Checkbox':
                return $returnVarchar;
            case 'Collection':
                return $returnInt;
            case 'Color':
                return $returnVarchar;
            case 'Date':
            case 'DateTime':
                return $returnInt;
            case 'Email':
                return $returnVarchar;
            case 'Image':
            case 'Integer':
                return $returnInt;
            case 'Money':
                return "$uniqueColumnName double(11,4) DEFAULT 0.0 NOT NULL";
            case 'MultiSelect':
                return $returnVarchar;
            case 'Number':
            case 'Percent':
                return $returnInt;
            case 'Radiobox':
                return $returnVarchar;
            case 'Select':
                return $returnVarchar;
            case 'Tel':
                return $returnVarchar;
            case 'Text':
                return $returnVarchar;
            case 'Textarea':
                return "$uniqueColumnName text";
            case 'TextMultiline':
                return "$uniqueColumnName text";
            case 'Time':
                return $returnInt;
            case 'Toggle':
                return $returnVarchar;
            case 'Url':
                return $returnVarchar;
            default:
                return ''; // TODO: throw exception not supported field type (column type).
        }

        return ''; // in case of fire, keep calm.
    }
}
