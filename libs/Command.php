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

/**
 * Command class.
 *
 * @copyright   copyright (c) 2015 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class Command {
    /**
     * Name of command.
     *
     * @type    string
     */
    protected $name;

    /**
     * Settings of command.
     *
     * @type    array
     */
    protected $settings;

    /**
     * Sub-commands.
     *
     * @type    array
     */
    protected $commands = array();

    /**
     * Command options.
     *
     * @type    array
     */
    protected $options = array();

    /**
     * Command operands.
     *
     * @type    array
     */
    protected $operands = array();

    /**
     * Parent command.
     *
     * @type    \Aaparser\Command
     */
    protected $parent;

    /**
     * Constructor.
     *
     * @param   string                   $name           Name of command.
     * @param   \Aaparser\Command|null   $parent         Parent command, if there is any.
     * @param   array                    $settings       Optional additional settings.
     */
    public function __construct($name, $parent, array $settings = array())
    {
        $this->name = $name;
        $this->settings = $settings;

        $this->parent = $parent;
    }

    /**
     * Set help text.
     *
     * @param   string      $str            Help text.
     */
    public function setHelp($str)
    {
        $this->settings['help'] = $str;
    }

    /**
     * Return help text.
     *
     * @return  string                      Help text.
     */
    public function getHelp()
    {
        return (isset($this->settings['help'])
                ? $this->settings['help']
                : '');
    }

    /**
     * Return command name.
     *
     * @return  string                      Name of command.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return parent command or null if there is no parent command.
     *
     * @return  \Aaparser\Command|null   Parent command.
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set action to call if command appears in arguments.
     *
     * @param   callable    $cb             Callback to call.
     */
    public function setAction(callable $cb)
    {
        $this->settings['action'] = $cb;
    }

    /**
     * Define a new command.
     *
     * @param   string      $name           Name of command.
     * @param   array       $settings       Optional additional settings.
     * @return  \Aaparser\Command    Instance of new object.
     */
    public function addCommand($name, array $settings = array())
    {
        $instance = new Command($name, $this, $settings);

        $this->commands[$name] = $instance;

        return $instance;
    }

    /**
     * Create a new option for command.
     *
     * @param   string              $name           Internal name of option.
     * @param   string              $flags          Option flags.
     * @param   callable|bool       $coercion       Either a coercion callback or a fixed value.
     * @param   array               $settings       Optional additional settings.
     * @return  \Aaparser\Option                    Instance of created option.
     */
    public function addOption($name, $flags, $coercion, array $settings = array())
    {
        $instance = new \Aaparser\Option($name, $flags, $coercion, $settings);

        $this->options[] = $instance;

        return $instance;
    }

    /**
     * Create a new operand (positional argument).
     *
     * @param   string              $name           Internal name of operand.
     * @param   int|string          $num            Number of arguments.
     * @param   array               $settings       Optional additional settings.
     * @return  \Aaparser\Operand                   Instance of created operand.
     */
    public function addOperand($name, $num, array $settings = array())
    {
        $instance = new \Aaparser\Operand($name, $num, $settings);

        $this->operands[] = $instance;

        return $instance;
    }

    /**
     * Test if command has subcommands.
     *
     * @return  bool                        Returns true, if command has subcommands.
     */
    public function hasCommands()
    {
        return (count($this->commands) > 0);
    }

    /**
     * Test if command with specified name exists.
     *
     * @param   string                  $name           Name of command.
     * @return  bool                                    Returns true, if specified command exists.
     */
    public function hasCommand($name)
    {
        return (isset($this->commands[$name]));
    }

    /**
     * Return all defined subcommands.
     *
     * @return  array                       Commands.
     */
    public function getCommands()
    {
        return array_values($this->commands);
    }

    /**
     * Lookup a defined command.
     *
     * @param   string                  $name           Name of command.
     * @return  \Aaparser\Command|bool                  Returns the command instance or 'false' if no command was found.
     */
    public function getCommand($name)
    {
        return (isset($this->commands[$name])
                ? $this->commands[$name]
                : false);
    }

    /**
     * Test if command has defined operands.
     *
     * @return  bool                            Returns true, if command has operands.
     */
    public function hasOperands()
    {
        return (count($this->operands) > 0);
    }

    /**
     * Return all defined operands.
     *
     * @return  array                           Operands.
     */
    public function getOperands()
    {
        return $this->operands;
    }

    /**
     * Test if command has defined options.
     *
     * @return  bool                            Returns true, if command has options.
     */
    public function hasOptions()
    {
        return (count($this->options) > 0);
    }

    /**
     * Return all defined options.
     *
     * @return  array                           Options.
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Lookup a defined option for a specified flag.
     *
     * @param   string                  $flag       Option flag to lookup.
     * @return  \Aaparser\Option|bool               Returns the option instance or 'false' if no option was found.
     */
    public function getOption($flag)
    {
        $instance = false;

        foreach ($this->options as $option) {
            if ($option->isFlag($flag)) {
                $instance = $option;
                break;
            }
        }

        return $instance;
    }

    /**
     * Return min/max expected number of operands.
     *
     * @return  array                           Min, max expected number of operands.
     */
    public function getMinMaxOperands()
    {
        $min = 0;
        $max = 0;

        foreach ($this->operands as $operand) {
            $mm = $operand->getExpected();

            $min += $mm[0];

            if ($max !== INF) {
                if ($mm[1] === INF) {
                    $max = INF;
                } else {
                    $max += $mm[1];
                }
            }
        }

        return [$min, $max];
    }

    /**
     * Get remaining minimum number of operands expected.
     *
     * @param   int         $n              Number of operand to begin with to calculate remaining minimum expected operands.
     * @return  int                         Expected minimum remaining operands.
     */
    public function getMinRemaining($n)
    {
        $return = 0;
        $operands = array_slice($this->operands, $n);

        foreach ($operands as $operand) {
            $return += $operand->getExpected()[0];
        }
    }

    /**
     * Process and validate operands.
     *
     * @param   array           &$args          Operandal arguments to validate.
     * @return  object                          Validated arguments.
     */
    public function processOperands(array &$args)
    {
        $operand = null;
        $ret = [];
        $op = 0;
        $minmax = $this->getMinMaxOperands();

        if ($minmax[0] > count($args)) {
            printf("not enough arguments -- available %d, expected %d\n", count($args), $minmax[0]);
            exit(1);
        } elseif ($minmax[1] !== INF && $minmax[1] < count($args)) {
            printf("too many arguments -- available %d, expected %d\n", count($args), $minmax[1]);
            exit(1);
        }

        while (count($args) > 0) {
            if (is_null($operand)) {
                // fetch next operand
                $operand = $this->operands[$op];

                $minmax = $operand->getExpected();
                $name = $operand->getName();

                ++$op;

                $remaining = $this->getMinMaxRemaining($op);
            }

            $cnt = (isset($ret[$name])
                    ? count($ret[$name])
                    : 0);

            if ($minmax[1] > $cnt || ($minmax[1] === INF && $remaining > count($args))) {
                // expected operand
                $arg = array_shift($args);

                if (!$operand->isValid($arg)) {
                    printf("invalid value \"%s\" for operand\n", $arg);
                    exit(1);
                }

                $operand->update($arg);

                $ret[$name] = $operand->getData();
            } else {
                // trigger fetching next operand
                $operand = null;
            }
        }

        return $ret;
    }

    /**
     * Parse arguments for command.
     *
     * @param   array            $args       Optional array of arguments.
     */
    public function parse(array $args = null)
    {
        $pargs = [];
        $options = [];
        $operands = [];
        $literal = false;
        $subcommand = null;

        array_map(function($option) use (&$options) {
            $data = $option->getData();

            if (!is_null($data)) {
                $options[$option->getName()] = $data;
            }
        }, $this->options);

        $mm = $this->getMinMaxOperands();

        while (($arg = array_shift($args))) {
            if ($literal) {
                $pargs[] = $arg;
                continue;
            }

            if ($arg == '--') {
                // only operands following
                $literal = true;
                continue;
            }

            if (preg_match('/^(-[a-z0-9])([a-z0-9]*)()$/i', $arg, $match) || preg_match('/^(--[a-z][a-z0-9-]*)()(=.*|)$/i', $arg, $match)) {
                // option argument
                if ($match[3] !== '') {
                    // push back value
                    array_unshift($args, substr($match[3], 1));
                }

                if (!($option = $this->getOption($match[1]))) {
                    printf("unknown argument \"%s\"\n", $match[1]);
                    exit(1);
                }

                if ($option->takesValue()) {
                    if (($arg = array_shift($args))) {
                        // value required
                        if (!$option->isValid($arg)) {
                            printf("invalid value for argument \"%s\"\n", $match[1]);
                            exit(1);
                        } else {
                            $option->update($arg);
                            $option->callAction($arg);
                        }
                    } else {
                        printf("value missing for argument \"\"\n", $match[1]);
                        exit(1);
                    }
                } else {
                    $option->update();
                    $option->callAction();
                }

                $options[$option->getName()] = $option->getData();

                if ($match[2] !== '') {
                    // push back combined short argument
                    array_unshift($args, '-' . $match[2]);
                }
            } elseif (count($pargs) < $mm[1]) {
                // expected operand
                $pargs[] = $arg;
            } elseif ($this->hasCommand($arg)) {
                // sub command
                $subcommand = $this->getCommand($arg);
                break;
            } else {
                // no further arguments should be parsed
                array_unshift($args, $arg);
                break;
            }
        }

        // check if all required options are available
        foreach ($this->options as $option) {
            if ($option->isRequired() && !isset($options[$option->getName()])) {
                printf("required argument is missing \"%s\"\n", implode(' | ', $option->getFlags()));
                exit(1);
            }
        }

        // parse operands
        $operands = $this->processOperands($pargs);

        // action callback for command
        if (isset($this->settings['action']) && is_callable($this->settings['action'])) {
            $this->settings['action']($options, $operands);
        }

        // there's a subcommand to be called
        if (!is_null($subcommand)) {
            do {
                $args = $subcommand->parse($args);

                if (($arg = array_shift($args))) {
                    if (!($subcommand = $this->getCommand($arg))) {
                        // argument does not belong to a subcommand registered at this level
                        array_unshift($args, $arg);
                        break;
                    }
                } else {
                    // no more arguments
                    break;
                }
            } while(true);
        }

        return $args;
    }
}
