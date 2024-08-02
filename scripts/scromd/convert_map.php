<?php

    $img = imagecreatefrompng('./mario_map.png');
    $width = imagesx($img);
    $height = imagesy($img);
    echo "Image: $width x $height\n";
    $tiles_dx = intval($width / 16);
    $tiles_dy = intval($height / 16);
    echo "Tiles: $tiles_dx x $tiles_dy\n";
    
    if ($tiles_dy != 15) {
	echo "Only works for x15 tiles by Y!\n";
	exit(0);
    }

    // tiles array
    $tilesArray = Array();
    // tiles map
    $tilesMap = Array();
    
    // scan image and create map and array
    for ($tilex=0; $tilex<$tiles_dx; $tilex++)
    {
        for ($tiley=0; $tiley<$tiles_dy; $tiley++)
        {
            // create a tile (bytes rgb from up to down and then left to right)
	    $tile = Array();
	    for ($y=0; $y<64; $y++)	// tiles will be 64 elements long, 4 sections by 16 elements
            {
                $res = 0; 
		for ($x=0; $x<4; $x++)	// cycle by 4 pix and double them (tile is 32x16 pix)
                {
                    $py = $tiley*16 + ($y&0x0F);
		    $px = $tilex*16 + (($y&0x30)>>2) + $x;
		    $res = ($res >> 2) & 0x00FFFFFF;
                    $rgb_index = imagecolorat($img, $px, $py);
                    $rgba = imagecolorsforindex($img, $rgb_index);
                    $r = $rgba['green']; // change to GRB
                    $g = $rgba['red'];
                    $b = $rgba['blue'];
		    if ($r > 127) { $res = $res | 0x00C00000; }	// set by 2-pix, enlarge tile by 2
                    if ($g > 127) { $res = $res | 0x0000C000; }
                    if ($b > 127) { $res = $res | 0x000000C0; }
		    // echo "$py $px $res\n";
                }
                array_push($tile, $res);
            }
	    // now check do we already have this tile
            $found = -1;
	    for ($i=0; $i<count($tilesArray); $i++)
            {
		$diff = array_diff_assoc($tile, $tilesArray[$i]);
                if (count($diff) == 0) {
                    $found = $i;
		    break;
                }
	    }
	    // if not found - add to tilesArray
	    if ($found < 0) {
		$found = array_push($tilesArray, $tile) - 1;
	    }
	    // add to tilesMap
	    array_push($tilesMap, $found);
        }
        // add extra 0 to tilesMap to make them .even 16
	array_push($tilesMap, 0);
    }
    
    echo "Total tiles in map: ".count($tilesMap)."\n";
    echo "Different tiles count: ".count($tilesArray)."\n";
    
    ////////////////////////////////////////////////////////////////////////////
    
    echo "Writing tiles map ...\n";
    $f = fopen ("tilesmap.txt", "w");
    fputs($f, "TILMAP:\t.byte\t");
    $total = count($tilesMap);
    for ($i=1; $i<=$total; $i++)
    {
	fputs($f, decoct($tilesMap[$i-1]));
	if (($i%($tiles_dy+1)) != 0) {
	    if ($i<$total) fputs($f, ", ");
	} else {
	    if ($i<$total) fputs($f, "\n\t.byte\t");
	}
    }
    fputs($f, "\n\n");
    fclose($f);
    
    ////////////////////////////////////////////////////////////////////////////
    
    echo "Writing CPU tiles data ...\n";
    $f = fopen ("tilescpu.txt", "w");
    for ($t=0; $t<count($tilesArray); $t++)
    {
	$tile = $tilesArray[$t];
	fputs($f, "TCP".str_pad("".$t, 3, "0", STR_PAD_LEFT).":");
	for ($i=0, $n=0; $i<64; $i++)
	{
	    if ($n==0) fputs($f, "\t.word\t");
	    $rg = ($tile[$i] &0xFFFF00) >> 8;
	    fputs($f, decoct($rg));
	    $n++;
	    if ($n<8) fputs($f, ", "); else { $n=0; fputs($f, "\n"); }
	}
    }
    fputs($f, "\n\n");
    fclose($f);
    
    ////////////////////////////////////////////////////////////////////////////
    
    echo "Writing PPU tiles data ...\n";
    $f = fopen ("tilesppu.txt", "w");
    for ($t=0; $t<count($tilesArray); $t++)
    {
	$tile = $tilesArray[$t];
	fputs($f, "TPP".str_pad("".$t, 3, "0", STR_PAD_LEFT).":");
	$n=0;
	for ($j=0; $j<2; $j++)
	for ($i=0; $i<16; $i++)
	{
    	    if ($n==0) fputs($f, "\t.word\t");
	    $bb = ($tile[$j*32+$i] & 0xFF) | (($tile[$j*32+$i+16] & 0xFF) << 8);
	    fputs($f, decoct($bb));
	    $n++;
	    if ($n<8) fputs($f, ", "); else { $n=0; fputs($f, "\n"); }
	}
    }
    fputs($f, "\n\n");
    fclose($f);

?>