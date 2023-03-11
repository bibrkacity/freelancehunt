<?php

class view
{

public static function render($view,$obj)
	{

	$filename = self::getDir();

	$filename .= '/'.$view.'.html';

	$html = file_get_contents($filename);

	$html = self::parse($html,$obj);

	return $html;

	}

//-----------------------------------------

public static function parse($html,$obj)
	{

	$re = '/##([^#]+)##/';

	if( preg_match_all($re,$html,$m,PREG_PATTERN_ORDER) )
		{

		foreach($m[1] as $one)
			$html = self::parse_replace($html,$one,$obj);
		
		}

	return $html;

	}

/*===============================
  PROTECTED
=================================*/

protected static function getDir()
	{

	$dir = __DIR__.'/../views';
	$dir = common::path_compact($dir);

	return $dir;

	}

//-----------------------------------------

protected static function parse_replace($html,$one,$obj)
	{

	if(preg_match('/^[A-Z0-9_]+$/',$one))
		$html=preg_replace('/##'.$one.'##/',constant($one),$html);
	elseif(preg_match('/^[A-Za-z0-9_]+$/',$one))
		$html=preg_replace('/##'.$one.'##/',$obj->$one,$html);
	elseif(preg_match('/^([A-Za-z0-9_]+)\(\)$/',$one,$m))
		{
		$name = $m[1];
		$re= '/##'.$name.'\(\)##/';
		$text = $obj->$name();
		$html=preg_replace($re,$text,$html);
		}

	return $html;
	}


}


?>