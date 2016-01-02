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
            'version_string' => "\${name} \${version}\n",
            'default_action' => function() {}
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
        $this->addOption(
            'help',
            '-h | --help',
            true,
            [
                'help' => 'Print help information.'
            ]
        )->setAction(function() {
            $this->printHelp();
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
     * @param   string              $command        Optional command to print help for.
     */
    public function printHelp($command = null)
    {
        if (is_null($command) || !$this->hasCommand($command)) {
            $instance = $this;
        } else {
            $instance = $this->getCommand($command);
        }

        \Aaparser\Help::printHelp($instance);

        exit(1);
    }

    /**
     * Set default action that is called, when no command line arguments are privided and no
     * error occured after argument processing.
     *
     * @param   callable            $fn             Function to call.
     */
    public function setDefaultAction(callable $fn)
    {
        $this->settings['default_action'] = $fn;
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
        if ($name != 'help' && !$this->hasCommand($name)) {
            // add implicit help command
            $cmd = parent::addCommand(
                'help',
                [
                    'help' => 'Display help for a subcommand.',
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

        $cnt = count($args);

        $args = parent::parse($args);

        if ($cnt == 0) {
            $fn = $this->settings['default_action'];
            $fn();
        }

        if (!is_null($arg = array_shift($args))) {
            printf("too many arguments at \"%s\"\n", $arg);

            exit(1);
        }
    }
}
