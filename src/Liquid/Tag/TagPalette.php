<?php

namespace Liquid\Tag;

use  Liquid\Liquid;
use  AmpClass;
use  Liquid\AbstractTag;
use  Liquid\Exception\ParseException;
use  Liquid\Context;
use  Liquid\FileSystem;
use  Liquid\Regexp;
use  Palette;

/**
 * Generate Palette string
 * Example:
 *     <% palette 'btn' %>
 */
class TagPalette extends AbstractTag
{
    /**
     * @var array
     */
    private $classes;

    /**
     * Renders the node
     *
     * @param Context $context
     * @return string
     * @throws ParseException
     */
    public function render(Context $context)
    {
        $this->renderMarkup($context);
        $paletteRegexp = new Regexp("/[0-9a-zA-Z\-\'\_]+(,[0-9a-zA-Z\-\'\_]+)*/");

        if ($paletteRegexp->matchAll($this->markup)) {
            $this->classes = $paletteRegexp->matches[0];
        } else {
            throw new ParseException("Error in tag 'palette' - Valid syntax: palette classA, classB, ... classZ");
        }

        $classes = [];
        $renderFragment = new Regexp(Liquid::get('RENDER_FRAGMENT'));

        foreach ($this->classes as $class) {
            if ($renderFragment->match($class)) {
                $classes[] = $renderFragment->matches[1];
            } else {
                $classes[] = $context->get($class);
            }
        }

        return Palette::getClasses(implode(',', $classes));
    }

    /**
     * Render the markup. If markup is 'palette' we're probably trying to render a variable, so render the variable and
     * pass it back in the same format as the rest of the markups.
     *
     * @param Context $context Context variable to allow us to render the variable
     */
    protected function renderMarkup(Context $context)
    {
        if (trim($this->markup) != 'palette') {
            return;
        }

        $markup = $this->getFirstAttribute($this->markup, $context);

        $markup = explode(',', $markup);
        foreach ($markup as &$markupItem) {
            $markupItem = "'" . trim($markupItem) . "'";
        }
        $markup = implode(', ', $markup);

        $this->markup = $markup;
    }
}
