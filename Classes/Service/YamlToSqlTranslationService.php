<?php

declare(strict_types=1);

/*
 * This file is part of the package typo3-contentblocks/contentblocks-reg-api.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 *
 *
 */

namespace Typo3Contentblocks\ContentblocksRegApi\Service;

use TYPO3\CMS\Core\SingletonInterface;

/**
 * Class YamlToSqlTranslationService
 * Manage to get the right SQL definition for each contentblock defined field in the yaml file.
 *
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
     *
     */
    public function getSQL(string $uniqueColumnName, string $type): string
    {
        if ( strlen('' . $uniqueColumnName) < 1 || strlen('' . $type) < 1 ) {
            return ""; # TODO: Throw exception not enough information to get SQL definition to create a column.
        }

        // Unique name for the column
        $uniqueColumnName = "`$uniqueColumnName`";

        switch ($type) {
            case 'Checkbox':
               return "$uniqueColumnName VARCHAR(255) DEFAULT '' NOT NULL";
            case 'Collection':
               return "$uniqueColumnName int(11) DEFAULT '0' NOT NULL";
            case 'Color':
               return "$uniqueColumnName VARCHAR(255) DEFAULT '' NOT NULL";
            case 'Date':
               return "$uniqueColumnName int(11) DEFAULT '0' NOT NULL";
            case 'DateTime':
               return "$uniqueColumnName int(11) DEFAULT '0' NOT NULL";
            case 'Email':
                return "$uniqueColumnName VARCHAR(255) DEFAULT '' NOT NULL";
            case 'Icon': // TODO: is the same as Image, support for Icon should be removed forever?
                return "$uniqueColumnName int(11) DEFAULT '0' NOT NULL";
            case 'Image':
                return "$uniqueColumnName int(11) DEFAULT '0' NOT NULL";
            case 'Integer':
                return "$uniqueColumnName int(11) DEFAULT '0' NOT NULL";
            case 'Link': // FIXME: type Link is missing in the documentation OR is it called Url?
                return "$uniqueColumnName VARCHAR(255) DEFAULT '' NOT NULL";
            case 'Money':
                return "$uniqueColumnName double(11,4) DEFAULT 0.0 NOT NULL";
            case 'MultiSelect':
                return "$uniqueColumnName VARCHAR(255) DEFAULT '' NOT NULL";
            case 'Number':
                return "$uniqueColumnName int(11) DEFAULT '0' NOT NULL";
            case 'Percent':
                return "$uniqueColumnName int(11) DEFAULT '0' NOT NULL";
            case 'Radiobox':
                return "$uniqueColumnName VARCHAR(255) DEFAULT '' NOT NULL";
            case 'Select':
                return "$uniqueColumnName VARCHAR(255) DEFAULT '' NOT NULL";
            case 'Tel':
                return "$uniqueColumnName VARCHAR(255) DEFAULT '' NOT NULL";
            case 'Text':
               return "$uniqueColumnName VARCHAR(255) DEFAULT '' NOT NULL";
            case 'Textarea':
               return "$uniqueColumnName text";
            case 'TextMultiline':
               return "$uniqueColumnName text";
            case 'Time':
                return "$uniqueColumnName int(11) DEFAULT '0' NOT NULL";
            case 'Toggle':
                return "$uniqueColumnName varchar(255) DEFAULT '' NOT NULL";
            case 'Url': // FIXME: is this the same as type Link?
                return "$uniqueColumnName varchar(255) DEFAULT '' NOT NULL";
            default:
                return ""; // TODO: throw exception not supported field type (column type).
        }

        return ""; // in case of fire, keep calm.
    }

}
