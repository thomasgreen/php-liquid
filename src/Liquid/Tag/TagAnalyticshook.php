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

use  Analytics\AnalyticsHook;
use  Liquid\AbstractBlock;
use  Liquid\Context;
use  Liquid\FileSystem;

class TagAnalyticsHook extends AbstractBlock
{

    public function __construct($markup, array &$tokens, FileSystem $fileSystem = null)
    {
        parent::__construct($markup, $tokens, $fileSystem);

        $this->markup = trim($this->markup);
        foreach ($this->nodelist as &$node) {
            if (is_string($node)) {
                $node = str_replace("\n", '', $node);
            }
        }
    }


    public function render(Context $context)
    {
        $this->renderAttributes($this->markup, $context);
        AnalyticsHook::addHook($this->attributes);
        return;
    }
}
