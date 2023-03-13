<?php

/**
 * Построение тега select
 */
class select
{
    /**
     * имя select-a (списка или комбобокса)
     * @var string
     */
    protected string $name;

    /**
     * асоциативный массив "значение=>текст"
     * @var array
     */
    protected array $options;

    /**
     * Значение select. Для списка может быть массивом
     * @var string|array|NULL
     */
    protected string|array|null $value;

    /**
     * доп. атрибуты тега
     * @var string
     */
    protected string $attributes;

    /**
     * Метка в конце имени опции, указывающая на её недоступность
     * @var string
     */
    protected string $disabled;

    /**
     * Если true -- ингорирует метки недоступности и вычистит их
     * @var bool
     */
    protected bool $show_all;

    /**
     * Метка в конце имени опции, указывающая, что эту опцию показывать не надо
     * @var string
     */
    protected string $ignore;

    /**
     * Конструктор
     * @param string $name имя select-a (списка или комбобокса)
     * @param array $options  асоциативный массив опций "значение=>текст"
     * @param string|array|NULL $value Значение select. Для списка может быть массивом
     * @param string $attributes  доп. атрибуты тега
     * @param string $disabled  Метка в конце имени опции, указывающая на её недоступность
     * @param bool $show_all Если true -- ингорирует метки недоступности и вычистит их
     * @param string $ignore Метка в конце имени опции, указывающая, что эту опцию показывать не надо
     */
    function __construct(string $name, array $options, string|array $value=NULL, string $attributes="", string $disabled=DISABLED, bool $show_all=false, string $ignore=IGNORE)
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

    /**
     * Построение HTML-кода тега SELECT
     * @param bool $break ставить ли конец строки после тега
     * @return string
     */
    function html(bool $break = true) : string
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

}
