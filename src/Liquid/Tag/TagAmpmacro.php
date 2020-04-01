<?php

namespace Liquid\Tag;

use  Liquid\AbstractTag;
use  Liquid\Context;
use  Macro;

/**
 * Register an AMP macro
 */
class TagAmpmacro extends AbstractTag
{

    /**
     * Render the AMP macro directly to the Macro class, which will store it and output later.
     * Attributes are rendered. So, if you created an ampmacro tag like this:
     * <% ampmacro 'circleArea' arguments='radius' expression='3.14 * radius * radius' %>
     * The output would look like this:
     * <amp-bind-macro id="circleArea" arguments="radius" expression="3.14 * radius * radius"></amp-bind-macro>
     * Note, attributes not in quotes get rendered as liquid variables. Attributes in quotes are rendered literally.
     * Note, this renders nothing to the output here, it's stored in the Macro class and rendered in the footer.
     * @param Context $context Context object
     * @return void
     */
    public function render(Context $context)
    {
        $this->renderAttributes($this->markup, $context);
        $macroName = $this->getFirstAttribute($this->markup, $context);

        Macro::addMacro(
            $macroName,
            $this->attributes
        );

        return;
    }

}
