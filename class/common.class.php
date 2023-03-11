<?php

/*===================================
Методы общего назначения. Фактически - библиотека функций
====================================*/

class common
{

//--------------------------------------

//Получение из текста числа
// Обрабатываем запятые и пробелы
public static function toNumber($s,$type="float",$default=null)
	{
	if($s===null)
		{
		return $default;
		}

	$s=trim($s);
	$s=preg_replace("/,/",".",$s);
	$s=preg_replace("/ /","",$s);

	if (!is_numeric($s))
		$n=$default;
	else
		{
		$n=$s;
		settype($n,$type);
		}

	return $n;
	}



//---------------------------------------

public static function path_compact($path) //Убрать из пути переходы на верхний уровень
	{
	$re='/\\\/';
	$path = preg_replace($re,'/',$path);

	$path = preg_replace('/\.\.$/','../',$path);


	$re = '/\/[^\.][^\/]*\/\.\.\//';

	while(preg_match($re,$path))
		$path=preg_replace($re,'/',$path);

	$path=preg_replace('/\/$/','',$path);

	return $path;
	}


//------ формирование части URL-а по имени ----------

    public static function mobile()
    {

        $re = '/Android|webOS|iPhone|iPad|iPod|BlackBerry|BB|PlayBook|IEMobile|Windows Phone|Kindle|Silk|Opera Mini/i' ;

        $agent =$_SERVER['HTTP_USER_AGENT'];

        $mobile = preg_match($re, $agent)  ? 1 : 0;

        define('MOBILE',$mobile);

        $_SESSION['mobile']=$mobile;
    }

}
?>