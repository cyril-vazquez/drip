<?php
/**
 * Abstract class for internal signatures byte sequences
 */
abstract class abstractSequence
{
    /**
     * The min offset where sequence can be found
     * @var int
     */
    protected $minOffset;

    /**
     * The max offset where sequence can be found
     * @var int
     */
    protected $maxOffset;

    /**
     * The char representation of hex sequence
     * @var string
     */
    protected $value;

    /**
     * The associated PCRE pattern that will be matched
     * @var string
     */
    protected $pattern;

    /**
     * Create the PCRE pattern from the character representation of hexadecimal sequence
     */
    protected function makePattern()
    {
        
        $bytes = str_split($this->value, 1);
        $this->pattern = "/";
        
        while (current($bytes) !== false) {
            $byte = current($bytes);
            switch ($byte) {
                case '[':
                    $this->pattern .= '[';
                    $byte = next($bytes);
                    switch($byte) {
                        case '!': 
                            $this->pattern .= '^';
                            
                            $hex = next($bytes) . next($bytes);
                            $this->addChar($hex);

                            $byte = next($bytes);
                            switch($byte) {
                                case ':':
                                    $this->pattern .= '-';
                                    
                                    $hex = next($bytes) . next($bytes);
                                    $this->addChar($hex);
                                    
                                    next($bytes); // ]
                                    // Skip and Continue to close bracket

                                case ']':
                                    $this->pattern .= ']';
                            }
                            break;

                        default:
                            $hex = $byte . next($bytes);
                            $this->addChar($hex); 
                            
                            next($bytes); // :
                            $this->pattern .= '-';
                            
                            $hex = next($bytes) . next($bytes);
                            $this->addChar($hex);

                            next($bytes); // ]
                            $this->pattern .= ']';

                    }
                    break;

                case '?':
                    next($bytes);
                    next($bytes);
                    $pattern .= ".";
                    break;

                case '*':
                    next($bytes);
                    $pattern .= ".*";
                    break;

                // Hex value
                default:
                    $hex = $byte . next($bytes);
                    $this->addChar($hex); 
            }

            next($bytes);

        }

        $this->pattern .= "/"; 
    }

    /**
     * Add a character to the PCRE pattern from an hexadecimal byte
     * @param string $hex
     */
    protected function addChar($hex) 
    {
        $dec = hexdec($hex);
        if ($dec >= 32 && $dec <= 126) {
            $this->pattern .= preg_quote(chr($dec), "/");
        } else {
            $this->pattern .= '\x' . $hex;
        }
    }

    /**
     * Attempt to match the sequence on the right side (after)
     * @param string  $contents  The data to match
     * @param integer $refOffset The reference offset to match from
     * 
     * @return string The matched sequence or false if no match
     */
    protected function matchRight($contents, $refOffset) 
    {
        $matched = false;

        $contentsChunk = substr($contents, 0, $refOffset + 1024);

        if (!preg_match_all($this->pattern, $contentsChunk, $matches, PREG_OFFSET_CAPTURE, $refOffset)) {
            return false;
        }

        $minOffset = $refOffset + $this->minOffset;
        $maxOffset = $refOffset + $this->maxOffset;
        
        foreach ($matches[0] as $match) {
            $length = mb_strlen($match[0]);
            $offset = $match[1];
            
            if ($this->minOffset !== null && $offset < $minOffset) {
                continue;
            }
            if ($this->maxOffset !== null && $offset > $maxOffset) {
                continue;
            }
            if (isset($this->minLength) && $length < $this->minLength) {
                continue;
            }

            // If all tests passed, sub sequence has matched, continue to left & right fragments
            $matched = true;
            break;
        }

        if (!$matched) {
            return false;
        }

        return $match;
    }

    /**
     * Attempt to match the sequence on the left side (before)
     * @param string  $contents  The data to match
     * @param integer $refOffset The reference offset to match from
     * 
     * @return string The matched sequence or false if no match
     */
    protected function matchLeft($contents, $refOffset) 
    {
        $matched = false;

        $minOffset = $refOffset - $this->minOffset;
        $maxOffset = $refOffset - $this->maxOffset;

        $contentsChunk = mb_substr($contents, 0, $refOffset);
        
        if (!preg_match_all($this->pattern, $contentsChunk, $matches, PREG_OFFSET_CAPTURE)) {
            return false;
        }
        
        foreach ($matches[0] as $match) {
            $length = mb_strlen($match[0]);
            $offset = $match[1] + $length;

            if ($this->minOffset !== null && $offset > $minOffset) {
                continue;
            }
            if ($this->maxOffset !== null && $offset < $maxOffset) {
                continue;
            }
            if (isset($this->minLength) && $length < $this->minLength) {
                continue;
            }

            // If all tests passed, sub sequence has matched, continue to left & right fragments
            $matched = true;
            break;
        }

        if (!$matched) {
            return false;
        }

        return $match;
    }

}
