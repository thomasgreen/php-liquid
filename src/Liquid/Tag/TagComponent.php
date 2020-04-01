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

use  Component;
use  Liquid\AbstractBlock;
use  Liquid\AbstractTag;
use  Liquid\Document;
use  Liquid\Context;
use  Liquid\Exception\MissingFilesystemException;
use  Liquid\Exception\ParseException;
use  Liquid\IncludePath;
use  Liquid\Liquid;
use  Liquid\LiquidException;
use  Liquid\FileSystem;
use  Liquid\Regexp;
use  Liquid\Template;
use  Palette;

/**
 * Includes another, partial, template
 *
 * Example:
 *
 *     {% include 'foo' %}
 *
 *     Will include the template called 'foo'
 *
 *     {% include 'foo' with 'bar' %}
 *
 *     Will include the template called 'foo', with a variable called foo that will have the value of 'bar'
 *
 *     {% include 'foo' for 'bar' %}
 *
 *     Will loop over all the values of bar, including the template foo, passing a variable called foo
 *     with each value of bar
 */
class TagComponent extends AbstractBlock
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
    private $requiresDynamic;

    /**
     * TagComponent constructor.
     *
     * @param                                $markup
     * @param array                          $tokens
     * @param \Vs\Amp\Liquid\FileSystem|null $fileSystem
     *
     * @throws \Liquid\Exception\ParseException
     * @throws \Vs\Amp\Liquid\Exception\ParseException
     */
    public function __construct($markup, array &$tokens, FileSystem $fileSystem = null)
    {
        $this->templateName = '';
        $this->fileSystem = $fileSystem;
        $regex = new Regexp('/("[^"]+"|\'[^\']+\'|[^\'"\s]+)(\s+(with|for)\s+('.Liquid::get('QUOTED_FRAGMENT').'+))?/');


        if (!$regex->match($markup)) {
            throw new ParseException("Error in tag 'include' - Valid syntax: include '[template]' (with|for) [object|collection]");
        }

        $unquoted = (strpos($regex->matches[1], '"') === false && strpos($regex->matches[1], "'") === false);

        $start = 1;
        $len = strlen($regex->matches[1]) - 2;

        $this->requiresDynamic = false;
        if ($unquoted) {
            $start = 0;
            $len = strlen($regex->matches[1]);
            $this->requiresDynamic = true;
            $this->templateName = substr($regex->matches[1], $start, $len);
        } else {
            $templateName = substr($regex->matches[1], $start, $len);
            $this->templateName = $templateName; //Component::getComponentType($templateName).'/'.$templateName;
        }

        if (isset($regex->matches[1])) {
            $this->collection = (isset($regex->matches[3])) ? ($regex->matches[3] == "for") : null;
            $this->variable = (isset($regex->matches[4])) ? $regex->matches[4] : null;
        }

        $this->extractAttributes($markup);
        parent::__construct($markup, $tokens, $fileSystem);

    }


    public function parse(array &$tokens)
    {
        if (!$this->requiresDynamic) {
            $this->getDocument();
        }
        parent::parse($tokens);

    }
    private function getDocument()
    {
        $source = $this->fileSystem->readTemplateFile($this->templateName);
        $templateTokens = Template::tokenize($source);
        $this->document = new Document($templateTokens, $this->fileSystem);
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
        $context->push();
        $this->renderAttributes($this->markup, $context);
        $templateName = $this->getFirstAttribute($this->markup, $context);
        $this->templateName = $templateName; //Component::getComponentType($templateName).'/'.$templateName;
        $this->getDocument();

        $result = '';
        $variable = $context->get($this->variable);

        foreach ($this->attributes as $key => $value) {
            $context->set($key, $value);
        }
        IncludePath::pushStack(basename($this->templateName));
        $context->set('slot', $this->renderAll($this->nodelist, $context));

        if ($this->collection) {
            foreach ($variable as $item) {
                $context->set($this->templateName, $item);
                $result .= $this->document->render($context);
            }
        } else {
            if (!is_null($this->variable)) {
                $context->set($this->templateName, $variable);
            }
            $result .= $this->document->render($context);
        }

        IncludePath::popStack();
        $context->pop();

        return $result;
    }
}
