<?php

/*
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace Liquid\Tag;

use CMSPage;
use  Liquid\AbstractBlock;
use  Liquid\AbstractTag;
use  Liquid\Context;

class TagCmscontent extends AbstractTag
{
    public function render(Context $context)
    {
        $contentName = $this->getFirstAttribute($this->markup, $context);

        $content = CMSPage::content($contentName);
        return !empty($content) ? $content : '';
    }
}
