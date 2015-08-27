<?php
/**
 * Class for internal signatures
 */
class internalSignature
{
    
    /**
     * The signature internal identifier
     * @var integer
     */
    public $id;

    /**
     * The associated byte sequences
     * @var array
     */
    public $byteSequences = array();

    /**
     * The associated internal format identifiers
     * @var array
     */
    public $formatIds;

    /**
     * Constructor for the internal signature
     * @param DOMElement $internalSignatureElement The signature document element
     */
    public function __construct($internalSignatureElement)
    {
        $this->id = (integer) $internalSignatureElement->getAttribute('ID');

        $byteSequenceElements = $internalSignatureElement->getElementsByTagName("ByteSequence");

        foreach ($byteSequenceElements as $byteSequenceElement) {
            $this->byteSequences[] = new byteSequence($byteSequenceElement);
        }
    }

    /**
     * Attempt to match the signature with a content
     * @param string $contents The data to match
     * 
     * @return bool The result of the match attempt
     */
    public function match($contents)
    {
        foreach ($this->byteSequences as $byteSequence) {
            if (!$byteSequence->match($contents)) {
                return false;
            }
        }
        
        return true;
    }

}
