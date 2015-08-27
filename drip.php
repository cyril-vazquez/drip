<?php
/*
 * Copyright (C) 2015 Maarch
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * Main class for digital resources information Php
 */
class drip
{
    /**
     * The PRONOM internal signature objects indexed by signature identifier
     * @var array
     */
    protected $internalSignatures = array();

    /**
     * The PRONOM formats indexed by format identifier
     * @var array
     */
    protected $formats = array();

    /**
     * The PRONOM formats indexed by puid (pronom external identifier)
     * @var array
     */
    protected $puids = array();

    /**
     * The PRONOM formats indexed by MIMEtype
     * @var array
     */
    protected $mimetypes = array();   

    /**
     * The PRONOM formats indexed by file extension
     * @var array
     */
    protected $extensions = array();

    /**
     * The currently loaded signature file version
     * @var string
     */
    protected $signatureFileVersion;

    /**
     * The finfo object
     * @var object
     */
    protected $finfo;

    /**
     * Constructor
     * @param string $droidSignatureFile The XML DROID signature filename
     */
    public function __construct($droidSignatureFile)
    {
        $this->loadSignatureFile($droidSignatureFile, false);

        $this->finfo = new finfo();
    }

    // ------------------------------------------------------------------------
    //  Getters
    // ------------------------------------------------------------------------
    /**
     * Get the formats
     * @return array
     */
    public function getFormats()
    {
        return $this->formats;
    }

    /**
     * Get a format by its Id
     * @param integer $formatId The format identifier
     * 
     * @return array
     */
    public function getFormatById($formatId)
    {
        if (isset($this->formats[$formatId])) {
            return $this->formats[$formatId];
        }
    }

    /**
     * Get the format by Puid
     * @param string $puid The PRONOM unique identifier
     * 
     * @return object
     */
    public function getFormatByPuid($puid)
    {
        if (isset($this->puids[$puid])) {
            $formatId = $this->puids[$puid];

            return $this->formats[$formatId];
        }
    }

    /**
     * Get the formats by mime type
     * @param string $mimetype
     * 
     * @return array
     */
    public function getFormatsByMimetype($mimetype)
    {
        $formats = array();
        if (isset($this->mimetypes[$mimetype])) {
            foreach ($this->mimetypes[$mimetype] as $formatId) {
                $formats[] = $this->formats[$formatId];
            }
        }

        return $formats;
    }

    /**
     * Get the formats by mim type
     * @param string $extension
     * 
     * @return array
     */
    public function getFormatsByExtension($extension)
    {
        $formats = array();
        if (isset($this->extensions[$extension])) {
            foreach ($this->extensions[$extension] as $formatId) {
                $formats[] = $this->formats[$formatId];
            }
        }

        return $formats;
    }

    /**
     * Get the signature database version
     * @return array
     */
    public function getSignatureFileVersion()
    {
        return $this->signatureFileVersion;
    }

    /**
     * Clear the cached database
     * @param string $droidSignatureFile The path to a DROID signature file (xml)
     * @param bool   $force              Force load or allow the use of cached signature object
     */
    public function loadSignatureFile($droidSignatureFile, $force=true)
    {
        $droidSignatureFileContents = file_get_contents($droidSignatureFile);

        $signatureDocument = new \DOMDocument();
        $signatureDocument->loadXml($droidSignatureFileContents);

        $this->signatureFileVersion = $signatureDocument->documentElement->getAttribute('Version');

        $signatureObjectFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'drip_signatureObject_' . $this->signatureFileVersion;
        
        if (!$force && is_file($signatureObjectFile)) {
            $this->loadCache($signatureObjectFile);
        } else {    
            $this->parseAndCache($signatureDocument, $signatureObjectFile);
        }
    }

    /**
     * Get file information from a filename
     * @param string $filename
     * 
     * @return array
     */
    public function file($filename)
    {
        if (!is_file($filename)) {
            trigger_error("drip::file($filename): failed to open stream: No such file", E_USER_ERROR);
        }

        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        $contents = file_get_contents($filename);

        $format = $this->getFormat($contents, $extension);

        return $format;
    }

    /**
     * Get file information from a string contents
     * @param string $contents  The stream contents
     * @param string $extension The file extension
     * 
     * @return array
     */
    public function buffer($contents, $extension=null)
    {
        $format = $this->getFormat($contents, $extension);

        return $format;
    }

    /**
     * Try to match a format with the contents and resource info
     * @param string $contents  The resource contents
     * @param string $extension The file extension
     * 
     * @return array The possible formats
     */
    protected function getFormat($contents, $extension=null)
    {
        $formats = $this->matchInternalSignature($contents);

        switch (count($formats)) {
            case 1:
                return reset($formats);

            case 0:
            default:
                if ($mimetype = $this->finfo->buffer($contents, FILEINFO_MIME_TYPE)) {
                    $formats = $this->matchMimetype($mimetype, $formats);
                }
        }

        switch (count($formats)) {
            case 1:
                return reset($formats);

            case 0:
            default:
                if ($extension) {
                    $formats = $this->matchExtension($extension, $formats);
                }
        }

        switch (count($formats)) {
            case 1:
                return reset($formats);

            case 0:
            default:
                return;
        }

    }

