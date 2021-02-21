<?php

declare(strict_types=1);

/*
 * This file is part of the package typo3-contentblocks/contentblocks-reg-api.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Typo3Contentblocks\ContentblocksRegApi\Generator;

class PageTsConfigGenerator
{
    public function pageTsConfigForContentBlock(array $contentBlock): string
    {
        return '
mod.wizards.newContentElement.wizardItems.' . ((strlen('' . $contentBlock['yaml']['group']) > 0) ? $contentBlock['yaml']['group'] : 'common') . '  {
    elements {
        ' . $contentBlock['CType'] . ' {
            iconIdentifier = ' . $contentBlock['CType'] . '
            title = LLL:' . $contentBlock['EditorInterfaceXlf'] . ':' . $contentBlock['vendor'] . '.' . $contentBlock['package'] . '.title
            description = LLL:' . $contentBlock['EditorInterfaceXlf'] . ':' . $contentBlock['vendor'] . '.' . $contentBlock['package'] . '.description
            tt_content_defValues {
                CType = ' . $contentBlock['CType'] . '
            }
        }
    }
    show := addToList(' . $contentBlock['CType'] . ')
}
';
    }
}
