<?php

require_once(__DIR__.'/inc/conn.php');
$brief = basename(__FILE__);

$projects = new projects($brief, $_GET, $_POST);

$page = new page(
        $brief,
        'general',
        $projects->html(),
    '<link rel="stylesheet" href="/styles/dictionary.css" type="text/css" />
            <link rel="stylesheet" href="/styles/projects.css" type="text/css" />'
            );

print $page->html();

require_once(__DIR__.'/inc/close.php');
