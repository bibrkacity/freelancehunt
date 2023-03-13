<?php

/**
 * Базовый класс для страниц справочников
 */
abstract class dictionary
{

    /**
     * Заголовок окна поиска
     * @var string
     */
    protected $search_title;

    /**
     * URL вызывающей страницы
     * @var string
     */
    protected string $action;

    /**
     * Метод POST
     * @var array
     */
    protected array $_post;

    /**
     * Метод GET
     * @var array
     */
	protected array $_get;

    /**
     * Номер страницы справочника (когда таблица многостраничная)
     * @var int|mixed
     */
    protected int $page;

    /**
     * Заголовок стравочника (<h1> над таблицей)
     * @var string
     */
    protected string $title;

    /**
     * Поля сортировки
     * @var array
     */
    protected array $sorting_fields;

    /**
     * Фильтры и их значения по умолчанию
     * @var array
     */
	protected array $filters_default;

    /**
     * Фильтры и их значения
     * @var array
     */
	protected array $filters;

    /**
     * Текущий класс (ребенок dictionary)
     * @var string
     */
    protected string $dict_class;

    /**
     * Число строк в таблдице объектов на одной странице (при многостраницной таблице)
     * @var int|mixed
     */
    protected int $in_page;

    /**
     * Подключение к базе данных (по умолчанию - текущее)
     * @var mixed|false|mysqli
     */
    protected mixed $conn;


    /**
     * HTML-код, вставляемый в окно поиска
     * @return mixed
     */
    abstract public function search();

    /**
     * Таблица объектов
     * @return string
     */
    abstract protected function exists()  : string;

    /**
     * Строка, содержащая критерии отбора (в шапке таблицы объектов)
     * @return string
     */
    abstract protected function filters() : string;

    /**
     * Составление запроса количества отобранных объектов
     * @return string
     */
    abstract protected function query_pages() : string;	//

    /**
     * Составление запроса количества всех объектов
     * @return string
     */
    abstract protected function query_total() : string;

    /**
     * конструктор для производных классов
     * @param string $action  URL вызывающей страницы
     * @param array $get  Данные GET
     * @param array $post Данные POST
     * @param mixed $connection Подключение к базе данных (по умолчанию - текущее)
     */
    public function __construct(string $action, array $get, array $post, mixed $connection=null)
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

    /**
     * запускается при  попытке получить извне значенеи защищённого свойства
     * @param string $property
     * @return mixed
     */
    public function __get(string $property) : mixed
	{
	if(!property_exists($this,$property))
		return null;

	return $this->$property;
	}

    /**
     * HTML-код страницы справочника
     * @return string
     */
    public function html(): string
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


    /**
     * Список вариантов количества строк на одной странице
     * @return string
     */
    protected function combo(): string
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

    /**
     * Кнопка поиска
     * @return string
     */
    protected function button_search(): string
    {

	$html=" <input class=\"knopka\" type=\"button\" value=\"Поиск\" onclick=\"open_filters()\" /><span id=\"tri\">&#9658;</span>";

	$html .= '<div id="search_popup" class="hidden">';
	$html .= 	$this->search();
	$html .= '</div>';

	return $html;

	}

    /**
     * Дополнительные кнопки (вот для тестового задания понадобилось)
     * @return string
     */
    protected function button_additional() : string
    {
        return '';
    }
//-----------------------------------------------------------

    /**
     * Заполнение массивов фильтров и параметров сортировки на основе GET
     * @return void
     */
    protected function fill_arrays(): void
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


    /**
     * Заполнение массивов фильтров на основе GET
     * @return void
     */
    protected function fill_arrays_filters(): void
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

    /**
     * Получение количества всех объектов справочника
     * @return int
     */
    protected function total() : int
	{
	$query=$this->query_total();
    $n=0;
	$result=mysqli_query($this->conn, $query);
	while($row=mysqli_fetch_row($result))
		{
		$n=$row[0];
		}
	mysqli_free_result($result);

	return $n;	
	}

//------------------------------------

    /**
     * Получение HTML-кода ссылок на страницы справочника
     * @param int $n
     * @return string
     */
    protected function pages(int &$n): string
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


    /**
     * JS-код, обслуживающий справочник
     * @return string
     */
    protected function js(): string
    {
        $js  = $this->js_general();
        $js .= $this->js_search();
        return $js;
    }


protected function js_general(): string
{
    return "
	<script type=\"text/javascript\" src=\"/javascript/dictionary/general.js\"></script>";
	}


//-----------------------------------------------------

protected function js_search(): string
{
	$js="
	<script type=\"text/javascript\">

	function search_submit()
	{
    let val_ = null;
	let query='';
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

								val_=el.value;
								break;
							case 'checkbox':
								if(el.checked==true)
									{
									val_=el.value;
									}
								else
									{
									val_=0;
									}
								break;

							default:
							break;
							}
						break;
					case 'SELECT':
					case 'TEXTAREA':
						val_=el.value;
						break;
					default:
						if(el.length!=null)
							{
							for(var i=0;i<el.length;i++)
								{
								if(el[i].checked)
									{
									val_=el[i].value;
									}
								}
							}
						break;
					}
				if(val_ != '".$this->filters_default[$filter]."')
					query+='&$filter='+val_;
				}
				";
				}

		$js.="
		self.location.href='".$this->action."?inpage=".$this->in_page."'+query;
	}
	</script>
	";

	return $js;

	}


    /**
     * Таблица для стрелочек сортировки. Архаика, надо при случае переписать
     * @param string $property имя поля сортировки (индекс массива $this->sorting_fields)
     * @return string
     */
    protected function table_sort(string $property): string
{

	$html="<table  class=\"sort\">";
	$html.="<tr>";
	$html.="<td>";

	if($this->sorting_fields[$property]!='asc')
		{
		$url=$this->sorting_url($property,'asc');
		$title="Відсортувати за зростанням";
		$html.="<a  class=\"sorting\" title=\"$title\" href=\"$url\">&#9650;</a>";
		}
	else
		{
		$html.="&#9650;";
		}
	$html.="</td>";
	$html.="</tr>";

	$html.="<tr>";
	$html.="<td>";

	if($this->sorting_fields[$property]!='desc')
			{
			$url=$this->sorting_url($property,'desc');
			$title="Сортувати у порядку спаданню";
			$html.="<a class=\"sorting\" title=\"$title\" href=\"$url\">&#9660;</a>";
			}
	else
			{
			$html.="&#9660;";
			}

	$html.="</td>";
	$html.="</tr>";
	$html.="</table>";	
				
	return $html;			
	}


    /**
     * URL для стрелочки сортировки
     * @param string $field  имя поля сортировки (индекс массива $this->sorting_fields)
     * @param string $value  asc, desc
     * @return string
     */
    protected function sorting_url(string $field, string $value): string
{

	$url_common="inpage=".$this->in_page;
	foreach($this->filters as $field2=>$value2)
		{
		if( $this->filters_default[$field2] !=  $value2)
			$url_common.="&amp;".$field2."=".urlencode($value2);
		}

	$url=$this->action."?$url_common&amp;$field=$value";

	return $url;

	}


    /**
     * Ячейка таблицы объектов с сортировкой
     * @param string $name имя фильтра (индекс массива $this->>filters)
     * @param string $sort_name asc, desc, no
     * @return string
     */
    protected function th_sort(string $name, string $sort_name): string
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

    /**
     * ЗАРЕЗЕРВИРОВАНА на будущее
     * @param $colspan
     * @return string
     */
    protected function  colspan($colspan): string //
	{
	return '';
	}

}

