<?php
    //
    // prepare rotating chunks as videobuf for vlines table
    //

    $Vmem = Array();
    $bitscolor = Array(0=>0x000000, 1=>0x000001, 2=>0x000100, 3=>0x000101, 4=>0x010000, 5=>0x010001, 6=>0x010100, 7=>0x010101);

    function put_pixel ($x, $y, $col)
    {
        global $bitscolor, $Vmem;
        if ($x<0) $x = 0;
        if ($x>319) $x = 319;
        if ($y<0) $y = 0;
        if ($y>255) $y = 255;
        $x_frac = ($x & 0x07);
        $dword_num = intval($y*40) + intval($x>>3);
        $dword = isset($Vmem[$dword_num]) ? $Vmem[$dword_num] : 0x000000;
        $mask = (~(0x010101 << $x_frac))&0xFFFFFF;
        $dword = ($dword & $mask);
        $colr = ($bitscolor[$col] << $x_frac);
        $dword = ($dword | $colr);
        $Vmem[$dword_num] = $dword;
    }

    $a2 = 0;
    $da2 = 2.0*M_PI / 256.0;
    for ($angle256=0; $angle256<256; $angle256++)
    {
        $angle = 2 * M_PI * $angle256 / 256.0;
        // modify tilt
        $tiltx = 40*cos($a2);
        // modify radius a bit
        $radius = 70 + 20*sin($a2);
        $a2 += $da2;
        //
	    $da = (2.0*M_PI/7.0);
        for ($a=0.0,$c=1; $c<=7; $c++,$a+=$da)
        {
            //
            $x1 = intval(160 + $tiltx + $radius*cos($angle+$a));
            $x2 = intval(160 + $tiltx + $radius*cos($angle+$a+$da));
            if ($x1 < $x2) {
                // 0-color must be dithered
                if ($c != 0) {
                    for ($j=$x1; $j<$x2-2; $j++) put_pixel($j, $angle256, $c);
                } else {
                    for ($j=$x1; $j<$x2-2; $j++) {
                        $c2 = 2 + ($j&1);
                        put_pixel($j, $angle256, $c2);
                    }
                }
            }
        }
    }

    $Apl2Vm = Array();
    for ($i=0, $a=040000/2; $i<256; $i++, $a+=40)
    {
        $Alp2Vm[$i] = $a;
    }


    // rotation angle table
    $f = fopen ("rottbl.mac", "w");
    fputs($f, "RotTbl:\n");
    $n=0;
    $ticksCount = 256;
    $ashift = 0.0; // 10.0*(2.0*M_PI/$ticksCount);
    $afrom = -(M_PI/2.0) + $ashift;
    $ato = 1.5*M_PI - $ashift;
    $aadd = ($ato-$afrom)/$ticksCount;
    $angl = $afrom;
   	for ($i=0; $i<$ticksCount; $i++)
    {
   	    if ($n==0) fputs($f, "\t.byte\t");
        $bb = 256.0*(sin($angl)+1.0)/2.0; $angl += $aadd;
        $bb = intval($bb) & 0xFFFF;
        if ($bb > 255) $bb = 255;
        fputs($f, decoct($bb));
        $n++; if ($n<10 && $i!=($ticksCount-1)) fputs($f, ", "); else { $n=0; fputs($f, "\n"); }
    }
    fputs($f, "\n");
    fclose($f); 


    // twist angle table
    $f = fopen ("../twstbl.mac", "w");
    fputs($f, "TwsTbl:\n");
    $n=0;
    $ticksCount = 512;
    $afrom = 0.0;
    $ato = 2*M_PI;
    $aadd = ($ato-$afrom)/$ticksCount;
    $angl = $afrom;
   	for ($i=0; $i<$ticksCount; $i++)
    {
   	    if ($n==0) fputs($f, "\t.word\t");
        $bb = 0.0 + 0.5*sin($angl); $angl += $aadd;
        // convert it to fixed point float two bytes [int part][1/256 part]
        $b1 = intval($bb);
        if ($bb < 0 && $b1 == 0) $b1 = 0xFF;
        $b2 = $bb*256 % 256;
        $b1 = $b1 & 0xFF;
        $b2 = $b2 & 0xFF;
        // make word from this two bytes
        echo "$bb $b1 $b2\n";
        $bb = $b1 << 8 | $b2;
        //
        $bb = $bb & 0xFFFF; // just in case
        fputs($f, decoct($bb));
        $n++; if ($n<10 && $i!=($ticksCount-1)) fputs($f, ", "); else { $n=0; fputs($f, "\n"); }
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

    // write CPU vram
    $f = fopen ("../vramcpu.mac", "w");
    fputs($f, "V20000Cpu:\n");
    $n=0;
   	for ($i=0; $i<256*40; $i++)
    {
   	    if ($n==0) fputs($f, "\t.word\t");
        $bb = isset($Vmem[$i]) ? $Vmem[$i] : 0;
        $bb = ($bb >> 8) & 0xFFFF;
        fputs($f, decoct($bb));
        $n++; if ($n<10) fputs($f, ", "); else { $n=0; fputs($f, "\n"); }
    }
    fputs($f, "\n");
    fclose($f); 

    // write PPU vram
    $f = fopen ("../vramppu.mac", "w");
    fputs($f, "V20000Ppu:\n");
    $n=0;
   	for ($i=0; $i<256*40; $i++)
    {
   	    if ($n==0) fputs($f, "\t.byte\t");
        $bb = isset($Vmem[$i]) ? $Vmem[$i] : 0;
        $bb = $bb & 0xFF;
        fputs($f, decoct($bb));
        $n++; if ($n<10) fputs($f, ", "); else { $n=0; fputs($f, "\n"); }
    }
    fputs($f, "\n");
    fclose($f); 

?>