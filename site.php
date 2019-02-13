<?php

use \NC\Page;
use \NC\Models\Products;
use \NC\Models\Category;

//ROTAS SITE PRINCIPAL ***************************************************

$app->get('/', function() {

	
	$products = Products::listAll();
    
	$page =new Page();

	$page->setTpl("index",[

		'products'=>Products::checkList($products)
	]);

});


$app->get("/categories/:idcategory",function($idcategory){

	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	$category = new Category();
	$category->get((int)$idcategory);

	$pagination = $category->getProductsPage($page);

	$page = new Page();

	$pages =[];

	for($i=1; $i <= $pagination['pages']; $i++){

		array_push($pages, [
							'link'=>'/categories/'.$category->getidcategory().'?page='.$i,
							'page'=>$i
		]);
	}

	$page-> setTpl("category",['category'=>$category->getValues(),
							   'products'=>$pagination["data"],
							   'pages'=>$pages
	]);
});


?>