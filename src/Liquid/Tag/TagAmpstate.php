<?php

namespace Liquid\Tag;

use  Liquid\AbstractBlock;
use  Liquid\Context;
use  Liquid\FileSystem;
use  State;

/**
 * Register an AMP state
 */
class TagAmpstate extends AbstractBlock
{

    /**
     * TagAmpstate constructor.
     * Additionally, register an AMP state
     *
     * @param            $markup
     * @param array      $tokens
     * @param FileSystem $fileSystem
     */
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

    /**
     * Render the AMP state directly to the State class, which will store it and output later.
     * Attributes are rendered. So, if you created an ampstate tag like this:
     * <% ampstate 'productList' src:endpoint, [src]:endpoint, foo:'bar' %><% endampstate %>
     * The output would look like this:
     * <amp-state id="productList" src="/amp/listings/products/c:1" [src]="/amp/endpoint" foo="bar" ></amp-state>
     * Note, attributes not in quotes (eg. endpoint) get rendered as liquid variables. Attributes in quotes are
     * rendered literally.
     * Note, this renders nothing to the output here, it's stored in the State class and rendered in the footer.
     * @param Context $context Context object
     * @return void
     */
    public function render(Context $context)
    {
        $this->renderAttributes($this->markup, $context);
        $stateName = $this->getFirstAttribute($this->markup, $context);

        State::addState(
            $stateName,
            json_decode($this->renderAll($this->nodelist, $context)),
            $this->attributes
        );

        return;
    }

}
