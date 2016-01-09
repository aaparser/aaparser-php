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
 * Operand class.
 *
 * @copyright   copyright (c) 2015 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class Operand
{
    /**
     * Operand settings.
     *
     * @type    array
     */
    protected $settings;

    /**
     * Operand index.
     *
     * @type    int
     */
    protected $index = 0;

    /**
     * Operand data.
     *
     * @type    mixed
     */
    protected $data;

    /**
     * Operand validators.
     *
     * @type    array
     */
    protected $validators = array();

    /**
     * Number of arguments.
     *
     * @type    int|string
     */
    protected $num;

    /**
     * Constructor.
     *
     * @param   string          $name           Internal name of operand.
     * @param   int|string      $num            Number of arguments.
     * @param   array           $settings       Optional additional settings.
     */
    public function __construct($name, $num, array $settings = array())
    {
        $this->settings = $settings + [
            'variable' => $name,
            'default' => [],
            'help' => '',
            'action' => function() {}
        ];

        if (is_int($num)) {
            $this->num = $num;
        } elseif ($num == '?' || $num == '*' || $num == '+') {
            $this->num = $num;
        } else {
            throw new \Exception('either an integer > 0 or one of the characters \'?\', \'*\' or \'+\' are required as second parameter. Input was: ' . $num);
        }

        $this->name = $name;
        $this->data = $this->settings['default'];
    }

    /**
     * Set help text.
     *
     * @param   string          $str            Help text.
     * @return  \Aaparser\Operand               Instance for method chaining.
     */
    public function setHelp($str)
    {
        $this->settings['help'] = $str;

        return $this;
    }

    /**
     * Return help text.
     *
     * @return  string                          Help text.
     */
    public function getHelp()
    {
        return $this->settings['help'];
    }

    /**
     * Return operand name.
     *
     * @return  string                          Name of operand.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get variable of option.
     *
     * @return  string                          Variable.
     */
    public function getVariable()
    {
        return $this->settings['variable'];
    }

    /**
     * Return min/max number of operands this instance matches.
     *
     * @return  array                           Min, max number of operands.
     */
    public function getExpected()
    {
        if ($this->num === '?') {
            $ret = [0, 1];
        } elseif ($this->num === '*') {
            $ret = [0, INF];
        } elseif ($this->num === '+') {
            $ret = [1, INF];
        } else {
            $ret = [$this->num, $this->num];
        }

        return $ret;
    }

    /**
     * Add a value validator. This has only effect for options that require a value.
     *
     * @param   callable        $cb             Validation callback.
     * @param   string          $errstr         Optional error string to print if validation fails.
     * @return  \Aaparser\Operand               Instance for method chaining.
     */
    public function addValidator($cb, $errstr = '')
    {
        $this->validators[] = [
            'fn' => $cb,
            'errstr' => $errstr
        ];

        return $this;
    }

    /**
     * Validate the value of an operand.
     *
     * @param   mixed           $value          Value to validate.
     * @return  array                           Returns an array in the form [is_valid, errstr].
     */
    public function isValid($value)
    {
        $return = [true, ''];

        foreach ($this->validators as $validator) {
            if (!$validator['fn']($value)) {
                $return = [false, $validator['errstr']];
                break;
            }
        }

        return $return;
    }

    /**
     * Return stored data up to the current index.
     *
     * @return  mixed                           Data of option.
     */
    public function getData()
    {
        return array_slice($this->data, 0, $this->index);
    }

    /**
     * Update operand data.
     *
     * @param   mixed           $value          Value to add.
     */
    public function update($value)
    {
        ++$this->index;

        if ($this->index > count($this->data)) {
            $this->data[] = $value;
        } else {
            // overwrite default value
            $this->data[$this->index - 1] = $value;
        }
    }
}
