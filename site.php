<?php

use \NC\Page;

//ROTAS SITE PRINCIPAL ***************************************************

$app->get('/', function() {
    
	$page =new Page();

	$page->setTpl("index");

});




?>