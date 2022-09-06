<?php

declare(strict_types=1);

/*
 * This file is part of the package typo3-contentblocks/contentblocks-reg-api.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Typo3Contentblocks\ContentblocksRegApi;

class Constants
{
    const BASEPATH = 'typo3conf/contentBlocks/';
    const CACHE = 'contentblocks_reg_api_configuration';
    const CACHE_CONFIGURATION_ENTRY = 'contentBlocks';
    const COLLECTION_FOREIGN_TABLE = 'tx_contentblocks_reg_api_collection';
    const COLLECTION_FOREIGN_TABLE_FIELD = 'content_block_foreign_table_field';
    const COLLECTION_FOREIGN_FIELD = 'content_block_foreign_field';
    const COLLECTION_FOREIGN_MATCH_FIELD = 'content_block_field_identifier';
    const FLEXFORM_FIELD = 'content_block';
    const UPGRADE_WIZARD_SEARCH_OLD_FLEXFORMS = '%T3FlexForms%data%sheet%';
    const LIST_INPUT_FIELD_TYPES = 'Color, Date, DateTime, Email, Integer, Money, Number, Percent, Tel, Text, Time, Url';
}
