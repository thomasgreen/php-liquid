<?php

namespace Liquid\Tag;

use  Liquid\AbstractTag;
use  Liquid\Context;
use  State;

/**
 * Output AMP state tags
 */
class TagAmpstatetags extends AbstractTag
{

    /**
     * Render the current batch of AMP state tags. Output might look something like this:
     *
     * <amp-state id="productList" [src]="statevar.value ? state.info : /amp/listings/products/c:1"></amp-state>
     * <amp-state id="testState">
     * <script type="application/json">
     * {"Mark":true}
     * </script>
     * </amp-state>
     *
     * @param Context $context
     * @return string AMP tags
     */
    public function render(Context $context)
    {
        $tags = '';

        foreach (State::render() as $stateId => $state) {
            $tags .= '<amp-state id="'.$stateId.'"';
            if (isset($state['props']) && is_array($state['props'])) {
                foreach ($state['props'] as $propId => $propContent) {
                        $tags .= ' '.$propId.'="'.$propContent.'"';
                }
            }
            $tags .= '>';

            if (!empty($state['data'])) {
                $tags .= PHP_EOL.'<script type="application/json">'.PHP_EOL;
                $tags .= json_encode($state['data']).PHP_EOL;
                $tags .= '</script>'.PHP_EOL;
            }

            $tags .= '</amp-state>'.PHP_EOL;
        }

        return $tags;
    }

}
