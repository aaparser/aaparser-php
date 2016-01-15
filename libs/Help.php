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
     * Wordwrap wrapper to handle paragraphs.
     * 
     * @param   string              $str                    The input string.
     * @param   int                 $width                  The number of characters at which the string will be wrapped.
     * @param   string              $indent                 Indenting width.
     */
    protected static function wordwrap($str, $width, $indent)
    {
        $indent = str_repeat(' ', $indent);
        $paragraphs = [];
        
        foreach (explode("\n", $str) as $paragraph) {
            $paragraphs[] = wordwrap($indent . $paragraph, $width, "\n" . $indent);
        }
        
        return rtrim(implode("\n", $paragraphs));
    }
    
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
            $usage[] = '[' . $operand->getVariable() . ']';
        }

        return implode(' ', $usage);
    }

    /**
     * Build usage information for command.
     */
    protected static function getUsage($command)
    {
        $usage = [];

        $options = $command->getOptions();
        usort($options, function($a, $b) {
            return strcasecmp(ltrim($a->getFlags()[0], '-'), ltrim($b->getFlags()[0], '-'));
        });

        foreach ($options as $option) {
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
        // render usage summary
        $cmd = $command;
        $tree = [];

        $help = $command->getHelp();
        
        print "Command:\n    " . rtrim(wordwrap($command->getName() . ($help !== '' ? ' -- ' . $help : ''), 78, "\n    ")) . "\n\n";

        do {
            array_unshift($tree, $cmd->getName());

            $cmd = $cmd->getParent();
        } while (!is_null($cmd));

        $usage = self::getUsage($command);
        
        print "Usage:\n";
        
        $buffer = rtrim('    ' . array_shift($tree) . ' ' . implode(' [ARGUMENTS] ', $tree)) . ' ';
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

        if (($description = $command->getDescription()) !== '') {
            print "\nDescription:\n" . rtrim(self::wordwrap($description, 78, 4)) . "\n";
        }

        // render lists of available options, operands and subcommands
        $indent = str_repeat(' ', 10);

        if ($command->hasOptions()) {
            print "\nOptions:\n";

            $options = $command->getOptions();
            usort($options, function($a, $b) {
                return strcasecmp(ltrim($a->getFlags()[0], '-'), ltrim($b->getFlags()[0], '-'));
            });

            foreach ($options as $option) {
                print "    " . implode(' | ', $option->getFlags()) . "\n";
                print $indent . rtrim(wordwrap($option->getHelp(), 78, "\n" . $indent)) . "\n";
            }
        }

        if ($command->hasOperands()) {
            print "\nOperands:\n";

            foreach ($command->getOperands() as $operand) {
                print "    " . $operand->getName() . "\n";
                print $indent . rtrim(wordwrap($operand->getHelp(), 78, "\n" . $indent)) . "\n";
            }
        }

        if ($command->hasCommands()) {
            print "\nCommands:\n";

            $commands = [];
            $size = array_reduce(
                $command->getCommands(),
                function($size, $cmd) use (&$commands) {
                    $name = $cmd->getName();

                    $commands[$name] = $cmd;

                    return max($size, strlen($name));
                },
                0
            );

            ksort($commands);

            foreach ($commands as $name => $command) {
                printf("    %-" . $size . "s    %s\n", $name, $command->getHelp());
            }
        }
        
        if (($example = $command->getExample()) !== '') {
            print "\nExample:\n" . rtrim(self::wordwrap($example, 78, 4)) . "\n";
        }
    }
}
