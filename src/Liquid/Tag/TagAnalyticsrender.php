<?php

namespace Liquid\Tag;

use  Analytics\Analytics;
use  Liquid\AbstractTag;
use  Liquid\Context;

/**
 * Output AMP state tags
 */
class TagAnalyticsrender extends AbstractTag
{

    public function render(Context $context)
    {
        $this->renderAttributes($this->markup, $context);
        $attributeName = $this->getFirstAttribute($this->markup, $context);

        if (!empty($attributeName)) {
            $attributes = Analytics::triggers($attributeName);
            Analytics::clear($attributeName);

        } else {
            $attributes = Analytics::triggers();
            Analytics::clear();
        }

        return json_encode($attributes);
    }

}
