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

$app->get("/admin/products/create",function(){

	User::verifyLogin();
	$page = new PageAdm();

	$page->setTpl("products-create");
});

$app->post("/admin/products/create", function(){

	User::verifyLogin();
	$products = new Products();
	$products->setData($_POST);
	$products->save();

	header("Location: /admin/products");
	exit;

});

$app->get("/admin/products/:idproduct",function($idproduct){

	User::verifyLogin();
	$products = new Products();
	$products->get((int)$idproduct);

	$page = new PageAdm();
	$page->setTpl("products-update",[
		'product'=>$products->getValues()
	]);
});
$app->post("/admin/products/:idproduct",function($idproduct){

	User::verifyLogin();
	$products = new Products();
	$products->get((int)$idproduct);
	$products->setData($_POST);
	$products->setPhoto($_FILES["file"]);
	$products->save();

	header('Location: /admin/products');
	exit;

});

$app->get("/admin/products/:idproduct/delete",function($idproduct){

	User::verifyLogin();
	$products = new Products();
	$products->get((int)$idproduct);
	$products->delete();

	header('Location: /admin/products');
	exit;

});

?>