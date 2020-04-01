<?php

namespace Liquid\Tag;

use  Liquid\Context;
use  Liquid\IncludePath;
use  Liquid\Liquid;
use  Liquid\AbstractBlock;
use  Liquid\Regexp;

class TagPortal extends AbstractBlock
{
    public function render(Context $context)
    {
        $this->renderAttributes($this->markup, $context);
        $portalName = $this->getFirstAttribute($this->markup, $context);

        $regex = new Regexp('/(?=\S*[\'-_])([a-zA-Z\'-_]+)(\.)(?=\S*[\'-_])([a-zA-Z\'-_]+)/');
        $portalContent = $this->renderAll($this->nodelist, $context);
        if ($regex->match($portalName)) {
            $portalArray = $context->get($regex->matches[1]) ?: [];
            $portalArray[$regex->matches[3]] = $portalContent;
            $context->set($regex->matches[1], $portalArray, true);
        } else {
            $context->set($portalName, $portalContent, true);
        }

        return '';
    }


}
