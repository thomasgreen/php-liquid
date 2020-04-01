<?php

namespace Liquid\Tag;

use  Liquid\AbstractTag;
use  Liquid\Context;
use  State;

/**
 * Output AMP state tags
 */
class TagIconrender extends AbstractTag
{

    /**
     * Render the icons used on page.
     *
     * @param Context $context
     * @return string Icons
     */
    public function render(Context $context)
    {
        return \Vs\Amp\Icon::renderAll();
    }

}
