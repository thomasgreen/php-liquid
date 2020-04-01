<?php

namespace Liquid\Tag;

use  Liquid\AbstractTag;
use  Liquid\Context;
use HomePage;
use  Liquid\FileSystem;

/**
 * Output a site slice
 */
class TagSlice extends AbstractTag
{

    public function __construct($markup, array &$tokens, FileSystem $fileSystem = null)
    {
        parent::__construct($markup, $tokens, $fileSystem);
        $this->markup = trim($this->markup);
    }

    /**
     * Render slices. If there's an array of slices then loop over it and render each one, if just one slice was
     * passed just render it.
     * @param Context $context
     * @return string Rendered slices
     */
    public function render(Context $context)
    {
        $slices = $this->renderAttributeValue($this->markup, $context);
        $output = '';


        if (is_array($slices)) {
            foreach ($slices as $slice) {
                $output .= $slice;
            }
        } else {
            $output = $slices;
        }

        return $output;
    }

}
