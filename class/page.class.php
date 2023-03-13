<?php

/**
 * Построение полного HTML-кода страницы приложения
 */
class page
{
    /**
     * Краткое имя вызывающего файла
     * @var string
     */
    protected string $brief;

    /**
     * Название шаблона (имя папки)
     * @var string
     */
    protected string $template;

    /**
     * Папка с шаблонами
     * @var string
     */
    protected string $templateDir;

    /**
     * Содержимое тега title
     * @var string
     */
    protected string $title;

    /**
     * Содержание страницы (HTML)
     * @var string
     */
    protected string $content;

    /**
     * Дополнительный (к файлам шаблона и общим файлам) CSS-код или ссылки на файлы
     * @var string
     */
	protected string $add_css;

    /**
     * Дополнительный (к файлам шаблона и общим файлам) JS-код или ссылки на файлы
     * @var string
     */
	protected string $add_js;

    /**
     * Если true - содержание откроется и незалогиненному пользователю
     * @var bool
     */
    protected bool $ignore_login;


    /**
     * Конструктор
     * @param string $brief Краткое имя вызывающего файла
     * @param string $template Название шаблона (имя папки)
     * @param string $content Содержание страницы (HTML)
     * @param string $add_css Дополнительный (к файлам шаблона и общим файлам) CSS-rjl или ссылки на файлы
     * @param string $add_js Дополнительный (к файлам шаблона и общим файлам) JS-код или ссылки на файлы
     * @param bool $ignore_login Если true - содержание откроется и незалогиненному пользователю
     */
    public function __construct(string $brief, string $template, string $content, string $add_css='', string $add_js='', bool $ignore_login=false)
	{

	$this->brief	=$brief;
	$this->template	=$template;
	$this->content	=$content;

	$this->add_css	=$add_css;
	$this->add_js	=$add_js;

	$this->ignore_login	=$ignore_login;

	}

    public function __get($property)
	{
        if( property_exists($this,$property))
            return $this->$property;
	}

    public function __set($property,$value)
	{
        $str = ['content','template','add_css','add_js','ignore_login'];
        if( in_array($property, $str))
            $this->$property = $value;
	}

    public function __call($name ,$arguments )
	{
        if( method_exists($this,$name))
            return $this->$name();
	}

    /**
     * Построение полного HTML-кода страницы
     * @return string
     */
    public function html() : string
	{

	$menu = new menu();
	$this->title = $menu->itemName($this->brief);

	$html = $this->getTemplateCode(); 

	$html = view::parse($html,$this); 

	$html = $this->replace_urls($html); 

	return $html;

	}

    /**
     * Получение html-кода шаблона
     * @return bool|string
     */
    protected function getTemplateCode(): bool|string
    {

        $dir = $this->getTemplateDir();
        $this->templateDir = $dir;


        $filename = $dir.'/index.html';

        if( file_exists($filename)  )
            $html = file_get_contents($filename);
        else
            {
            $html = 'Template '.$filename.' is absent';
            }

        return $html;
	}

    /**
     * Получение полного пути к папке с шаблонами
     * @return string
     */
    protected function getTemplateDir():string
	{
	$dir = __DIR__. '/../templates';

	$dir = common::path_compact($dir);

	$dir .= '/'.$this->template;

	return $dir;

	}

    /**
     * Получение html-кода страницы. В тестовом задании смысла не имеет
     * @return string
     */
    protected function content():string
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

    protected function login_form(): string
    {

        $html = view::render('login',$this);

        return $html;

	}


    protected function restricted_message($subj): string
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

protected function header_menu_mobile(): string
{
    return '';

/*
 * 	if( MOBILE == 0)
		return '';
        $menu=new header_menu_mobile(1,0,1);
        $html=$menu->mainmenu();
        return $html;
*/
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

