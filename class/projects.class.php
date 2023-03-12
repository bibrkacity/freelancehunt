<?php

class projects extends dictionary
{

    public function __construct(string $action, array $get, array $post, $connection = null)
    {
        parent::__construct($action, $get, $post, $connection);

        //Поля для сортировки
        $this->sorting_fields = ['id', 'url', 'budget'];

        //Перечень фильтров и их значение по умолчанию
        $this->filters_default = array
        (
         'bySkill' => '0'
        );

        $this->optional_fields = array();
        $this->optional_names = array();

        $this->fill_arrays();

        $this->title = 'Проекти';
        $this->search_title =  'Пошук';

        $this->dict_class = __CLASS__;
    }

    public function search() : string
    {

        global $conn;

        $query =" SELECT * FROM `skills` ORDER BY `name`";
        $result = mysqli_query($this->conn, $query ) ;
        $options = [ 0=> '--Немає відбору--'];
        while ($row = mysqli_fetch_assoc($result)) {
            $options[ (int)$row['id'] ] = $row['name'];
        }
        mysqli_free_result($result);

        $select = new select('',$options, $this->filters['bySkill'],'id="bySkill"');
        $combo=$select->html();

        $html = '<table class="usual">';

        $html .= '<tr>';
        $html .= '<td>Skill:</td>';
        $html .= "<td>$combo</td>";


        $html .= "</tr>";

        $html .= '<tr>';
        $html .= '<td colspan="2"><input type="button" value="Поиск" onclick="search_submit()" /></td>';
        $html .= "</tr>";

        $html .= '</table>';

        return $html;
    }

    protected function exists(): string
    {

        $query = "SELECT `projects`.* , concat(`employees`.`first_name`,' ',`employees`.`last_name`) `employee`,  `employees`.`login`
				  FROM `projects` 
                    JOIN `employees` ON (`projects`.`employee_id` = `employees`.`id`)
						";

        if( $this->filters['bySkill'] != '0')
            $query .= " JOIN `project_skill` ON (`projects`.`id` = `project_skill`.`project_id`)";

        $query .= $this->where();

        if ($this->sorting_fields['id'] != 'no')
            $query .= " order by projects.id " . $this->sorting_fields['id'];

        elseif ($this->sorting_fields['budget'] != 'no')
            $query .= " order by projects.budget " . $this->sorting_fields['budget'];

        else {
            $this->sorting_fields['id'] = 'asc';
            $query .= " order by projects.id " . $this->sorting_fields['id'];
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
            $tr .= '<a href="'.$row['url'].'">';
            $tr .= stripslashes($row['name']);
            $tr .= '</a>';
            $tr .= '</td>';

            $tr .= '<td>';
            $tr .= number_format($row['budget'], 2, ',', ' ');
            $tr .= '</td>';

            $tr .= '<td>';
            $tr .= $row['employee'];
            $tr .= '</td>';

            $tr .= '<td>';
            $tr .= $row['login'];
            $tr .= '</td>';

            $tr .= '</tr>';

        }//end while

        if ($tr == '') {
            return '<h2>Не знайдено проектів у базі</h2>' . $this->filters();
        }

        $colspan = 5;
        $this->colspan($colspan);
        $n = 0;
        $pages = $this->pages($n);
        $html = $pages;
        $html .= '
		<table class="dictionary"><tr><th id="table_caption" colspan="' . $colspan . '">';
        $html .= "$n  із загальної кількості " . $this->total() . ' проектів';
        $html .= '<br />' . $this->filters();
        $html .= "</th></tr>";
        $html .= '<tr>';

        $html .= $this->th_sort('id','id');
        $html .= '<th>Назва</th>';
        $html .= $this->th_sort( 'Бюджет, грн', 'budget');
        $html .= '<th>Замовник</th>';
        $html .= '<th>Логин замовникa</th>';
        $html .= '</tr>';
        $html .= $tr;
        $html .= '</table>';
        $html .= $pages;

        return $html;
    }

    protected function where() : string
    {
        $where = '';
        if ($this->filters['bySkill'] != '0') {
            $where = " `project_skill`.`skill_id`=".(int)$this->filters['bySkill'];
        }

        if ($where != '')
            $where = ' WHERE ' . $where;
        return $where;
    }

    protected function filters() : string
    {
        $f = '';
        if ($this->filters['bySkill'] != '0') {

            global $conn;
            $name='';
            $query =" SELECT `name` FROM `skills` WHERE `id`=".(int)$this->filters['bySkill'];
            $result = mysqli_query($this->conn, $query ) ;
            while ($row = mysqli_fetch_assoc($result)) {
                $name= $row['name'];
            }
            mysqli_free_result($result);



            $f .=  'Skill: <b>' . $name . '</b>';
        }

        if ($f == '') {
            $f = 'Фільтри не задіяні';
        } else {
            $f = 'Застосований фільтр: ' . $f;
        }

        return $f;
    }


    protected /*override*/ function query_pages() : string //Подсчет объектов в справочнике, отобранных фильтром
    {
        $query = "SELECT count(`projects`.`id`)
				  FROM `projects` 
                    JOIN `employees` ON (`projects`.`employee_id` = `employees`.`id`)
						";

        if( $this->filters['bySkill'] != '0')
            $query .= " JOIN `project_skill` ON (`projects`.`id` = `project_skill`.`project_id`)";
        $query .= $this->where();
        return $query;
    }

    protected /*override*/ function query_total(): string //Подсчет всех объектов в справочнике
    {
        $query = "SELECT count(id) from projects ";
        return $query;
    }

    protected function js()
    {
        $js = $this->js_common();
        $js .= $this->js_search();

        return $js;
    }


    protected /*override*/ function button_additional() : string
    {
        $html =  '<div id="graph">';

        $html.="<input class=\"knopka\" type=\"button\" value=\"Діаграма\" onclick=\"graph()\" /><span id=\"tri\">&#9658;</span>";

        $html .=  '</div>';

        return $html;
    }
}
