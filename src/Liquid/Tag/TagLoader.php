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

use  Icon;
use  Liquid\Document;
use  Liquid\Context;
use  Liquid\Exception\ParseException;
use  Liquid\Liquid;
use  Liquid\FileSystem;
use  Liquid\Regexp;


class TagLoader extends TagInclude
{
    /**
     * @var string The name of the template
     */
    private $templateName;

    /**
     * @var bool True if the variable is a collection
     */
    private $collection;

    /**
     * @var mixed The value to pass to the child template as the template name
     */
    private $variable;

    /**
     * @var Document The Document that represents the included template
     */
    private $document;

    /**
     * @var string The Source Hash
     */
    protected $hash;
    /**
     * @var bool
     */
    private $dynamic;

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
        $regex = new Regexp('/("[^"]+"|\'[^\']+\'|[^\'"\s]+)(\s+(with|for)\s+('.Liquid::get('QUOTED_FRAGMENT').'+))?/');

        if (!$regex->match($markup)) {
            throw new ParseException("Error in tag 'icon' - Valid syntax: include '[template]' (with|for) [object|collection]");
        }
        $unquoted = (strpos($regex->matches[1], '"') === false && strpos($regex->matches[1], "'") === false);
        $start = 1;
        $len = strlen($regex->matches[1]) - 2;
        $this->dynamic = false;
        if ($unquoted) {
            $start = 0;
            $len = strlen($regex->matches[1]);
            $this->dynamic = true;
        }

        $this->templateName = substr($regex->matches[1], $start, $len);

        if (isset($regex->matches[1])) {
            $this->collection = (isset($regex->matches[3])) ? ($regex->matches[3] == "for") : null;
            $this->variable = (isset($regex->matches[4])) ? $regex->matches[4] : null;
        }

        $this->extractAttributes($markup);
        parent::__construct($markup, $tokens, $fileSystem);

    }


    public function parse(array &$tokens)
    {
        return;
    }

    /**
     * Renders the node
     *
     * @param Context $context
     *
     * @return string
     */
    public function render(Context $context)
    {
        $this->renderAttributes($this->markup, $context);
        $this->templateName = \Kohana::config('icons.loader');

        $this->attributes['class'] = $this->templateName;

        return Icon::render('loading/'.$this->templateName, $this->attributes);
    }
}
