<?php

namespace Liquid\Tag;

use  Liquid\AbstractTag;
use  Liquid\Context;

/**
 * Register an AMP state
 */
class TagAmpvars extends AbstractTag
{
    public function render(Context $context)
    {
        $this->renderAttributes($this->markup, $context);
        $configItem = $this->getFirstAttribute($this->markup, $context);

        return \Kohana::config('amp_vars.'.$configItem);
    }
}
