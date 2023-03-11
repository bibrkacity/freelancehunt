<?php
//Класс работы с select
class select 
{
protected $name;       // имя select-a (списка или комбобокса)
protected $options;    // асоциативный массив "значение=>текст"
protected $value;      // значение select-a (списка или комбобокса), может быть массивом
protected $attributes; // доп. атрибуты тега
protected $disabled; //Метка в конце имени опции, указывающая на её недоступность 
protected $show_all; //Если true -- ингорирует метки недоступности и вычистит их 
protected $ignore; //Метка в конце имени опции, указывающая, что эту опцию показывать не надо 

function __construct($name,$options,$value=NULL,$attributes="",$disabled=DISABLED,$show_all=false,$ignore=IGNORE)
	{
	$this->name=$name;
	$this->options=$options;
	$this->value=$value;
	if (!is_array($this->value))
		settype($this->value,"string");
	$this->attributes=$attributes;
	$this->disabled=$disabled;
	$this->show_all=$show_all;
	$this->ignore=$ignore;
	}

//---------------------------------

function html($break = true)
	{
	$select="\n<select name=\"".$this->name."\" ".$this->attributes.">";

	$options="";

	$len_ignore=strlen($this->ignore);
	$len_disabled=strlen($this->disabled);	

	if( !is_array($this->options))
		{ /* Надо бросить исклюение - что опции не массив*/ }
	elseif( count($this->options) == 0 )
		{ /* Тут, скорее всего, ничего не надо делать */ }
	else
	  foreach ($this->options as $value=>$text)
		{

		if (substr($text,strlen($text)-$len_ignore,$len_ignore)==$this->ignore)
			{
			continue;
			}

		$options.="\n<option value=\"$value\"";

		if (is_array($this->value)) //Для списка с Multiple 
			{
			$svalue=trim(strval($value));
			if (array_key_exists ( $svalue, $this->value ))
				$options.=" selected=\"selected\"";
			}
		else //Т.е. значение -- "скалярная" велоичина (не массив)
			{
			settype($value,"string");
			if ($value==$this->value)
				{
				$options.=" selected=\"selected\"";
				}
			}

		if (substr($text,strlen($text)-$len_disabled,$len_disabled)==$this->disabled)
				{
				if ($this->show_all==false)
					{
					$options.=" disabled=\"disabled\"";
					}
				$text1=substr($text,0,strlen($text)-$len_disabled);
				}
		else
				$text1=$text;

		$options.=">$text1</option>";
		}
	
	$select.=$options."\n</select>";

	if($break)
		$select .= "\n";
	return $select;
	}

//---------------------------------



}//end class
?>