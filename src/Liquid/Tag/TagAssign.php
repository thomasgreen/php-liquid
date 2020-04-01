<?php

/*
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace Liquid\Tag;

use  Liquid\AbstractTag;
use  Liquid\Exception\ParseException;
use  Liquid\Liquid;
use  Liquid\FileSystem;
use  Liquid\Regexp;
use  Liquid\Context;
use  Liquid\Variable;

/**
 * Performs an assignment of one variable to another
 *
 * Example:
 *
 *     {% assign var = var %}
 *     {% assign var = "hello" | upcase %}
 */
class TagAssign extends AbstractTag
{
    /**
     * @var string The variable to assign from
     */
    private $from;
    private $to;
    private $test;
    private $from_2;

    /**
     * Constructor
     *
     * @param string     $markup
     * @param array      $tokens
     * @param FileSystem $fileSystem
     *
     * @throws \Liquid\Exception\ParseException
     */
    public function __construct($markup, array &$tokens, FileSystem $fileSystem = null)
    {

        $syntaxRegexp = new Regexp('/(\w+)\s*=\s*(.*)\s*/');
        $ternaryRegexp = new Regexp('/(\w+)\s*=\s*(.*)\s*\?\s*(.*)\s*:\s*(.*)\s*/');

        if ($ternaryRegexp->match($markup)) {
            $this->to = $ternaryRegexp->matches[1];
            $this->test = $ternaryRegexp->matches[2];
            $this->from = new Variable($ternaryRegexp->matches[3]);
            $this->from_2 = new Variable($ternaryRegexp->matches[4]);
        } elseif ($syntaxRegexp->match($markup)) {
            $this->to = $syntaxRegexp->matches[1];
            $this->from = new Variable($syntaxRegexp->matches[2]);
        } else {
            throw new ParseException("Syntax Error in 'assign' - Valid syntax: assign [var] = [source]");
        }
    }

    /**
     * Renders the tag
     *
     * @param Context $context
     *
     * @return string|void
     */
    public function render(Context $context)
    {
        $multiOperatorRegexp = new Regexp('/(.*)\s(and|or)(?(2)\s*(.*)|)/');
        $operatorRegexp = new Regexp('/(.*)\s*(==|>|>=|<|<=|!=)\s*(.*)/');

        if (isset($this->test)) {
            $testVar = new Variable($this->test);
            $test = $testVar->render($context);

            if ($multiOperatorRegexp->match($this->test)) {
                //we have an operand (&& or ||) lets deal with that
                if ($this->booleanTest($multiOperatorRegexp->matches, $context)) {
                    $output = $this->from->render($context);
                } else {
                    $output = $this->from_2->render($context);
                }
            } elseif ($operatorRegexp->match($this->test)) {
                if ($this->evaluate($this->test, $context)) {
                    $output = $this->from->render($context);
                } else {
                    $output = $this->from_2->render($context);
                }
            } elseif (!empty($test)) {
                $output = $this->from->render($context);
            } else {
                $output = $this->from_2->render($context);
            }
        } else {
            $output = $this->from->render($context);
        }

        $context->set($this->to, $output);
    }

    private function booleanTest(array $matches, Context $context)
    {
        $boolean = $matches[2];
        $leftHand = $this->evaluate($matches[1], $context);
        $rightHand = $this->evaluate($matches[3], $context);
        switch ($boolean) {
            case 'and':
                return $leftHand && $rightHand;
                break;
            case 'or';
                return $leftHand || $rightHand;
                break;
        }
        return false;
    }

    private function evaluate($testString, Context $context)
    {
        $operatorRegexp = new Regexp('/(.*)\s(==|===|>|>=|<|<=|!=|!==)\s*(.*)/');

        //strip out any ( or )
        $testString = trim($testString, '( )');

        if ($operatorRegexp->match($testString)) {
            $leftHand = $operatorRegexp->matches[1];
            $rightHand = $operatorRegexp->matches[3];
            $operator = $operatorRegexp->matches[2];

            $renderFragment = new Regexp(Liquid::get('RENDER_FRAGMENT'));
            if ($rightHand == 'true' || $rightHand == 'false') {
                $rightHand = ($rightHand == 'true');
            } elseif (!$renderFragment->match($rightHand)) {
                $rightHand = $context->get($rightHand);
                if ($rightHand == 'true' || $rightHand == 'false') {
                    $rightHand = ($rightHand == 'true');
                }
            } else {
                $rightHand = trim($rightHand, '\'\|"');
            }

            if ($leftHand == 'true' || $rightHand == 'false') {
                $leftHand = ($leftHand == 'true');
            } elseif (!$renderFragment->match($leftHand)) {
                $leftHand = $context->get($leftHand);
                if ($leftHand == 'true' || $leftHand == 'false') {
                    $leftHand = ($leftHand == 'true');
                }
            } else {
                $leftHand = trim($leftHand, '\'\|"');
            }

            switch ($operator) {
                case '==':
                    return $leftHand == $rightHand;
                    break;
                case '===':
                    return $leftHand === $rightHand;
                    break;
                case '>':
                    return $leftHand > $rightHand;
                    break;
                case '>=':
                    return $leftHand >= $rightHand;
                    break;
                case '<':
                    return $leftHand < $rightHand;
                    break;
                case '<=':
                    return $leftHand <= $rightHand;
                    break;
                case '!=':
                    return $leftHand != $rightHand;
                    break;
                case '!==':
                    return $leftHand !== $rightHand;
                    break;
            }
        } else {
            return !empty($context->get($testString));
        }

    }

}
