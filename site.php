<?php

use \NC\Page;
use \NC\Models\Products;

//ROTAS SITE PRINCIPAL ***************************************************

$app->get('/', function() {

	
	$products = Products::listAll();
    
	$page =new Page();

	$page->setTpl("index",[

		'products'=>Products::checkList($products)
	]);

});




?>