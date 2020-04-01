<?php

/*
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace Liquid;

use  AmpClass;
use  Analytics\AnalyticsHook;
use  Cms;

/**
 * A selection of custom filters.
 */
class CustomFilters
{

    /**
     * Sort an array by key.
     *
     * @param array $input
     *
     * @return array
     */
    public static function sort_key(array $input)
    {
        ksort($input);
        return $input;
    }


    public static function localisation()
    {
        $args = func_get_args();
        $input = array_shift($args);
        return \Kohana::lang($input, $args);
    }

    public static function unique($input)
    {
        return $input.md5(uniqid(rand(), true));
    }

    public static function setting($input)
    {
        $regex = new Regexp('/(\w+)(\.)(\w+)/');

        if ($regex->match($input)) {
            $setting = \vsconfig::get_module_setting($regex->matches[1], $regex->matches[3]);
            if (is_null($setting) || empty($setting)) {
                return false;
            }
            return $setting;
        } else {
            return \vsconfig::get($input);
        }
    }

    public static function config($input)
    {
        $regex = new Regexp('/(\w+)(\.)(\w+)/');

        if ($regex->match($input)) {
            $setting = \Kohana::config($regex->matches[1], $regex->matches[3]);
            if (is_null($setting) || empty($setting)) {
                return false;
            }
            return $setting;
        } else {
            return \Kohana::config($input);
        }
    }

    public static function module($input)
    {
        return \vsconfig::get_module($input);
    }

    public static function ampclass($string)
    {
        return AmpClass::clean($string);
    }

    public static function filter($input)
    {
        if (!is_array($input) || empty($input)) {
            return $input;
        }

        $args = func_get_args();
        array_shift($args);

        $filterCriteria = [];

        for ($i = 0; $i < count($args); $i++) {
            if ($i % 2 != 0) {
                $filterCriteria[$args[$i-1]] = $args[$i];
            }
        }

        foreach ($filterCriteria as $key => $value) {
            $input = array_filter($input, function ($var) use ($key, $value) {
                if (!isset($var[$key]) || empty($var[$key])) {
                    return false;
                }
                return ($var[$key] == $value);
            });

            if (empty($input)) {
                return $input;
            }
        }
        return $input;
    }

    public static function analyticshook($string)
    {
        return AnalyticsHook::getHookData($string);
    }

    public static function cms_style($string)
    {
        $isPTag = preg_match_all('/<(?:\/?(?:p))[^>]*>\s*/', $string);
        $dom = new \DOMDocument;
        $dom->loadHTML(mb_convert_encoding($string, 'HTML-ENTITIES', 'UTF-8'));
        Cms::applyCmsStyle($dom);
        $regex = '~<(?:!DOCTYPE|/?(?:html|head|body|p))[^>]*>\s*~i';
        if ($isPTag) {
            $regex = '~<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>\s*~i';
        }

        $html = preg_replace($regex, '', $dom->saveHTML());

        return $html;
    }


}
