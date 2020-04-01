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

/**
 * Base class for tags.
 */
abstract class AbstractTag
{
	/**
	 * The markup for the tag
	 *
	 * @var string
	 */
	protected $markup;

	/**
	 * Filesystem object is used to load included template files
	 *
	 * @var FileSystem
	 */
	protected $fileSystem;

	/**
	 * Additional attributes
	 *
	 * @var array
	 */
	protected $attributes = array();

	/**
	 * Constructor.
	 *
	 * @param string $markup
	 * @param array $tokens
	 * @param FileSystem $fileSystem
	 */
	public function __construct($markup, array &$tokens, FileSystem $fileSystem = null)
	{
		$this->markup = $markup;
		$this->fileSystem = $fileSystem;
		$this->parse($tokens);
	}

	/**
	 * Parse the given tokens.
	 *
	 * @param array $tokens
	 */
	public function parse(array &$tokens)
	{
		// Do nothing by default
	}

	/**
	 * Render the tag with the given context.
	 *
	 * @param Context $context
	 *
	 * @return string
	 */
	abstract public function render(Context $context);

    /**
     * Render attributes from a markup string. If the markup looks like "test foo:bar, foo2:=bar2" this method will
     * return ['foo' => 'bar', 'foo2' => '*rendered_liquid_variable*'] (everything after the first non-word character)
     *
     * @param string  $markup  Markup string
     * @param Context $context Context object
     */
    protected function renderAttributes($markup, Context $context)
    {
        $this->attributes = [];

        $attributeRegexp = new Regexp(Liquid::get('TAG_ATTRIBUTES'));

        $matches = $attributeRegexp->scan($markup);

        foreach ($matches as $match) {
            $this->attributes[$match[0]] = $this->renderAttributeValue($match[1], $context);
        }
    }

    /**
     * Render an attribute value. Values are allowed to be enclosed in quote marks or start with an "=" sign, in which
     * case they will be treated as a Liquid variable. Can also handle ternary statements.
     * @param string  $value   Attribute value to parse
     * @param Context $context Context object
     * @return string
     */
    protected function renderAttributeValue($value, Context $context)
    {
        $renderFragment = new Regexp(Liquid::get('RENDER_FRAGMENT'));
        $ternaryMatch = new Regexp(Liquid::get('TERNARY'));

        if ($renderFragment->match($value)) {
            $value = $renderFragment->matches[1];
            if ($ternaryMatch->match($value)) {
                // Regex check for an attribute that looks like a ternary statement.
                $value = self::renderAttributeValue($ternaryMatch->matches[1], $context) .
                    ' ? ' .
                    self::renderAttributeValue($ternaryMatch->matches[2], $context) .
                    ' : ' .
                    self::renderAttributeValue($ternaryMatch->matches[3], $context);
            }
        } else {
            $value = $context->get($value);
        }

        return $value;
    }

    /**
     * Return the first attribute from a markup list. If the markup looks like "'test' foo:'bar', foo2:bar2" this
     * method will return "test"
     * If the markup looks like "test foo:'bar', foo2:bar2" this
     * method will return the contents of the liquid "test" variable.
     *
     * @param string  $markup  Markup string
     * @param Context $context Context object
     * @return string First attribute from the markup
     */
    protected function getFirstAttribute($markup, Context $context)
    {
        $attributePattern = new Regexp(Liquid::get('FIRST_ATTRIBUTE'));

        if (!$attributePattern->match($markup)) {
            return '';
        }

        if (empty($attributePattern->matches[1])) {
            // First attribute was not enclosed in 'quotes', so we interpret it as a Liquid variable
            $attributePattern->matches[2] = $context->get($attributePattern->matches[2]);
        }

        return !empty($attributePattern->matches[2]) ? $attributePattern->matches[2] : '';
    }

	/**
	 * Extracts tag attributes from a markup string.
	 *
	 * @param string $markup
	 */
	protected function extractAttributes($markup)
	{
		$this->attributes = array();

		$attributeRegexp = new Regexp(Liquid::get('TAG_ATTRIBUTES'));

		$matches = $attributeRegexp->scan($markup);

		foreach ($matches as $match) {
			$this->attributes[$match[0]] = $match[1];
		}
	}

	/**
	 * Returns the name of the tag.
	 *
	 * @return string
	 */
	protected function name()
	{
		return strtolower(get_class($this));
	}
}
