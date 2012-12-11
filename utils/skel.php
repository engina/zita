<?php
const src_dir = '../gen/Models';
const dst_dir = '../Models/';

require_once('lib/Template.php');

$gens = scandir(src_dir);
foreach($gens as $g)
{
	if(substr($g, -4) != '.php') continue;
	$tpl = new Template('generators/php/templates/skel.txt');
	$tpl->set('CLASS', basename($g, '.php'));
	$dst = dst_dir.DIRECTORY_SEPARATOR.$g;
	if(file_exists($dst))
	{
		echo "Skipping $g\n";
		continue;
	}
	echo "Creating $g\n";
	file_put_contents($dst, $tpl->process());
}
?>
