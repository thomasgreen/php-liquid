<?php

namespace Liquid\Tag;

use  Liquid\AbstractTag;
use  Liquid\Context;
use \FormBuilderFactory;
use Exception;

/**
 * Set up a Formbuilder form
 */
class TagCsrf extends AbstractTag
{

    /**
     * Render a FormBuilder csrf input for a form.
     * @param Context $context
     * @return string
     * @throws Exception
     */
    public function render(Context $context)
    {
        $formType = $this->getFirstAttribute($this->markup, $context);
        $this->renderAttributes($this->markup, $context);

        // Build the form and return the AMP view
        $form = FormBuilderFactory::create(
            $formType
        );

        return $form->ampRenderCsrf();
    }
}
