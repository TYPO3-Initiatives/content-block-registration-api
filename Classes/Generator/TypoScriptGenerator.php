<?php

declare(strict_types=1);

/*
 * This file is part of the package typo3-contentblocks/contentblocks-reg-api.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Typo3Contentblocks\ContentblocksRegApi\Generator;

class TypoScriptGenerator
{
    public static function typoScriptForContentBlock(array $contentBlock): string
    {
        return '
tt_content.' . $contentBlock['CType'] . ' < lib.contentElement
tt_content.' . $contentBlock['CType'] . ' = FLUIDTEMPLATE
tt_content.' . $contentBlock['CType'] . '{
    templateName = Frontend
    templateRootPaths {
        20 = ' . $contentBlock['frontendTemplatesPath'] . '
    }
    partialRootPaths {
        20 = ' . $contentBlock['frontendPartialsPath'] . '
    }
    layoutRootPaths {
        -5 = EXT:contentblocks_reg_api/Resources/Private/Layouts/
        20 = ' . $contentBlock['frontendLayoutsPath'] . '
    }
    dataProcessing {
        10 = Typo3Contentblocks\ContentblocksRegApi\DataProcessing\FlexFormProcessor
        20 = Typo3Contentblocks\ContentblocksRegApi\DataProcessing\CbProcessor
    }
}
';
    }
}
