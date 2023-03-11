<?php

/*=======================
Построение страницы
=========================*/

class page
{
	
	protected $brief;

	protected $template;
	protected $templateDir;

	protected $title;

	protected $content;

	protected $add_css;
	protected $add_js;

	protected $messages;
	protected $show_messages;

	protected $ignore_login;




//-------------------------------

public function __construct($brief, $template, $content,$add_css='',$add_js='',$ignore_login=false)
	{

	$this->brief	=$brief;
	$this->template	=$template;
	$this->content	=$content;

	$this->add_css	=$add_css;
	$this->add_js	=$add_js;

	$this->ignore_login	=$ignore_login;

	$this->getMessages(); 



	}

//------------

public function __get($property)
	{
	if( property_exists($this,$property))
		return $this->$property;
	}

//------------

public function __set($property,$value)
	{
	$str = ['content','template','add_css','add_js','ignore_login'];
	if( in_array($property, $str))
		$this->$property = $value;
	}

//------------

public function __call($name ,$arguments )
	{
	if( method_exists($this,$name))
		return $this->$name();	
	}


//--------------------------------

public function html()
	{

	$menu = new menu();
	$this->title = $menu->itemName($this->brief);

	$html = $this->getTemplateCode(); 

	$html = view::parse($html,$this); 

	$html = $this->replace_urls($html); 

	return $html;

	}

/*=======================
			PROTECTED
=========================*/

protected function getTemplateCode()
	{

	$dir = $this->getTemplateDir();
	$this->templateDir = $dir;


	$filename = $dir.'/index.html'; 

	$html = '';

	if( file_exists($filename)  )
		$html = file_get_contents($filename);
	else
		{
		log::write('error',__FILE__.', line '.__LINE__.' Absent template '.$filename);
		$html = 'Template '.$filename.' is absent';
		}

	return $html;
	}

//--------------------------------

protected function getTemplateDir()
	{
	$dir = __DIR__. '/../templates';

	$dir = common::path_compact($dir);

	$dir .= '/'.$this->template;

	return $dir;

	}

//--------------------------------

protected function content()
	{

	global $conn;

	$html = '';

	if($this->ignore_login)
		$html = $this->content;
	else
		{
			$menu = new menu();
			$allow=$menu->allow($this->brief);

			if($allow)
				$html = $this->content;
			else
				$html = $this->restricted_message('role');
		}
	return $html;

	}

//--------------------------------

protected function login_form()
	{

	$html = view::render('login',$this);

	return $html;

	}


//--------------------------------

protected function restricted_message($subj)
	{

	$view = '';

	switch($subj)
		{
		case 'user':
			$view = 'ban_user';
			break;
		case 'client':
			$view = 'ban_client';
			break;
		case 'role':
			$view = 'ban_role';
			break;
		case 'office':
			$view = 'no_offices';
			break;
		}

	return view::render($view, $this);

	}

//--------------------------------

protected function replace_urls($html)
	{

	$url = preg_replace('/.*templates/','/templates',$this->templateDir);

	$html = preg_replace('/"images\//', '"'.$url.'/images\/',$html);
	$html = preg_replace('/"css\//',	'"'.$url.'/css/',$html);
	$html = preg_replace('/"js\//',		'"'.$url.'/js/',$html);

	return $html;

	}

//--------------------------------

protected function mobile_test()
	{
	$html = view::render('mobile_test',$this);
	return $html;
	}

//--------------------------------

protected function header_outer()
	{

	//$view = (MOBILE == 0) ? 'header' : 'header_mobile';

    $view = 'header' ;

	$html = view::render($view,$this);
	return $html;
	}

//--------------------------------

protected function header_client()
	{
	return 'guest';
	}

//--------------------------------

protected function header_office()
	{
	$office =  (!defined('OFFICE_NAME')) ? '' : OFFICE_NAME;
	if($office == 'undefined')
		$office='';
	elseif( $office != '')
		$office="Офис <strong>$office</strong>";

	return $office;
	}

//--------------------------------

protected function header_user()
	{
	return 'Здравствуйте, guest!';

	}

//--------------------------------

protected function header_menu()
	{

	if( MOBILE == 1)
		return '';

	$menu = new menu();
	$html = $menu->html();
	return $html;
	}

protected function header_menu_mobile()
    {

	if( MOBILE == 0)
		return '';

        $menu=new header_menu_mobile(1,0,1);
        $html=$menu->mainmenu();
        return $html;
    }

protected function footer()
	{
	$html = view::render('footer',$this);
	return $html;

	}

protected function m()
	{

	if( MOBILE == 0)
		return '';

	$html = view::render('m',$this);
	return $html;
	}

protected function css_menu()
	{
	$view = ( MOBILE == 1) ? 'css_menu_mobile' : 'css_menu_desktop';
	$html = view::render($view,$this);
	return $html;
	}


protected function wrapperClass()
	{
	return (MOBILE == 1) ? 'mobile' : 'desktop';
	}

protected function cssSuffix()
	{
	return (MOBILE == 1) ? '_mobile' : '';
	}

protected function wrapperOnClick()
	{
	return (MOBILE == 1) ? ' onclick="document.getElementById(\'mainmenu_mobile\').classList.add(\'invisible\')"' : '';
	}


}


?>