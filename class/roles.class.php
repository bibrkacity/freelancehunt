<?php
class roles
{

const MANAGER	= 1;
const ENGINEER	= 3;
const ACCOUNTER	= 4;
const OFFICE_CHIEF	= 5;
const DIRECTOR	= 7;
const ADMIN		= 9;

public static function listing()
	{
	$array = 
		[
		 self::MANAGER	=>'Менеджер'
		,self::ENGINEER	=>'Инженер'
        ,self::ACCOUNTER	=>'Бухгалтер'
		,self::OFFICE_CHIEF	=>'Начальник офиса'
		,self::DIRECTOR	=>'Директор'
		,self::ADMIN	=>'Администратор'
		];

	return $array;

	}


}

