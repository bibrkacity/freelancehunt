<?php
/*===============================
построение меню админпанели по роли в системе
Роли:

см. класс roles

=================================*/
class menu
{
	public $lng;			//Язык меню
	public $mainmenu;		//Массив главного меню 
	public $submenu;		//Массив подменю 

//--------------------------------------------------------------

function __construct()
	{
	$this->mainmenu=$this->mainmenu_basic();
	$this->submenu=$this->submenu_basic();
	}

//--------------------------------------------------------------

public function itemName($fileName) //используется для формирования TITLE страницы админпанели
	{
	$name="";
	foreach($this->submenu as $key=>$array)
		{
		if($array[2]==$fileName)
			{
			$name=$array[1];
			break;
			}
		}

	if(($name == '') && ($fileName == 'index.php'))
		$name ='Старт';
	return $name;
	}

//--------------------------------------------------------------

public function allow($fileName) //
	{
	$allow=false;

    if(USER_ROLE_ID == roles::ADMIN)
        $allow=true;
    else
        foreach($this->submenu as $key=>$array)
            {
            if($array[2]==$fileName)
                {
                $allow = in_array(USER_ROLE_ID,$array[3]);
                break;
                }
            }

	return $allow;
	}

//--------------------------------------------------------------

public function html()
	{
		$html=$this->html_desktop();
		return $html;
	}

//--------------------------------------------------------------

protected function mainmenu_etalon()
	{
	$mainmenu = array
	(
	   1 => array ("Справочники",  array ())
	  ,3 => array ("Импорт",  array ())

	);	

	return $mainmenu;

	}

//--------------------------------------------------------------

protected function submenu_etalon()
	{
	$submenu= array
		(
		  array( 0, "Старт" , "index.php", array())

		 ,array( 1, "Проекты" , "projects.php", array())

		 ,array( 3, "Проекты" , "projects_import.php", array())

		);

	return $submenu;
	}

//--------------------------------------------------------------

protected function mainmenu_basic() /* На случай сложной логики. Сейчас просто воспроизводит массив mainmenu_etalon().  */
	{
	$etalon= $this->mainmenu_etalon();

	$basic=array();

	foreach($etalon as $key=>$value)
		{


		$item=array();
		
		$item[0]=$value[0];
		$item[1]=$value[1];

		$basic[$key]=$item;
		}

	return $basic;
	}

//--------------------------------------------------------------

protected function submenu_basic() 
	{
	$etalon= $this->submenu_etalon();

	$basic=array();

	foreach($etalon as $key=>$value)
		{

		if(!in_array(USER_ROLE_ID,$value[3]) && (USER_ROLE_ID != roles::ADMIN) )
			continue;		

		$item=array();

		$item[0]=$value[0];
		$item[1]=$value[1];
		$item[2]=$value[2];
		$item[3]=$value[3];

		$basic[$key]=$item;
		}

	return $basic;
	}	


//--------------------------------------------------------------

protected function html_desktop()
	{
		
		$html="\n<div id=\"topmenu\">";

		foreach( $this->mainmenu as $id=>$one)
			{
			$html .= $this->div_desktop($id,$one);
			}

		$html.="</div>\n";
		return $html;
	}


//--------------------------------------------------------------

protected function div_desktop($id,$one)
	{
		
		$html="\n<div>";

		$html .= $one[0];

		$html .= '<div><ul>';

		foreach($this->submenu as $one)
			{
			if($one[0] == $id)
				$html .= '<li onclick="location.href=\''.$one[2].'\'">'.$one[1].'</li>';
			}

		$html .= '</ul></div>';

		

		$html.="</div>\n";
		return $html;
	}


//--------------------------------------------------------------

}//end of class


?>