<?php
/**
 * Class for left fragments of byte sequences
 */
class leftFragment
    extends abstractFragment
{
    /**
     * Attempt to match the sequence on the left side (before)
     * @param string  $contents  The data to match
     * @param integer $refOffset The reference offset to match from
     * 
     * @return string The matched sequence or false if no match
     */
    public function match($contents, $refOffset)
    {
        $matched = parent::matchLeft($contents, $refOffset);

        return $matched;
    }

}
