<?php

require_once(__DIR__.'/inc/conn.php');
$brief = basename(__FILE__);

$import = new import();

$page = new page($brief,'general',$import->content() );

print $page->html();

require_once(__DIR__.'/inc/close.php');
