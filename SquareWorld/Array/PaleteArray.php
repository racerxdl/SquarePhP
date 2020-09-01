<?php
class PaleteArray {
    public $storage;
    public $bitsPerNumber;
    public $singleNumberMask;

	function ceilToBase($number, $base) {
		$ceil = ($number / $base) * $base;
		if ($number != $ceil) {
			$ceil += $base;
		}
		return $ceil;
	}

    function __construct($bitsPerNumber, $size)
    {
        $this->bitsPerNumber = $bitsPerNumber;
		$this->singleNumberMask = (1 << $bitsPerNumber) - 1;
        $this->storage = array_fill(0, $this->ceilToBase($size * $bitsPerNumber, 64) / 64, 0x0);
    }
    
    function getNumber($index) : int {
		$bitStartIndex = $index * $this->bitsPerNumber;
		$arrStartIndex = $bitStartIndex >> 6;
		$arrEndIndex = (($bitStartIndex + $this->bitsPerNumber) - 1) >> 6;
		$localStartBitIndex = $bitStartIndex & 63;
		if ($arrStartIndex == $arrEndIndex) {
			return (int) (($this->storage[$arrStartIndex] >> $localStartBitIndex) & $this->singleNumberMask);
		} else {
			return (int) ((($this->storage[$arrStartIndex] >> $localStartBitIndex) | ($this->storage[$arrEndIndex] << (64 - $localStartBitIndex))) & $this->singleNumberMask);
		}
    }

    function setNumber($index, $number) {
		$bitStartIndex = $index * $this->bitsPerNumber;
		$arrStartIndex = $bitStartIndex >> 6;
		$arrEndIndex = (($bitStartIndex + $this->bitsPerNumber) - 1) >> 6;
		$localStartBitIndex = $bitStartIndex & 63;
		$this->storage[$arrStartIndex] = (($this->storage[$arrStartIndex] & ~($this->singleNumberMask << $localStartBitIndex)) | (($number & $this->singleNumberMask) << $localStartBitIndex));
		if ($arrStartIndex != $arrEndIndex) {
			$thisPartSift = 64 - $localStartBitIndex;
			$otherPartShift = $this->bitsPerNumber - $thisPartSift;
			$this->storage[$arrEndIndex] = ((($this->storage[$arrEndIndex] >> $otherPartShift) << $otherPartShift) | (($number & $this->singleNumberMask) >> $thisPartSift));
		}
    }
    
    function getPalette() {
        return $this->storage;
    }
}
