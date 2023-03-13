<?php

require_once(__DIR__.'/inc/conn.php');

$brief = basename(__FILE__);

$page = new page($brief,'general', view::render('start',null) );

print $page->html();

require_once(__DIR__.'/inc/close.php');
