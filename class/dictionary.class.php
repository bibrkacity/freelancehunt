<?php

/**
 * Базовый класс для страниц справочников
 */
abstract class dictionary
{

	protected $search_title;  /* Заголовок окна поиска */

	protected $action;  /* Файл вызывающей страницы */
	protected $_post;	// Метод POST
	protected $_get;	// Метод GET

	protected int $page;	//Номер страницы справочника (когда таблица многостраничная)
	protected $title;	//Заголовок стпавочника (<h1> над таблицей)

	protected $sorting_fields;	//Поля сортировки (массив)

	protected $filters_default; //Фильтры и их значения по умолчанию (массив)

	protected $filters; //Фильтры и их значения по умолчанию (массив)
	protected $optional_fields;  //ЗАРЕЗЕРВИРОВАНО на будущее

	protected $dict_class; //Текущий класс

	protected $in_page;		//Число строк в таблдице объектов на одной странице (при многостраницной таблице)

	protected $conn;		//Подключение к базе данных (по умолчанию - текущее)

    protected $error;

//---------------------------------------------------

abstract public function search();  //HTML-код, вставляемый в окно поиска

abstract protected function exists()  : string;	//Таблица объектов
abstract protected function filters() : string;  //Строка, содержащая критерии отбора (в шапке таблицы)

abstract protected function query_pages() : string;	//Составление запроса количества отобранных объектов
abstract protected function query_total() : string;  //Составление запроса количества всех объектов


/* конструктор для производных классов */
public function __construct($action,$get,$post,$connection=null)
	{
		if(isset($get['inpage']))
			{
			$this->in_page=common::toNumber($get['inpage'],'int',20);
			}
		elseif(isset($post['inpage']))
			{
			$this->in_page=common::toNumber($post['inpage'],'int',20);
			}
		else
			{
			$this->in_page = 20;
			}

		$this->action=$action;
		$this->_get=$get;
		$this->_post=$post;

		$this->page=1;
		if (isset($this->_get["page"]))
			{
			$this->page=common::toNumber($this->_get["page"],'int',1);
			}

		if($connection==null)
			{
			global $conn;
			$this->conn= $conn;
			}
		else
			{
			$this->conn= $connection;
			}

	}

// запускается при  попытке получить извне значенеи защищённого свойства 
public function __get($property)
	{
	if(!property_exists($this,$property))
		return null;

	return $this->$property;
	}
//---------------------------------------------------
public function html(): string //HTML-код страницы справочника
	{

	global $conn;

		$html=$this->js();

		$html.="<div id=\"dictionary_caption\">";
			
		$html.="<h1>".$this->title."</h1>";

		$html.="<div class=\"buttons\">";

		if(count($this->filters)>0)
		    $html.= $this->button_search();

        $html.= $this->button_additional();

		$html.="</div>";
					
		$html.="</div><!-- /id=\"dictionary_caption\" -->";
            
		$html.= $this->exists();

	return $html;
	}


/*======================================
PROTECTED
========================================*/

protected function combo() //Список вариантов количества строк на одной странице
	{
	$pages = array
		(
		  '0' => 'Всё'
		,'12' => '12'
		,'20' => '20'
		,'50' => '50'
		,'100' => '100'
		,'200' => '200'
		,'500' => '500'
		);

	$url=$this->action."?inpage='+this.value+'";
	foreach($this->filters as $field2=>$value2)
		{
		if($this->filters_default[$field2] != $value2)
			{
			$url.="&amp;".$field2."=".urlencode($value2);
			}
		}
	foreach($this->sorting_fields as $field2=>$value2)
		{
		if($value2!="no")
			{
			$url.="&amp;".$field2."=".$value2;
			}
		}

	$select = new select('inpage',$pages,$this->in_page,'onchange="location.href=\''.$url.'\'"');
	return 'Вивести на сторінку:'.$select->html();


	}

//-----------------------------------------------------------

protected function button_search()
	{

	$html=" <input class=\"knopka\" type=\"button\" value=\"Поиск\" onclick=\"open_filters()\" /><span id=\"tri\">&#9658;</span>";

	$html .= '<div id="search_popup" class="hidden">';
	$html .= 	$this->search();
	$html .= '</div>';

	return $html;

	}

