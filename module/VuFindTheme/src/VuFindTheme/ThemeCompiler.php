<?php
/**
 * Class to compile a theme hierarchy into a single flat theme.
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2017.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category VuFind
 * @package  Theme
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
namespace VuFindTheme;

/**
 * Class to compile a theme hierarchy into a single flat theme.
 *
 * @category VuFind
 * @package  Theme
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
class ThemeCompiler
{
    /**
     * Theme info object
     *
     * @var ThemeInfo
     */
    protected $info;

    /**
     * Last error message
     *
     * @var string
     */
    protected $lastError = null;

    /**
     * Constructor
     *
     * @param ThemeInfo $info Theme info object
     */
    public function __construct(ThemeInfo $info)
    {
        $this->info = $info;
    }

    /**
     * Compile from $source theme into $target theme.
     *
     * @param string $source Name of source theme
     * @param string $target Name of target theme
     *
     * @return bool
     */
    public function compile($source, $target)
    {
        // Validate input:
        try {
            $this->info->setTheme($source);
        } catch (\Exception $ex) {
            return $this->setLastError($ex->getMessage());
        }
        // Validate output:
        $baseDir = $this->info->getBaseDir();
        $targetDir = "$baseDir/$target";
        if (file_exists($targetDir)) {
            return $this->setLastError('Target already exists!');
        }
        if (!mkdir($targetDir)) {
            return $this->setLastError("Cannot create $targetDir");
        }

        // Copy all the files:
        $info = $this->info->getThemeInfo();
        $config = [];
        do {
            $config = $this->mergeConfig($info[$source], $config);
            if (!$this->copyDir("$baseDir/$source", $targetDir)) {
                return false;
            }
            $source = isset($info[$source]['extends'])
                ? $info[$source]['extends']
                : false;
        } while ($source);
        $configFile = "$targetDir/theme.config.php";
        if (!file_put_contents($configFile, var_export($config, true))) {
            return $this->setLastError("Problem exporting $configFile.");
        }
        return true;
    }

    /**
     * Get last error message.
     *
     * @return string
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Copy the contents of $src into $dest if no matching files already exist.
     *
     * @param string $src  Source directory
     * @param string $dest Target directory
     *
     * @return bool
     */
    protected function copyDir($src, $dest)
    {
        if (!is_dir($dest)) {
            if (!mkdir($dest)) {
                return $this->setLastError("Cannot create $dest");
            }
        }
        $dir = opendir($src);
        while ($current = readdir($dir)) {
            if ($current === '.' || $current === '..') {
                continue;
            }
            if (is_dir("$src/$current")) {
                if (!$this->copyDir("$src/$current", "$dest/$current")) {
                    return false;
                }
            } else if (!file_exists("$dest/$current")
                && !copy("$src/$current", "$dest/$current")
            ) {
                return $this->setLastError(
                    "Cannot copy $src/$current to $dest/$current."
                );
            }
        }
        closedir($dir);
        return true;
    }

    protected function mergeConfig($src, $dest)
    {
        foreach ($src as $key => $value) {
            switch ($key) {
            case 'extends':
                // skip "extends" configurations
                break;
            case 'helpers':
                if (!isset($dest['helpers'])) {
                    $dest['helpers'] = [];
                }
                $dest['helpers'] = $this->mergeConfig($value, $dest['helpers']);
                break;
            default:
                if (!isset($dest[$key])) {
                    $dest[$key] = $value;
                } else if (is_array($dest[$key])) {
                    $dest[$key] = array_merge($value, $dest[$key]);
                }
                break;
            }
        }
        return $dest;
    }

    /**
     * Set last error message and return a boolean false.
     *
     * @param string $error Error message.
     *
     * @return bool
     */
    protected function setLastError($error)
    {
        $this->lastError = $error;
        return false;
    }
}
