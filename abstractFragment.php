<?php
/**
 * Abstract class for binary fragments on internal signature objects
 */
abstract class abstractFragment
    extends abstractSequence
{
    /**
     * Constructor for the fragment
     * @param DOMElement $fragmentElement The signature document element
     */
    public function __construct($fragmentElement)
    {
        $this->position = (integer) $fragmentElement->getAttribute('Position');

        if ($fragmentElement->hasAttribute('MinOffset')) {
            $this->minOffset = (integer) $fragmentElement->getAttribute('MinOffset');
        }

        if ($fragmentElement->hasAttribute('MaxOffset')) {
            $this->maxOffset = (integer) $fragmentElement->getAttribute('MaxOffset');
        }

        $this->value = $fragmentElement->nodeValue;
        
        $this->makePattern();
    }
}
