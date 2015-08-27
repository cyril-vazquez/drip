<?php
/**
 * Class for subsequences of byte sequences
 */
class subSequence
    extends abstractSequence
{

    /**
     * The min length for matches
     * @var integer
     */
    public $minLength;

    /**
     * The left fragments of subsequence
     * @var array
     */
    public $leftFragments;

    /**
     * The right fragments of subsequence
     * @var array
     */
    public $rightFragments;

    /**
     * Constructor for the subsequence
     * @param DOMElement $subSequenceElement The signature document element
     */
    public function __construct($subSequenceElement)
    {
        $this->position = $subSequenceElement->getAttribute('Position');

        if ($subSequenceElement->hasAttribute('SubSeqMinOffset')) {
            $this->minOffset = (integer) $subSequenceElement->getAttribute('SubSeqMinOffset');
        } 
        if ($subSequenceElement->hasAttribute('SubSeqMaxOffset')) {
            $this->maxOffset = (integer) $subSequenceElement->getAttribute('SubSeqMaxOffset');
        } 

        if ($subSequenceElement->hasAttribute('MinFragLength')) {
            $this->minLength = (integer) $subSequenceElement->getAttribute('MinFragLength');
        }

        $this->value = $subSequenceElement->getElementsByTagName("Sequence")->item(0)->nodeValue;
        
        $this->makePattern();

        $leftFragmentElements = $subSequenceElement->getElementsByTagName('LeftFragment');
        for ($i=0, $l=$leftFragmentElements->length; $i<$l; $i++) {
            
            $leftFragment = new leftFragment($leftFragmentElements->item($i));
            if (!isset($this->leftFragments[$leftFragment->position])) {
                $this->leftFragments[$leftFragment->position] = array();
            }
            $this->leftFragments[$leftFragment->position][] = $leftFragment;
        }

        $rightFragmentElements = $subSequenceElement->getElementsByTagName('RightFragment');
        for ($i=0, $l=$rightFragmentElements->length; $i<$l; $i++) {
            
            $rightFragment = new rightFragment($rightFragmentElements->item($i));
            if (!isset($this->rightFragments[$rightFragment->position])) {
                $this->rightFragments[$rightFragment->position] = array();
            }
            $this->rightFragments[$rightFragment->position][] = $rightFragment;
        }


    }

    /**
     * Attempt to match the subsequence with a content
     * @param string $contents  The data to match
     * @param string $reference The reference for offsets : BOF or EOF
     * 
     * @return bool The result of the match attempt
     */
    public function match($contents, $reference)
    {
        $filesize = mb_strlen($contents);

        switch ($reference) {
            case 'EOFoffset':
                $subSequenceMatch = parent::matchLeft($contents, $filesize);
                break;

            case 'BOFoffset':
            default:
                $subSequenceMatch = parent::matchRight($contents, 0);
                break;

        }

        if (!$subSequenceMatch) {
            return false;
        }

        
        // left and right fragments at same position must match only once
        if ($this->leftFragments) {

            // Base offset for left search is sub sequence offset
            $offset = $subSequenceMatch[1];

            foreach ($this->leftFragments as $leftFragmentPosition) {
                $atLeastOneMatch = false;
                foreach ($leftFragmentPosition as $leftFragment) {
                    if ($leftFragmentMatch = $leftFragment->match($contents, $offset)) {
                        $atLeastOneMatch = true;

                        // Offset for next fragment is current fragment offset
                        $offset = $leftFragmentMatch[1];

                        break;
                    }
                }
                if (!$atLeastOneMatch) {
                    return false;
                }
            }
            
        }

        if ($this->rightFragments) {

            // Base offset for right search is sub sequence offset + length
            $offset = $subSequenceMatch[1] + mb_strlen($subSequenceMatch[0]);

            foreach ($this->rightFragments as $rightFragmentPosition) {
                $atLeastOneMatch = false;
                foreach ($rightFragmentPosition as $rightFragment) {
                    if ($rightFragmentMatch = $rightFragment->match($contents, $offset)) {
                        $atLeastOneMatch = true;

                        // Offset for next fragment is current fragment offset + length
                        $offset = $rightFragmentMatch[1] + mb_strlen($rightFragmentMatch[0]);

                        break;
                    }
                }
                if (!$atLeastOneMatch) {
                    return false;
                }
            }            
        }

        return true;
    }

}
