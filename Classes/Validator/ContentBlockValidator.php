<?php

declare(strict_types=1);

/*
 * This file is part of the package typo3-contentblocks/contentblocks-reg-api.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Typo3Contentblocks\ContentblocksRegApi\Validator;

use TYPO3\CMS\Core\SingletonInterface;

class ContentBlockValidator implements SingletonInterface
{
    /**
     * Throws on validation error
     * @param array $cbConfiguration
     */
    public function validate(array $cbConfiguration): void
    {
        // TODO
        // we should check that there are no fields 'data' or 'cb' defined since these would clash
        // identifiers should not contain '.'
    }
}
