<?php

namespace Liquid\Tag;

use  Liquid\AbstractTag;
use  Liquid\Context;
use  Macro;

/**
 * Output AMP macro tags
 */
class TagAmpmacrotags extends AbstractTag
{

    /**
     * Render the current batch of AMP macro tags. Output might look something like this:
     *
     * <amp-bind-macro id="circleArea" arguments="radius" expression="3.14 * radius * radius"></amp-bind-macro>
     *
     * @param Context $context
     * @return string AMP tags
     */
    public function render(Context $context)
    {
        $tags = '';

        foreach (Macro::render() as $macroId => $props) {
            $tags .= '<amp-bind-macro id="' . $macroId . '"';
            foreach ($props as $propId => $propContent) {
                $tags .= ' ' . $propId . '="' . $propContent . '"';
            }
            $tags .= '></amp-bind-macro>' . PHP_EOL;
        }

        return $tags;
    }

}
