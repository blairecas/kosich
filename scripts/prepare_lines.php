<?php
    //
    // prepare rotating chunks as videobuf for vlines table
    //

    $Vmem = Array();
    $bitscolor = Array(0=>0x0000, 1=>0x0100, 2=>0x0001, 3=>0x0101);

    function put_pixel ($x, $y, $col)
    {
        global $bitscolor, $Vmem;
        if ($x<0) $x = 0;
        if ($x>319) $x = 319;
        if ($y<0) $y = 0;
        if ($y>255) $y = 255;
        $x_frac = ($x & 0x07);
        $word_num = intval($y*40) + intval($x>>3);
        $word = isset($Vmem[$word_num]) ? $Vmem[$word_num] : 0x0000;
        $mask = (~(0x0101 << $x_frac))&0xFFFF;
        $word = ($word & $mask);
        $colr = ($bitscolor[$col] << $x_frac);
        $word = ($word | $colr);
        $Vmem[$word_num] = $word;
    }

    for ($angle256=0; $angle256<=255; $angle256++)
    {
        $angle = 2 * M_PI * $angle256 / 256.0;
        $radius = 120;
        for ($a=0.0,$c=0; $c<=3; $c++,$a+=(M_PI/2))
        {
            $x1 = intval(160 + $radius*cos($angle+$a));
            $x2 = intval(160 + $radius*cos($angle+$a+(M_PI/2)));
            if ($x1 < $x2) {
                // 0-color must be dithered
                if ($c != 0) {
                    for ($j=$x1; $j<$x2-6; $j++) put_pixel($j, $angle256, $c);
                } else {
                    for ($j=$x1; $j<$x2-6; $j++) {
                        $c2 = 2 + ($j&1);
                        put_pixel($j, $angle256, $c2);
                    }
                }
            }
        }
    }

    $Apl2Vm = Array();
    for ($i=0, $a=010000; $i<256; $i++, $a+=40)
    {
        $Alp2Vm[$i] = $a;
    }


    $f = fopen ("sintbl.mac", "w");
    fputs($f, "SinTbl:\n");
    $n=0;
   	for ($i=0; $i<256; $i++)
    {
   	    if ($n==0) fputs($f, "\t.word\t");
        $bb = 255.0*((sin(2.0*M_PI*$i/256.0) + 1.0)/2);
        $bb = intval($bb) & 0xFFFF;
        fputs($f, decoct($bb));
        $n++; if ($n<10 && $i!=255) fputs($f, ", "); else { $n=0; fputs($f, "\n"); }
    }
    fputs($f, "\n");
    fclose($f); 


    $f = fopen ("alp2vm.mac", "w");
    fputs($f, "Alp2Vm:\n");
    $n=0;
   	for ($i=0; $i<256; $i++)
    {
   	    if ($n==0) fputs($f, "\t.word\t");
        $bb = $Alp2Vm[$i];
        fputs($f, decoct($bb));
        $n++; if ($n<10 && $i!=255) fputs($f, ", "); else { $n=0; fputs($f, "\n"); }
    }
    fputs($f, "\n");
    fclose($f); 

    $f = fopen ("vram.mac", "w");
    fputs($f, "V20000:\n");
    $n=0;
   	for ($i=0; $i<256*40; $i++)
    {
   	    if ($n==0) fputs($f, "\t.word\t");
        $bb = isset($Vmem[$i]) ? $Vmem[$i] : 0;
        fputs($f, decoct($bb));
        $n++; if ($n<10) fputs($f, ", "); else { $n=0; fputs($f, "\n"); }
    }
    fputs($f, "\n");
    fclose($f); 
    
?>