    protected function button_additional() : string
    {
        return '';
    }
//-----------------------------------------------------------

protected function fill_arrays() //Заполнение массивов фильтров и параметров сортировки на основе GET
	{

	$new_array=array();
	$allowed=array('asc','desc');
	foreach($this->sorting_fields as $field)
		{
		$value='no';
		if(isset($this->_get[$field]))
			{
			$v=strtolower($this->_get[$field]);
			if(in_array($v,$allowed))
				$value=$v;
			}
		$new_array[$field]=$value;
		}

	$this->sorting_fields=$new_array;

	$this->fill_arrays_filters();
	}

//------------------------------------
protected function fill_arrays_filters()  //Заполнение массивов фильтров на основе GET
	{
	$this->filters=$this->filters_default;
	foreach($this->filters as $field=>$f_value)
		{
		$value=$f_value;
		if(isset($this->_get[$field]))
			{
			$value=mysqli_real_escape_string($this->conn, $this->_get[$field]);
			}

		$this->filters[$field]=$value;
		}
	}

//------------------------------------

protected function total()  //Получение количества всех объектов справочника
	{
	$query=$this->query_total();

	$result=mysqli_query($this->conn, $query);
	while($row=mysqli_fetch_row($result))
		{
		$n=$row[0];
		}
	mysqli_free_result($result);

	return $n;	
	}

//------------------------------------

protected function pages(&$n)   //Получение HTML-кода ссылок на страницы справочника
	{
	$filters="inpage=".$this->in_page;
	foreach($this->filters as $name=>$value)
		{
		if($value!=$this->filters_default[$name])
			{
			$filters.="&amp;$name=".urlencode($value);
			}
		}

	$order="";
	foreach($this->sorting_fields as $name=>$value)
		{
		if($value!="no")
			{
			$order.="&amp;$name=".$value;
			}
		}

	foreach($this->optional_fields as $name=>$value)
		{
		if($value!="none")
			{
			$order.="&amp;$name=".$value;
			}
		}

	$query=$this->query_pages();

	$result=mysqli_query($this->conn,$query) ;
	$n=0;
	while ($row=mysqli_fetch_row($result))
		{
		$n=$row[0];
		}
	mysqli_free_result($result);

	$pages=0;
	if($this->in_page > 0)
		$pages=ceil($n/$this->in_page);
	if($pages<2)
		{
		return '<div class="pages">'.$this->combo().'</div>';
		}
	
	$html = '<div class="pages">'.$this->combo();

	$switch1=false;
	$switch2=false;

	for ($i=1;$i<=$pages;$i++)
		{

		if(($i>8)&&($i<($this->page-7))&&($pages>50))
			{
			if(!$switch1)
				{
				$html.=" ... ";
				$switch1=true;
				}
			continue;
			}

		if(($i>($this->page+7))&&($i<($pages-7))&&($pages>50))
			{
			if(!$switch2)
				{
				$html.=" ... ";
				$switch2=true;
				}
			continue;
			}

		if($i==$this->page)
			{
			$html.="<span style=\"background:blue;color:white;font-weight:bold\">$i</span> ";
			}
		else
			{
			$html.="<a id=\"page$i\" href=\"".$this->action."?".$filters.$order."&amp;page=$i\">$i</a> ";
			}
		}
	$html.='</div>';
	return $html;
	}


//-----------------------------------------------------


protected function js_common()
	{
	$js="
	<script type=\"text/javascript\">
		
		function switch_field(field_name)
			{
			var title_th=document.getElementById('table_caption');
			var colspan=parseInt(title_th.colSpan);
			var th=document.getElementById(field_name+'00');

			var button1=document.getElementById('th_'+field_name);
			var button2=document.getElementById('panel_'+field_name);

			var display;
			if(th.style.display=='none') 
				{
				display='';
				title_th.colSpan=(colspan+1);

				if(button1!=null)
					{
					button1.value='-';
					button1.title='Скрыть';
					}

				if(button2!=null)
					{
					button2.value='-';
					button2.title='Скрыть';
					}

				var in_xls=document.getElementById('excel_'+field_name);
				if(in_xls!=null)
					{
					in_xls.value='1';
					}
				else
					{
					var f=document.getElementById('excel');
					if(f!=null)
						{
						var hid=document.createElement('INPUT');
						hid.type='hidden';
						var hid=f.appendChild(hid);
						hid.value='1';
						hid.id='excel_'+field_name;
						hid.name=field_name;
						}
					}
				}
			else
				{
				display='none';
				title_th.colSpan=(colspan-1);

				if(button1!=null)
					{
					button1.value='+';
					button1.title='Показать';
					}
				if(button2!=null)
					{
					button2.value='+';
					button2.title='Показать';
					}	
				var in_xls=document.getElementById('excel_'+field_name);
				if(in_xls!=null)
					{
					in_xls.value='none';
					}
				}

			th.style.display=display;

			var cells=document.getElementsByTagName('td');
			var re=new RegExp(field_name+'[0-9]+','g');
			for (var i=0;i<cells.length;i++)
				{
				if(cells[i].id!=null)
					{
					if(re.test(cells[i].id))
						{
						cells[i].style.display=display;
						}
					}

				}

			var links=document.links;		
			var re=/page[0-9]+/;
			for (var i=0;i<links.length;i++)
				{
				if(links[i].id!=null)
					{
					if(re.test(links[i].id))
						{
						links[i].href=links[i].href+'&'+field_name+'='+display;
						}
					}
				if(links[i].className!=null)
					{
						if(links[i].className=='sorting')
							{
							links[i].href=links[i].href+'&'+field_name+'='+display;
							}
					}
				}
			}
		
//=============================================================

		var panel_state='none';

		function switch_panel()
			{
			var div_panel=document.getElementById('panel');
			var button=document.getElementById('panel_button');

			var display;
			if(panel_state=='none') 
				{
				display='block';
				button.value='Скрыть панель добавления полей';
				}
			else
				{
				display='none';
				button.value='Показать панель добавления полей';
				}
			div_panel.style.display=display;
			panel_state=display;

			}

		//=============================================================

";

	$js .= $this->js_open_filters();

	$js .= "	
	</script>
		";		
	return $js;
	
	}	
//-----------------------------------------------------
protected function js_open_filters()
	{
	$js = "
		function open_filters()
			{
			var div = document.getElementById('search_popup');

			var className = div.className;

			div.className = (className == 'hidden') ? 'show' : 'hidden' ;

			var span = document.getElementById('tri');

			span.textContent = (className == 'hidden') ? String.fromCharCode(9650) : String.fromCharCode(9658);
			}
			";
	return $js; 

	}

//-----------------------------------------------------

function js_search()
	{
	$js="
	<script type=\"text/javascript\">

	function search_submit()
	{

		var query=new String('');
		";

		foreach($this->filters as $filter=>$value)
			{
			$js.="var el=document.getElementById('$filter');
			if(el!=null)
				{
				switch(el.nodeName)
					{
					case 'INPUT':
						switch(el.type)
							{
							case 'text':
							case 'hidden':

								var val_=el.value;
								break;
							case 'checkbox':
								if(el.checked==true)
									{
									var val_=el.value;
									}
								else
									{
									var val_=0;
									}
								break;

							default:
							break;
							}
						break;
					case 'SELECT':
					case 'TEXTAREA':
						var val_=el.value;
						break;
					default:
						if(el.length!=null)
							{
							for(var i=0;i<el.length;i++)
								{
								if(el[i].checked)
									{
									var val_=el[i].value;
									}
								}
							}
						break;
					}
				if(val_ != '".$this->filters_default[$filter]."')
					query+='&$filter='+val_;
				}//end if (el!=null)
				";
				}

		$js.="
		self.location.href='".$this->action."?inpage=".$this->in_page."'+query;
	}
	</script>
	";

	return $js;

	}

//----------------------------------------------

protected function table_sort($property)
	{

	$html="<table  class=\"sort\">";
	$html.="<tr>";
	$html.="<td>";

	if($this->sorting_fields[$property]!='asc')
		{
		$url=$this->sorting_url($property,'asc');
		$title="Отсортировать по возрастанию";
		$html.="<a  class=\"sorting\" href=\"$url\">&#9650;</a>";
		}
	else
		{
		$title="Отсортировано по возрастанию";
		$html.="&#9650;";
		}
	$html.="</td>";
	$html.="</tr>";

	$html.="<tr>";
	$html.="<td>";

	if($this->sorting_fields[$property]!='desc')
			{
			$url=$this->sorting_url($property,'desc');
			$title="Отсортировать по убыванию";
			$html.="<a class=\"sorting\" href=\"$url\">&#9660;</a>";
			}
	else
			{
			$title="Отсортировано по убыванию";
			$html.="&#9660;";
			}

	$html.="</td>";
	$html.="</tr>";
	$html.="</table>";	
				
	return $html;			
	}

//----------------------------------------------------------------
protected function sorting_url($field,$value)
	{

	$url_common="inpage=".$this->in_page;
	foreach($this->filters as $field2=>$value2)
		{
		if( $this->filters_default[$field2] !=  $value2)
			$url_common.="&amp;".$field2."=".urlencode($value2);
		}
	foreach($this->optional_fields as $field2=>$value2)
		{
		if($value!="none")
			{
			$url_common.="&amp;".$field2."=".$value2;
			}
		}
	//$url_common=substr($url_common,5);

	$url=$this->action."?$url_common&amp;$field=$value";

	return $url;

	}

//------------------------------------------

protected function th_sort($name,$sort_name)  
	{

	$html="<th class=\"usual_small\">";
		$html.="<table>";
		$html.="<tr>";
		$html.="<td>";
		$html.=$this->table_sort($sort_name);
		$html.="</td>";

		$html.="<th class=\"usual_small\" style=\"border:none\">";
		$html.=$name;
		$html.="</th>";

		$html.="</tr>";
		$html.="</table>";
	$html.="</th>";
	return $html;
	}


//------------------------------------------

protected function  colspan($colspan) //ЗАРЕЗЕРВИРОВАНА на будущее
	{
	return '';
	}

} //end class

