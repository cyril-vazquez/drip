<?php
/**
 * Class for right fragments of byte sequences
 */
class rightFragment
    extends abstractFragment
{

    /**
     * Attempt to match the sequence on the right side (after)
     * @param string  $contents  The data to match
     * @param integer $refOffset The reference offset to match from
     * 
     * @return string The matched sequence or false if no match
     */
    public function match($contents, $refOffset)
    {
        $matched = parent::matchRight($contents, $refOffset);

        return $matched;
    }

}
