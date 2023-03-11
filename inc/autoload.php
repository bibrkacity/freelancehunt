<?php

spl_autoload_register('autoload');

function autoload($className)
	{

	$dir=__DIR__.'/../class';

	$re = '/^((\w+)\\\)+(\w+)/';

	if(preg_match($re, $className, $m))
		{

		$ns = $m[0];

		$ns = preg_replace('/\\\/','/',$ns);

		$filename = $dir.'/'.$ns.'.class.php';

		if(file_exists($filename))
			{
			require_once $filename;
			return;
			}
		else
			{

			$filename = $dir.'/'.$ns.'.php';
			if(file_exists($filename))
				{
				require_once $filename;
				return;
				}
			else
				{
				print('<h1 style="color:red">Class '.$className.' is not found ('.$filename.')</h1><pre>');
				print_r(debug_backtrace());
				die('</pre>');
				}
			}
		}

	$filename=$dir.'/'.strtolower($className).'.class.php';

	if(file_exists($filename))
		{
		require_once $filename;
		}
	else
		{
		print('<h1 style="color:red">Class '.$className.' is not found ('.$filename.')</h1><pre>');
		print_r(debug_backtrace());
		die('</pre>');
		}
	}
?>