    /**
     * Try to match a format with the resource mimetype
     * @param string $mimetype The resource mimetype
     * @param array  $formats  An array of formats to test
     * 
     * @return array The possible formats
     */
    protected function matchMimetype($mimetype, array $formats=null)
    {
        if (!$formats || count($formats) == 0) {
            $formats = $this->formats;
        }

        $selectedFormats = array();

        if (isset($this->mimetypes[$mimetype])) {
            $formatIds = array_intersect(array_keys($formats), $this->mimetypes[$mimetype]);
            foreach ($formatIds as $formatId) {
                $selectedFormats[$formatId] = $formats[$formatId];
            }
        }

        return $selectedFormats;
    }

    /**
     * Try to match a format with the internal signatures
     * @param string $contents The resource contents
     * @param array  $formats  An array of formats to test
     * 
     * @return array The possible formats
     */
    protected function matchInternalSignature($contents, array $formats=null)
    {
        if (!$formats || count($formats) == 0) {
            $formats = $this->formats;
        }

        $selectedFormats = array();

        $testedInternalSignatureIds = array();

        foreach ($formats as $formatId => $format) {

            if (count($format->internalSignatureIds) == 0) {
                continue;
            }

            foreach ($format->internalSignatureIds as $internalSignatureId) {
                if (in_array($internalSignatureId, $testedInternalSignatureIds)) {
                    continue;
                } else {
                    $testedInternalSignatureIds[] = $internalSignatureId;
                }
                $internalSignature = $this->internalSignatures[(integer) $internalSignatureId];
                if ($internalSignature->match($contents)) {
                    $selectedFormats[$formatId] = $format;
                    break;  
                }
            }     
        }

        return $selectedFormats;

    }
   
    /**
     * Try to match a format with the extension
     * @param string $extension The resource object
     * @param array  $formats   An array of formats to test
     * 
     * @return array The possible formats
     */
    protected function matchExtension($extension, $formats=false)
    {
        
        if (!$formats || count($formats) == 0) {
            $formats = $this->formats;
        }

        $selectedFormats = array();

        if (isset($this->extensions[$extension])) {
            $formatIds = array_intersect(array_keys($formats), $this->extensions[$extension]);
            foreach ($formatIds as $formatId) {
                $selectedFormats[$formatId] = $formats[$formatId];
            }
        }

        return $selectedFormats;
    }

    protected function applyPriorities($formats)
    {
        // Apply priorities to keep only most restricted formats (PDF/A is a restriction on PDF 1.4)
        foreach ($formats as $formatId => $format) {
            if ($format->hasPriorityOverFormatIds) {
                foreach ($format->hasPriorityOverFormatIds as $hasPriorityOverFormatId) {
                    if (isset($formats[$hasPriorityOverFormatId])) {
                        unset($formats[$hasPriorityOverFormatId]);
                    }
                }
            }
        }

        return $formats;
    }

    protected function loadCache($signatureObjectFile)
    {
        $signatureObject = unserialize(file_get_contents($signatureObjectFile));
        if (!isset($signatureObject->internalSignatures)
            || !isset($signatureObject->formats)
            || !isset($signatureObject->puids)
            || !isset($signatureObject->mimetypes)
            || !isset($signatureObject->extensions)
        ) {
            $this->parseAndCache();

            return;
        }

        $this->internalSignatures = $signatureObject->internalSignatures;
        $this->formats = $signatureObject->formats;
        $this->puids = $signatureObject->puids;
        $this->mimetypes = $signatureObject->mimetypes;
        $this->extensions = $signatureObject->extensions;
    }

    protected function parseAndCache($signatureDocument, $signatureObjectFile)
    {
        $internalSignatureElements = $signatureDocument->getElementsByTagName("InternalSignature");
        foreach ($internalSignatureElements as $internalSignatureElement) {
            $internalSignature = new internalSignature($internalSignatureElement);
           
            $this->internalSignatures[(integer) $internalSignature->id] = $internalSignature;            
        }

        $formatElements = $signatureDocument->getElementsByTagName("FileFormat");
        foreach ($formatElements as $formatElement) {
            $format = new format($formatElement);
            $this->formats[$format->id] = $format;

            $this->puids[$format->puid] = $format->id; 

            foreach ($format->mimetypes as $mimetype) {
                $this->mimetypes[$mimetype][] = $format->id; 
            }

            foreach ($format->extensions as $extension) {
                $this->extensions[$extension][] = $format->id; 
            }
        }

        $signatureObject = new \stdClass();
        $signatureObject->internalSignatures = $this->internalSignatures;
        $signatureObject->formats = $this->formats;
        $signatureObject->puids = $this->puids;
        $signatureObject->mimetypes = $this->mimetypes;
        $signatureObject->extensions = $this->extensions;

        file_put_contents($signatureObjectFile, serialize($signatureObject));
    }

}
