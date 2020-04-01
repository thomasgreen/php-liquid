<?php

namespace Liquid\Tag;

use  Component;
use  Liquid\AbstractTag;
use  Liquid\Context;
use \FormBuilderFactory;
use Exception;
use Kohana;
use  Liquid\IncludePath;

/**
 * Set up a Formbuilder form
 */
class TagFormbuilder extends AbstractTag
{

    /**
     * Render a FormBuilder form.
     * @param Context $context
     * @return string
     * @throws Exception
     */
    public function render(Context $context)
    {
        // Register the AMP components we'll need for forms
        Component::addComponent('amp-form');
        Component::addComponent('amp-mustache');

        $formType = $this->getFirstAttribute($this->markup, $context);
        $this->renderAttributes($this->markup, $context);
        $this->populateFormBuilderAttributes($formType);

        IncludePath::pushStack($formType);

        // Build the form and return the AMP view
        $form = FormBuilderFactory::create(
            $formType,
            isset($this->attributes['action_url']) ? $this->attributes['action_url'] : '/amp/form/submit',
            null,
            $this->attributes // Load all attributes as form options so they can be used later in the liquid template
        )->ampView();

        IncludePath::popStack();

        return $form;
    }

    /**
     * Provide some form option defaults and then load in the AMP options for the form from FormBuilder config.
     * These options are overwritten by the options provided when FormBuilder was called.
     * @param string $formType Name of the FormBuilder form
     */
    protected function populateFormBuilderAttributes($formType)
    {
        $config = Kohana::config('formbuilder/' . $formType . '.amp');
        $config = !empty($config) ? $config : [];

        $this->attributes = array_merge(
            [
                'form_target' => '_top',
                'button_text' => 'submit_' . $formType,
                'button_type' => 'submit',
            ],
            $config,
            $this->attributes
        );
    }

}
