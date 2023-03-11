<?php

class projects extends dictionary
{

    public function __construct($action, $get, $post, $connection = null)
    {
        parent::__construct($action, $get, $post, $connection);

        //Поля для сортировки
        $this->sorting_fields = array('name', 'url', 'budget', 'currency', 'employee_id');

        //Перечень фильтров и их значение по умолчанию
        $this->filters_default = array
        (
          'byName' => ''
        , 'byUrl' => ''
        , 'byBudget' => ''
        , 'byCurrency' => ''
        , 'byEmployee_id' => ''

        );

        $this->optional_fields = array();
        $this->optional_names = array();

        $this->fill_arrays();

        $this->title = 'Проекты';
        $this->search_title =  'Поиск';

        $this->dict_class = __CLASS__;
    }


//-----------------------------------------------------
    public function search()
    {

        $html = '<table class="usual">';

        $html .= '<tr>';
        $html .= '<td colspan="2"><input type="button" value="Поиск" onclick="search_submit()" /></td>';
        $html .= "</tr>";

        $html .= '</table>';

        return $html;
    }

    /*=============================================
         PROTECTED
    ==============================================*/


    protected function exists()
    {

        $query = "SELECT projects.*
						FROM projects
						";

        $query .= $this->where();

        if ($this->sorting_fields['name'] != 'no')
            $query .= " order by projects.name " . $this->sorting_fields['name'];

        elseif ($this->sorting_fields['url'] != 'no')
            $query .= " order by projects.url " . $this->sorting_fields['url'];

        elseif ($this->sorting_fields['budget'] != 'no')
            $query .= " order by projects.budget " . $this->sorting_fields['budget'];

        elseif ($this->sorting_fields['currency'] != 'no')
            $query .= " order by projects.currency " . $this->sorting_fields['currency'];

        elseif ($this->sorting_fields['employee_id'] != 'no')
            $query .= " order by projects.employee_id " . $this->sorting_fields['employee_id'];

        else {
            $this->sorting_fields['name'] = 'asc';
            $query .= " order by projects.name " . $this->sorting_fields['name'];
        }

        if ($this->in_page != 0)
            $query .= " LIMIT " . ($this->page - 1) * $this->in_page . "," . $this->in_page;
        $result = mysqli_query($this->conn, $query ) ;
        $tr = '';
        while ($row = mysqli_fetch_assoc($result)) {

            $tr .= '<tr id="tr' . $row['id'] . '">';
            $tr .= '<td>';
            $tr .= $row['id'];
            $tr .= '</td>';

            $tr .= '<td>';
            $tr .= stripslashes($row['name']);
            $tr .= '</td>';

            $tr .= '<td>';
            $tr .= stripslashes($row['url']);
            $tr .= '</td>';

            $tr .= '<td>';
            $tr .= number_format($row['budget'], 2, ',', ' ');
            $tr .= '</td>';

            $tr .= '<td>';
            $tr .= stripslashes($row['currency']);
            $tr .= '</td>';

            $tr .= '<td>';
            $tr .= number_format($row['employee_id'], 2, ',', ' ');
            $tr .= '</td>';

            $tr .= '</tr>';

        }//end while

        if ($tr == '') {
            return '<h2>Не найдено проектов в базе</h2>' . $this->filters();
        }

        $colspan = 5;
        $this->colspan($colspan);
        $n = 0;
        $pages = $this->pages($n);
        $html = $pages;
        $html .= '
		<table class="dictionary"><tr><th id="table_caption" colspan="' . $colspan . '">';
        $html .= "$n  из общего числа " . $this->total() . ' проектов';
        $html .= '<br />' . $this->filters();
        $html .= "</th></tr>";
        $html .= '<tr>';

        $html .= $this->th_sort('','id');
        $html .= $this->th_sort('','name');
        $html .= $this->th_sort('','url');
        $html .= $this->th_sort( '', 'budget');
        $html .= $this->th_sort( '', 'currency');
        $html .= $this->th_sort( '', 'employee_id');
        $html .= '</tr>';
        $html .= $tr;
        $html .= '</table>';
        $html .= $pages;

        return $html;
    }


//-----------------------------------------------------
    protected function where()
    {
        $where = '';
        if ($this->filters['byName'] != '') {

        }

        if ($where != '')
            $where = ' WHERE ' . $where;
        return $where;
    }

//-----------------------------------------------------

    protected function filters()
    {
        $f = '';
        if ($this->filters['byName'] != '') {
            $f .=  '' . ': <b>' . $this->filters['byName'] . '</b>';
        }

        if ($f == '') {
            $f = 'Фильтры не задействованы';
        } else {
            $f = 'Применен фильтр: ' . $f;
        }

        return $f;
    }

//-----------------------------------------------------

    protected /*override*/ function query_pages()//Подсчет объектов в справочнике, отобранных фильтром
    {
        $query = "SELECT count(id) from projects ";
        $query .= $this->where();
        return $query;
    }

//-----------------------------------------------------

    protected /*override*/ function query_total()//Подсчет всех объектов в справочнике
    {
        $query = "SELECT count(id) from projects ";
        return $query;
    }

//-----------------------------------------------------

    protected function js()
    {
        $js = $this->js_common();
        $js .= $this->js_search();

        return $js;
    }
}
