<?php
/**
 * Copyright 2017 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl.
 *
 * @author   Michael J Rubinsky <mrubinsk@horde.org>
 * @author   Michael Slusarz <slusarz@horde.org>
 * @category Horde
 * @license  https://www.horde.org/licenses/bsd BSD
 * @package  GitTools
 */

namespace Horde\GitTools\Action\Dev;

use Horde\GitTools\Cli;
use Horde\GitTools\Local\PearPackages;

/**
 * Links the framework libraries into the web directory.
 *
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @author    Michael Slusarz <slusarz@horde.org>
 * @category  Horde
 * @copyright 2017 Horde LLC
 * @license   https://www.horde.org/licenses/bsd BSD
 * @package   GitTools
 */
class LinkFramework extends \Horde\GitTools\Action\Base
{
    /**
     *
     */
    public function run()
    {
        $horde_git = rtrim(ltrim($this->_params['git_base']), '/ ');
        $web_dir = rtrim(ltrim($this->_params['web_dir']), '/ ');
        $this->_linkFramework($horde_git, $web_dir);
    }

    /**
     * Perform the linking of the libraries in '/libs'.
     *
     * @param  string $horde_git  Path to the local base directory of all
     *                            repositories.
     * @param  string $web_dir    Path to the web accessible directory.
     * @todo  Allow overriding the '/libs' target?
     * @todo  This also assumes that the 'Horde_Cli' library is available either
     *        in the include_path, or locally checked out in $horde_git. Do
     *        we need to change this?
     */
    protected function _linkFramework($horde_git, $web_dir)
    {
        $destDir = $web_dir . DIRECTORY_SEPARATOR . '/libs';

        Cli::$cli->message('Source directory: ' . $horde_git);
        Cli::$cli->message('Framework destination directory: ' . $destDir);
        Cli::$cli->message('Horde directory: ' . $web_dir);
        Cli::$cli->message('Create symbolic links: ' . (!empty($this->_params['copy']) ? 'NO' : 'Yes'));

        // List of available packages to link.
        $pkg_ob = new PearPackages($this->_params);
        $pkg_list = $pkg_ob->getRepositories();

        Cli::$cli->writeLn();
        Cli::$cli->message('Package(s) to install: ' . ((count($pkg_list) === 1) ? reset($pkg_list) : 'ALL (' . count($pkg_list) . ' packages)'));

        // $key = package name, $val is repo base.
        foreach ($pkg_list as $key => $val) {
            Cli::$cli->message('Installing package ' . $key);

            // Get list of files
            $pear = $pkg_ob->getPearPackage($val . '/package.xml');
            $file_list = $pear->getInstallationFileList();

            foreach ($file_list as $file) {
                if (!isset($file['attribs']['name'])) {
                    $cli->message('Invalid <install> entry: ' . print_r($file['attribs'], true), 'cli.error');
                    continue;
                }

                $orig = realpath($val . '/' . $file['attribs']['name']);
                if (empty($orig)) {
                    $cli->message('Install file does not seem to exist: ' . $val . '/' . $file['attribs']['name'], 'cli.error');
                    continue;
                }

                switch ($file['attribs']['role']) {
                case 'horde':
                    if (isset($file['attribs']['install-as'])) {
                        $dest = $web_dir . '/' . $file['attribs']['install-as'];
                    } else {
                        Cli::$cli->message('Could not determine install directory (role "horde") for ' . $web_dir . '/' . $file['attribs']['install-as'], 'cli.error');
                        continue 2;
                    }
                    break;

                case 'php':
                    if (isset($file['attribs']['install-as'])) {
                        $dest = $destDir . '/' . $file['attribs']['install-as'];
                    } elseif (isset($file['attribs']['baseinstalldir'])) {
                        $dest = $destDir . $file['attribs']['baseinstalldir'] . '/' . $file['attribs']['name'];
                    } else {
                        $dest = $destDir . '/' . $file['attribs']['name'];
                    }
                    break;

                default:
                    $dest = null;
                    break;
                }

                if (!is_null($dest)) {
                    if (file_exists($dest)) {
                        @unlink($dest);
                    } elseif (!file_exists(dirname($dest))) {
                        @mkdir(dirname($dest), 0777, true);
                    }

                    if (!empty($this->_params['copy'])) {
                        if ($this->_params['debug']) {
                            print 'COPY: ' . $orig . ' -> ' . $dest . "\n";
                        }
                        if (!copy($orig, $dest)) {
                            Cli::$cli->message('Could not link ' . $orig . '.', 'cli.error');
                        }
                    } else {
                        if ($this->_params['debug']) {
                            print 'SYMLINK: ' . $orig . ' -> ' . $dest . "\n";
                        }
                        if (!symlink($orig, $dest)) {
                            Cli::$cli->message('Could not link ' . $orig . '.', 'cli.error');
                        }
                    }
                }
            }
        }
    }

}
