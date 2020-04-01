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

use arr;
use Kohana;
use Router;
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
use  Page;
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
class TagLayout extends AbstractBlock
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



    private function getDocument()
    {
        $source = $this->fileSystem->readTemplateFile($this->templateName, false, 'layout');
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
        $this->renderAttributes($this->markup, $context);
        $this->templateName = $this->getFirstAttribute($this->markup, $context);
        $this->templateName = !empty($this->templateName) ? $this->templateName : Kohana::config('liquid_layouts.default');

        $globalData = Page::getData($this->templateName);

        if (!empty($globalData['layout'])) {
            $this->templateName = $globalData['layout'];
        }

        $this->getDocument();

        foreach ($globalData as $key => $value) {
            $context->set($key, $value, true);
        }

        $mainView = $this->renderAll($this->nodelist, $context);
        $context->set('mainView', $mainView);

        $result = $this->document->render($context);
        return $result;
    }
}
