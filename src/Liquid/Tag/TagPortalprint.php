<?php

namespace Liquid\Tag;

use  Liquid\AbstractTag;
use  Liquid\Context;
use  Liquid\IncludePath;
use  Liquid\Regexp;

class TagPortalprint extends AbstractTag
{
    public function render(Context $context)
    {
        $this->renderAttributes($this->markup, $context);
        $portalName = $this->getFirstAttribute($this->markup, $context);

        $portal = $context->get($portalName) ?: [];

        $response = '';
        if (is_array($portal)) {
            foreach ($portal as $name => $value) {
                $response .= $value;
            }
        } else {
            $response = $portal;
        }
        return $response;
    }


}
