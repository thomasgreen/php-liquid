<?php

namespace Liquid\Tag;

use  Component;
use  Liquid\AbstractTag;
use  Liquid\Context;

class TagAmpRegister extends AbstractTag
{

    /**
     * Renders nothing, but does add a component to be output in the head of the page.
     *
     * @param Context $context Context object
     * @return string Empty string
     */
    public function render(Context $context)
    {
        $ampComponents = explode(',', $this->markup);

        foreach ($ampComponents as $component) {
            Component::addComponent(trim($component, '"\' '));
        }

        return '';
    }
}
