<?php

/**
Методы общего назначения. Фактически - библиотека функций
*/
class common
{

    /**
     * Получение из текста числа. Обрабатываем запятые и пробелы
     * @param string|null $s текст, из которого получаем число
     * @param string $type числовой тип, в который нужно преобразовать
     * @param mixed|null $default что вернуть, если не получилось преобразовать
     * @return mixed
     */
    public static function toNumber(string $s=null, string $type="float", mixed $default=null): mixed
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


    /**
     * Убрать из пути переходы на верхний уровень
     * @param string $path путь, содержащий переходы на верхний уровень
     * @return string
     */
    public static function path_compact(string $path) : string  //
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

    /**
     * Установка констант, определяющий, мобитльное устройство или нет
     * @return void
     */
    public static function mobile():void
    {

        $re = '/Android|webOS|iPhone|iPad|iPod|BlackBerry|BB|PlayBook|IEMobile|Windows Phone|Kindle|Silk|Opera Mini/i' ;

        $agent =$_SERVER['HTTP_USER_AGENT'];

        $mobile = preg_match($re, $agent)  ? 1 : 0;

        define('MOBILE',$mobile);

        $_SESSION['mobile']=$mobile;
    }

}
