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
 * Option class.
 *
 * @copyright   copyright (c) 2015 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class Option
{
    /**
     * Name of option
     *
     * @type    string
     */
    protected $name;

    /**
     * Data of option.
     *
     * @type    mixed
     */
    protected $data = null;

    /**
     * Option flags.
     *
     * @type    array
     */
    protected $flags = array();

    /**
     * Option coercion.
     *
     * @type    mixed
     */
    protected $coercion;

    /**
     * Variable name of option.
     *
     * @type    mixed
     */
    protected $variable = null;

    /**
     * Option validators.
     *
     * @type    array
     */
    protected $validators = array();

    /**
     * Option settings.
     *
     * @type    array
     */
    protected $settings;

    /**
     * Constructor.
     *
     * @param   string          $name           Name of option.
     * @param   string          $flags          Option flags.
     * @param   callable|mixed  $coercion       Either a coercion callback or a fixed value.
     * @param   array           $settings       Optional additional settings.
     */
    public function __construct($name, $flags, $coercion, array $settings = array())
    {
        $this->settings = $settings + [
            'variable' => null,
            'default' => null,
            'help' => '',
            'required' => false,
            'action' => function() {}
        ];

        $this->name = $name;
        $this->coercion = $coercion;

        foreach (preg_split('/[, |]+/', $flags) as $part) {
            if (preg_match('/^-[a-z0-9]$/i', $part)) {
                $this->flags[] = $part;
            } elseif (preg_match('/^--[a-z][a-z0-9-]+$/i', $part)) {
                $this->flags[] = $part;
            } elseif (preg_match('/^<([^>]+)>$/', $part, $match)) {
                $this->variable = (!is_null($this->settings['variable'])
                                    ? $this->settings['variable']
                                    : $match[1]);
            } else {
                throw new \Exception('unexpected string "' . $part . '"');
            }
        }
    }

    /**
     * Set help text.
     *
     * @param   string          $str            Help text.
     * @return  \Aaparser\Option                Instance for method chaining.
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
     * Return the flags the option corresponds to.
     *
     * @return  array                           Corresponding flags.
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * Get variable of option.
     *
     * @return  string                          Variable.
     */
    public function getVariable()
    {
        return $this->variable;
    }

    /**
     * Return option name.
     *
     * @return  string                          Name of option.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set action to call if option appears in arguments.
     *
     * @param   callable        $cb             Callback to call.
     * @return  \Aaparser\Option                Instance for method chaining.
     */
    public function setAction($cb)
    {
        $this->settings['action'] = $cb;

        return $this;
    }

    /**
     * Call action callback.
     *
     * @param   mixed           $value          Optional value for action callback.
     */
    public function callAction($value = null)
    {
        $this->settings['action']($value);
    }

    /**
     * Add a value validator. This has only effect for options that require a value.
     *
     * @param   callable        $cb             Validation callback.
     * @param   string          $errstr         Optional error string to print if validation fails.
     * @return  \Aaparser\Option                Instance for method chaining.
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
     * Whether the option is required.
     *
     * @return  bool                            Returns true if the option is required.
     */
    public function isRequired()
    {
        return !!$this->settings['required'];
    }

    /**
     * Test if specified flag represents the option.
     *
     * @param   string          $flag           Flag name.
     * @return  bool                            Returns true if the option is represented by the flag.
     */
    public function isFlag($flag)
    {
        return (in_array($flag, $this->flags));
    }

    /**
     * Validate the value of an option.
     *
     * @param   mixed           $value          Value to validate.
     * @return  array                           Returns an array in the form [is_valid, errstr].
     */
    public function isValid($value)
    {
        $return = [true, ''];

        foreach ($this->validators as $validator) {
            if (!$validator($value)) {
                $return = [false, $validator['errstr']];
                break;
            }
        }

        return $return;
    }

    /**
     * Returns true if the option is expected to take a value.
     *
     * @return  bool                            Returns true or false.
     */
    public function takesValue()
    {
        return (!is_null($this->variable));
    }

    /**
     * Return stored data.
     *
     * @return  mixed                           Data of option.
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Update option value.
     *
     * @param   mixed           $value          Optional value to set (ignored for 'type' == 'count' and 'type' == 'bool').
     */
    public function update($value = null)
    {
        if (is_callable($this->coercion)) {
            $cb = $this->coercion;
            $this->data = $cb($value, $this->data, $this->settings['default']);
        } else {
            $this->data = $this->coercion;
        }
    }
}
