<?php
declare(strict_types=1);

/*
 * This file is part of the package sci/sci-api.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Sci\SciApi\Service;

use Sci\SciApi\Constants;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ConfigurationService
{
    public static function getConfiguration()
    {
        $cache = GeneralUtility::makeInstance(CacheManager::class)
            ->getCache(Constants::CACHE);

        if (false === $configuration = $cache->get(Constants::CACHE_CONFIGURATION_ENTRY)) {
            $configuration = self::buildConfiguration();
            $cache->set(Constants::CACHE_CONFIGURATION_ENTRY, $configuration, [], 0);
        }

        return $configuration;
    }

    protected static function buildConfiguration()
    {
        $cbsFinder = new Finder();
        $cbsFinder->directories()->in(Environment::getPublicPath() . Constants::BASEPATH);

        $contentBlockConfiguration = [];
        foreach ($cbsFinder as $cbDir) {
            $_path = $cbDir->getRealPath();

            $_composerJsonPath = $_path . DIRECTORY_SEPARATOR . 'composer.json';
            if (!is_readable($_composerJsonPath)) {
                throw new \Exception($_composerJsonPath . ' not found');
            }

            $_editorInterfaceYamlPath = $_path . DIRECTORY_SEPARATOR . 'EditorInterface.yaml';
            if (!is_readable($_composerJsonPath)) {
                throw new \Exception($_editorInterfaceYamlPath . ' not found');
            }

            $_cbIdentifier = $cbDir->getBasename();

            $_composerJson = json_decode(file_get_contents($_composerJsonPath), true);
            if (null === $_composerJson) {
                // fallback: use directory name
                $_ctype = $cbDir->getBasename();
            } else {
                [$_vendor, $_packageName] = explode('/', $_composerJson['name']);
                $_ctype = $_vendor . '_' . $_packageName;
            }

            $_editorInterfaceYaml = Yaml::parseFile($_editorInterfaceYamlPath);

            $contentBlockConfiguration [$_cbIdentifier] = [
                'path' => $_path,
                'CType' => $_ctype,
                'yaml' => $_editorInterfaceYaml,
            ];
        }

        return $contentBlockConfiguration;
    }
}
