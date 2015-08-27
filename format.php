<?php
/**
 * Class for pronom database format
 */
class format
{
    /**
     * The internal identifier
     * @var string 
     */
    public $id;

    /**
     * The format name
     * @var string 
     */
    public $name;

    /**
     * The format version
     * @var string 
     */
    public $version;

    /**
     * The format pronom database identifier
     * @var string 
     */
    public $puid;

    /**
     * The associated mime types
     * @var array 
     */
    public $mimetypes = array();

    /**
     * The associated file extensions
     * @var array 
     */
    public $extensions = array();

    /**
     * The associated internal dignature ids
     * @var array 
     */
    public $internalSignatureIds = array();

    /**
     * Declares on which format ids this format has priority over
     * @var array 
     */
    public $hasPriorityOverFormatIds;

    /**
     * Constructor of the format
     * @param DOMElement $formatElement The signature document element
     */
    public function __construct($formatElement)
    {
        $this->id = (integer) $formatElement->getAttribute('ID');

        $this->puid = (string) $formatElement->getAttribute('PUID');

        $this->name = (string) $formatElement->getAttribute('Name');

        if ($formatElement->hasAttribute('MIMEType')) {
            $mimetypes = explode(',', $formatElement->getAttribute('MIMEType'));
            foreach ($mimetypes as $mimetype) {
                $this->mimetypes[] = trim($mimetype);
            }
        }

        $this->version = (string) $formatElement->getAttribute('Version');

        $extensionElements = $formatElement->getElementsByTagName('Extension');
        for ($i=0, $l=$extensionElements->length; $i<$l; $i++) {
            $this->extensions[] = (string) $extensionElements->item($i)->nodeValue;
        }

        $internalSignatureElements = $formatElement->getElementsByTagName('InternalSignatureID');
        for ($i=0, $l=$internalSignatureElements->length; $i<$l; $i++) {
            $this->internalSignatureIds[] = (integer) $internalSignatureElements->item($i)->nodeValue;
        }

        $hasPriorityOverFileFormatIdElements = $formatElement->getElementsByTagName('HasPriorityOverFileFormatID');
        for ($i=0, $l=$hasPriorityOverFileFormatIdElements->length; $i<$l; $i++) {
            $this->hasPriorityOverFormatIds[] = (integer) $hasPriorityOverFileFormatIdElements->item($i)->nodeValue;
        }

    }

}
