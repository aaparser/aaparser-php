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
 * Class with default coercion functions.
 *
 * @copyright   copyright (c) 2015 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class Coercion
{
    /**
     * Build a collection (array) of values.
     */
    public static function collect($value, array $collection = array())
    {
        $collection[] = $value;

        return $collection;
    }

    /**
     * Increase a counter.
     */
    public static function count($value, $total = 0) {
        return $total += $value;
    }

    /**
     * Build a key/value with values provided.
     */
    public static function kv($value, array $collection = null) {
        if (is_null($collection)) {
            $collection = array();
        }
        
        $kv = explode('=', $value);

        $collection[$kv[0]] = $kv[1];

        return $collection;
    }

    /**
     * Split a value by ','.
     */
    public static function listing($value) {
        return preg_split('/\s*,\s*/', $value);
    }

    /**
     * Build a range of numbers.
     */
    public static function range($value) {
        $lh = explode('..', $value);
        
        return range($lh[0], $lh[1]);
    }

    /**
     * Just store the value.
     */
    public static function value($value) {
        return $value;
    }
}
