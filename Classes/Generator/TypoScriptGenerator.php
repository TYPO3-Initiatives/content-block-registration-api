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
    public function typoScriptForContentBlock(array $contentBlock): string
    {
        return '
tt_content.' . $contentBlock['CType'] . ' < lib.contentBlock
tt_content.' . $contentBlock['CType'] . '{
    templateName = Frontend
    templateRootPaths {
        20 = ' . $contentBlock['frontendTemplatesPath'] . '
    }
    partialRootPaths {
        20 = ' . $contentBlock['frontendPartialsPath'] . '
    }
    layoutRootPaths {
        20 = ' . $contentBlock['frontendLayoutsPath'] . '
    }
}
';
    }
}
