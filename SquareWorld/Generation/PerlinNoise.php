<?php
namespace NoiseGenerator;

/**
 * Port of Ken Perlin's "Improved Noise"
 * http://mrl.nyu.edu/~perlin/noise/
 */
class PerlinNoise
{
    var $p;
    var $permutation;

    /** @var int */
    var $seed;

    function __construct($seed = null)
    {
        //Initialize the permutation array
        $this->p = array();
        $this->permutation = array(151,160,137,91,90,15,
        131,13,201,95,96,53,194,233,7,225,140,36,103,30,69,142,8,99,37,240,21,10,23,
        190, 6,148,247,120,234,75,0,26,197,62,94,252,219,203,117,35,11,32,57,177,33,
        88,237,149,56,87,174,20,125,136,171,168, 68,175,74,165,71,134,139,48,27,166,
        77,146,158,231,83,111,229,122,60,211,133,230,220,105,92,41,55,46,245,40,244,
        102,143,54, 65,25,63,161, 1,216,80,73,209,76,132,187,208, 89,18,169,200,196,
        135,130,116,188,159,86,164,100,109,198,173,186, 3,64,52,217,226,250,124,123,
        5,202,38,147,118,126,255,82,85,212,207,206,59,227,47,16,58,17,182,189,28,42,
        223,183,170,213,119,248,152, 2,44,154,163, 70,221,153,101,155,167, 43,172,9,
        129,22,39,253, 19,98,108,110,79,113,224,232,178,185, 112,104,218,246,97,228,
        251,34,242,193,238,210,144,12,191,179,162,241, 81,51,145,235,249,14,239,107,
        49,192,214, 31,181,199,106,157,184, 84,204,176,115,121,50,45,127, 4,150,254,
        138,236,205,93,222,114,67,29,24,72,243,141,128,195,78,66,215,61,156,180
        );

        //Populate it
        for ($i=0; $i < 256 ; $i++) {
            $this->p[256+$i] = $this->p[$i] = $this->permutation[$i];
        }

        //And set the seed
        if ($seed === null) {
            $this->seed = microtime(true) * 10000;
        } else {
            $this->seed = $seed;
        }
    }

    function noise($x, $y, $z, array $octaves)
    {
        //Set the initial value and initial size
        $value = 0.0;
        $initialSize = $octaves[0];

        //Add finer and finer hues of smoothed noise together
        foreach ($octaves as $octave) {
            $value += $this->smoothNoise($x / $octave, $y / $octave, $z / $octave) * $octave;
        }

        //Return the result over the initial size
        return $value / $initialSize;
    }

    //This function determines what cube the point passed resides in
    //and determines its value.
    function smoothNoise($x, $y, $z)
    {
        //Offset each coordinate by the seed value
        $x += $this->seed;
        $y += $this->seed;
        $z += $this->seed;

        $origX = $x;
        $origY = $y;
        $origZ = $z;

        $X1 = (int)floor($x) & 255;                  // FIND UNIT CUBE THAT
        $Y1 = (int)floor($y) & 255;                  // CONTAINS POINT.
        $Z1 = (int)floor($z) & 255;
        $x -= floor($x);                             // FIND RELATIVE X,Y,Z
        $y -= floor($y);                             // OF POINT IN CUBE.
        $z -= floor($z);
        $u = $this->fade($x);                        // COMPUTE FADE CURVES
        $v = $this->fade($y);                        // FOR EACH OF X,Y,Z.
        $w = $this->fade($z);

        $A  = $this->p[$X1]+$Y1;
        $AA = $this->p[$A]+$Z1;
        $AB = $this->p[$A+1]+$Z1;      // HASH COORDINATES OF
        $B  = $this->p[$X1+1]+$Y1;
        $BA = $this->p[$B]+$Z1;
        $BB = $this->p[$B+1]+$Z1;      // THE 8 CUBE CORNERS,

        //Interpolate between the 8 points determined
        // AND ADD BLENDED RESULTS FROM 8 CORNERS OF CUBE
        $result = $this->lerp($w, $this->lerp($v, $this->lerp($u, $this->grad($this->p[$AA  ], $x  , $y  , $z   ),
                                                                  $this->grad($this->p[$BA  ], $x-1, $y  , $z   )),
                                                  $this->lerp($u, $this->grad($this->p[$AB  ], $x  , $y-1, $z   ),
                                                                  $this->grad($this->p[$BB  ], $x-1, $y-1, $z   ))),
                                  $this->lerp($v, $this->lerp($u, $this->grad($this->p[$AA+1], $x  , $y  , $z-1 ),
                                                                  $this->grad($this->p[$BA+1], $x-1, $y  , $z-1 )),
                                                  $this->lerp($u, $this->grad($this->p[$AB+1], $x  , $y-1, $z-1 ),
                                                                  $this->grad($this->p[$BB+1], $x-1, $y-1, $z-1 ))));

        return $result;
    }

    function fade($t)
    {
        return $t * $t * $t * ( ( $t * ( ($t * 6) - 15) ) + 10);
    }

    function lerp($t, $a, $b)
    {
        //Make a weighted interpolation between points
        return $a + $t * ($b - $a);
    }

    function grad($hash, $x, $y, $z)
    {
        // CONVERT LO 4 BITS OF HASH CODE INTO 12 GRADIENT DIRECTIONS
        $h = $hash & 15;
        $u = $h<8 ? $x : $y;
        $v = $h<4 ? $y : ($h==12||$h==14 ? $x : $z);

        return (($h&1) == 0 ? $u : -$u) + (($h&2) == 0 ? $v : -$v);
    }

    function random2D($x, $y)
    {
        $x += $this->seed;
        $y += $this->seed;

        $value = 0.0;
        $initialSize = $size = 3;

        while ($size >= 1) {
            $value += $this->smoothNoise($x*3 / $size, $y*3 / $size, 100 / $size);
            $size--;
        }

        if ($value < -1) $value = -1;
        if ($value > 1) $value = 1;

        return $value;
    }
}