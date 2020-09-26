<?php
namespace Former\Form\Fields;

use Former\Traits\Checkable;

/**
 * Creating radios elements since 1873
 */
class Radio extends Checkable
{
    /**
     * The current checkable type
     *
     * @var string
     */
    protected $checkable = 'radio';

    ////////////////////////////////////////////////////////////////////
    ////////////////////////// FIELD METHODS ///////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Create a serie of radios
     *
     * @return self
     */
    public function radios(): self
    {
        $this->items(func_get_args());

        return $this;
    }
}
