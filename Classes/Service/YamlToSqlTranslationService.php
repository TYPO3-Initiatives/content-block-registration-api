<?php

declare(strict_types=1);

/*
 * This file is part of the package typo3-contentblocks/contentblocks-reg-api.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 *
 * Class YamlToSqlTranslationService
 * Manage to get the right SQL definition for each contentblock defined field in the yaml file.
 *
 */

namespace Typo3Contentblocks\ContentblocksRegApi\Service;

use TYPO3\CMS\Core\SingletonInterface;

class YamlToSqlTranslationService implements SingletonInterface
{
    /** Method getSQL
     *  Main function to get SQL statement due to the given $field.
     *  $field needs to have a identifier and a supported type.
     *  That means, this both keys are required:
     *  $field['identifier']
     *  $field['type']
     *
     *  To generate a unique field name, we also need the contentblock identifier $cbIdentifier.
     *  The result of the field name is going to be 'bc_contentblockidentifier_fieldidentifier'.
     *
     * TODO: Translation from type to SQL should be managed in a yaml configuration.
     * This this yaml configuration should be overwritable by integrators.
     *
     * @param string $fieldIdentifier
     * @param string $cbIdentifier
     * @return string SQL statement
     */
    public function getSQL(string $fieldIdentifier, string $cbIdentifier, string $type): string
    {
        if ( strlen('' . $fieldIdentifier) < 1 || strlen('' . $type) < 1 || strlen('' . $cbIdentifier) < 1 ) {
            return ""; # TODO: Throw exception not enough information to get SQL definition to create a column.
        }

        // Unique name for the column
        $identifier = "`cb_" .$cbIdentifier . "_" . $fieldIdentifier . "`";

        switch ($type) {
            case 'Checkbox':
               return "$identifier VARCHAR(255) DEFAULT '' NOT NULL";
            case 'Collection':
               return "$identifier int(11) DEFAULT '0' NOT NULL";
            case 'Color':
               return "$identifier VARCHAR(255) DEFAULT '' NOT NULL";
            case 'Date':
               return "$identifier date";
            case 'Datetime':
               return "$identifier datetime";
            case 'Email':
                return "$identifier VARCHAR(255) DEFAULT '' NOT NULL";
            case 'Icon': // TODO: is the same as Image, support for Icon should be removed forever?
                return "$identifier int(11) DEFAULT '0' NOT NULL";
            case 'Image':
                return "$identifier int(11) DEFAULT '0' NOT NULL";
            case 'Integer':
                return "$identifier int(11) DEFAULT '0' NOT NULL";
            case 'Link': // FIXME: type Link is missing in the documentation OR is it called Url?
                return "$identifier VARCHAR(255) DEFAULT '' NOT NULL";
            case 'Money':
                return "$identifier double(11,4) DEFAULT 0.0 NOT NULL";
            case 'Multiselect':
                return "$identifier VARCHAR(255) DEFAULT '' NOT NULL";
            case 'Number':
                return "$identifier int(11) DEFAULT '0' NOT NULL";
            case 'Percent':
                return "$identifier int(11) DEFAULT '0' NOT NULL";
            case 'Radiobox':
                return "$identifier VARCHAR(255) DEFAULT '' NOT NULL";
            case 'Select':
                return "$identifier VARCHAR(255) DEFAULT '' NOT NULL";
            case 'Tel':
                return "$identifier VARCHAR(255) DEFAULT '' NOT NULL";
            case 'Text':
               return "$identifier VARCHAR(255) DEFAULT '' NOT NULL";
            case 'Textarea':
               return "$identifier text";
            case 'TextMultiline':
               return "$identifier text";
            case 'Time':
                return "$identifier datetime";
            case 'Toggle':
                return "$identifier varchar(255) DEFAULT '' NOT NULL";
            case 'Url': // FIXME: is this the same as type Link?
                return "$identifier varchar(255) DEFAULT '' NOT NULL";
            default:
                return ""; // TODO: throw exception not supported field type (column type).
        }

        return ""; // in case of fire, keep calm.
    }

}
