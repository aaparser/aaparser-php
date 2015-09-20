<?php

/*
 * This file is part of the octris/aaparser.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aaparser;

require_once(__DIR__ . '/Help.php');

/**
 * Argument parser main class.
 *
 * @copyright   copyright (c) 2015 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>>
 */
class Args extends \Aaparser\Command
{
    /**
     * Constructor.
     *
     * @param   string          $name           Name of application.
     * @param   array           $settings       Optional settings.
     */
    public function __construct($name, array $settings = array())
    {
        $settings = $settings + [
            'name' => $name,
            'version' => '0.0.0',
            'version_string' => "\${name} \${version}\n"
        ];

        parent::__construct($name, null, $settings);

        $this->addOption(
            'version',
            '--version',
            true,
            [
                'help' => 'Print version info.'
            ]
        )->setAction(function() {
            $this->printVersion();
        });
    }

    /**
     * Setter for the application version.
     *
     * @param   string          $str        Version number.
     * @return  \Aaparser\Args              Returns class instance.
     */
    public function setVersion($str)
    {
        $this->settings['version'] = $str;

        return $this;
    }

    /**
     * Return application version.
     *
     * @return  string                      Application version.
     */
    public function getVersion()
    {
        return $this->settings['version'];
    }

    /**
     * Print version.
     */
    public function printVersion()
    {
        print preg_replace_callback('/\${(.+?)}/', function($match) {
            if (isset($this->settings[$match[1]])) {
                $return = $this->settings[$match[1]];
            } else {
                $return = '${' . $match[1] . '}';
            }

            return $return;
        }, $this->settings['version_string']);
    }

    /**
     * Print help.
     *
     * @param   string          $command        Optional command to print help for.
     */
    public function printHelp($command = '')
    {
        \Aaparser\Help::printHelp($command);

        exit(1);
    }

    /**
     * Define a new command.
     *
     * @param   string              $name           Name of command.
     * @param   array               $settings       Optional additional settings.
     * @return  \Aaparser\Command                   Instance of new command object.
     */
    public function addCommand($name, array $settings = array())
    {
        if ($name != 'help') {
            // add implicit help command
            $cmd = parent::addCommand(
                'help',
                [
                    'help' => 'Help',
                    'action' => function(array $options, array $operands) {
                        if (isset($operands['command'])) {
                            // resolve actual command
                        }
                    }
                ]
            );
            $cmd->addOperand(
                'command',
                '*',
                [
                    'help' => 'Command to get help for.'
                ]
            );
        }

        return parent::addCommand($name, $settings);
    }

    /**
     * Parse arguments for command. Uses '$argv' if no parameter is specified.
     *
     * @param   array            $args       Optional array of arguments.
     */
    public function parse(array $args = null)
    {
        global $argv;

        if (is_null($args)) {
            $args = array_slice($argv, 1);
        }

        $args = parent::parse($args);

        if (!is_null($arg = array_shift($args))) {
            print 'too many arguments for "' . $arg . '"';

            exit(1);
        }
    }
}
