<?php
/**
 * Class for a byte sequence of an internal signature
 */
class byteSequence
{
    /**
     * The reference for offset : BOF or EOF
     * @var string
     */
    public $reference;

    /**
     * The endianness : littleEndian or bigEndian
     * @var string
     */
    public $endianness;

    /**
     * The subsequence objects
     * @var array
     */
    public $subSequences = array();

    /**
     * Constructor for the byte sequence
     * @param DOMElement $byteSequenceElement The signature document element
     */
    public function __construct($byteSequenceElement)
    {
        $this->reference = $byteSequenceElement->getAttribute('Reference');

        $this->endianness = $byteSequenceElement->getAttribute('Endianness');

        $subSequenceElements = $byteSequenceElement->getElementsByTagName("SubSequence");

        foreach ($subSequenceElements as $subSequenceElement) {

            $subSequence = new subSequence($subSequenceElement);

            if (!isset($this->subSequences[$subSequence->position])) {
                $this->subSequences[$subSequence->position] = array();
            }

            $this->subSequences[$subSequence->position][] = $subSequence;
        }
    }

    /**
     * Attempt to match the sequence with a content
     * @param string $contents The data to match
     * 
     * @return bool The result of the match attempt
     */
    public function match($contents)
    {
        // All subsequences positions must match (AND) 
        foreach ($this->subSequences as $position => $subSequencePosition) {
            // But subsequences with same position must match at least one (OR)
            foreach ($subSequencePosition as $pos => $subSequence) {
                $atLeastOneMatch = false;
                if ($subSequence->match($contents, $this->reference)) {
                    $atLeastOneMatch = true;
                    break;
                }

                if (!$atLeastOneMatch) {
                    return false;
                }
            }

        }

        return true;
    }

}
