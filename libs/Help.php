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
 * Help system for aaparser.
 *
 * @copyright   copyright (c) 2015 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class Help
{
    /**
     * Build usage information for options.
     */
    protected static function getOptionUsage($option)
    {
        $usage = implode(' | ', $option->getFlags());

        if ($option->isRequired()) {
            if (count($option->getFlags()) > 0) {
                $ch = ['(', ')'];
            } else {
                $ch = ['', ''];
            }
        } else {
            $ch = ['[', ']'];
        }

        if ($option->takesValue()) {
            $usage .= ' <' . $option->getVariable() . '>';
        }

        return $ch[0] . $usage . $ch[1];
    }

    /**
     * Build usage information for operands
     */
    protected static function getOperandUsage($operand)
    {
        $usage = [];
        $minmax = $operand->getExpected();

        if ($minmax[0] > 0) {
            $usage[] = implode(
                ' ',
                array_fill(
                    0,
                    $minmax[0],
                    '<' . $operand->getVariable() . '>'
                )
            );
        }

        if ($minmax[1] == INF) {
            $usage[] = '[' . $operand->getVariable() . ' ...]';
        } elseif ($minmax[0] == 0) {
            $usage[] = '[' . $operand->getVariable() . ']');
        }

        return implode(' ', $usage);
    }

    /**
     * Build usage information for command.
     */
    protected static function getUsage($command)
    {
        $usage = [];

        foreach ($command->getOptions() as $option) {
            $usage[] = self::getOptionUsage($option);
        }

        foreach ($command->getOperands() as $operand) {
            $usage[] = self::getOperandUsage($operand);
        }

        if ($command->hasCommands()) {
            $usage[] = '<command> [ARGUMENTS]';
        }

        return $usage;
    }

    /**
     * Print help.
     *
     * @param   Command             $command            Command to print help for.
     */
    public static function printHelp($command)
    {
        // collect

        // render usage summary
        $cmd = $command;
        $tree = []

        do {
            array_unshift($tree, $cmd->getName());

            $cmd = $cmd->getParent();
        } while (!is_null($cmd));

        $usage = self::getUsage(command);
        $buffer = rtrim('usage: ' . array_shift($tree) . ' ' . implode(' [ARGUMENTS] ', $tree)) . ' ';
        $len = strlen($buffer);

        foreach ($usage as $u) {
            if (strlen($buffer) + strlen($u) <= 78 || strlen($buffer) == $len) {
                $buffer .= $u .' ';
            } else {
                print $buffer . "\n";
                
                $buffer = str_repeat(' ', $len) . $u . ' ';
            }
        }
        
        if (strlen($buffer) > $len) {
            print $buffer . "\n";
        }

        // render lists of available options, operands and subcommands
        $indent = str_repeat(' ', 10);

        if ($command->hasOptions() || $command->hasOperands() || $command->hasCommands()) {
            print "\n";
        }

        if ($command.hasOptions()) {
            print "Options:\n";

            foreach ($commands->getOptions() as $option) {
                print "    " . implode(' | ', $option->getFlags()) . "\n";
                print $indent . rtrim(wordwrap($option->getHelp(), 78, "\n" . $indent)) . "\n";
            });
        }

        if ($command.hasOperands()) {
            print "Operands:\n";

            foreach ($command->getOperands() as $operand) {
                print "    " . $operand->getName()) . "\n";
                print $indent . rtrim(wordwrap($operand->getHelp(), 78, "\n" . $indent)) . "\n";
            }
        }

        if ($command->hasCommands()) {
            print "Commands:\n";

            $commands = [];
            $size = array_reduce(
                $command->getCommands(),
                function($size, $cmd) use (&$commands) {
                    $name = $cmd->getName();
                    
                    $commands[$name] = $cmd;
                    
                    return Math.max($size, strlen($name));
                },
                0
            );

            ksort($commands);
            
            foreach ($commands as $name => $command) {
                printf("    %-" . $size . "%    %s\n", $name, $command->getHelp());
            }
        }
    }
}
