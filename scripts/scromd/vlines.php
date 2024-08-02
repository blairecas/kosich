<?php
	$f = fopen("vtable.txt", "w");
	$vaddr = 0100000;
	$paddr_start = 01130;
	$paddr = $paddr_start + 4;
	$count = 288;
	for ($i=0,$n=0; $i<$count; $i++)
	{
		if ($n == 0) fputs($f, "\t.word\t");
		fputs($f, decoct($vaddr) . "," . decoct($paddr));
		$vaddr += 82;
		$paddr += 4;
		$n++;
		if ($n<4) fputs($f, ", "); else { $n=0; fputs($f, "\n"); }
	}

	fputs($f, "\n\n");
	
	$vaddr = 0100000;
	$count = 288;
	for ($i=0,$n=0; $i<$count; $i++)
	{
		if ($n == 0) fputs($f, "\t.word\t");
		fputs($f, decoct($vaddr));
		$vaddr += 40;
		$n++; if ($n<16) fputs($f, ", "); else { $n=0; fputs($f, "\n"); }
	}
	fclose($f); 
?>