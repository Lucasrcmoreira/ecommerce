<?php

use \NC\PageAdm;
use \NC\Models\User;
use \NC\Models\Products;

// ROTAS ADMINISTRADOR PRODUTOS ***********************************************

$app->get('/admin/products',function(){

	User::verifyLogin();
	$page = new PageAdm();

	$products = Products::listAll();

	$page->setTpl("products", [
		"products"=>$products
	]);

});

?>