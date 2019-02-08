<?php

use \NC\PageAdm;
use \NC\Models\User;
use \NC\Models\Category;
use \NC\Models\Products;

//ROTAS CRUD CATEGORIAS **********************************************************************

$app->get("/admin/categories",function(){

	User::verifyLogin();

	$categories = Category::listAll();

	$page = new PageAdm();
	$page->setTpl("categories",[
					'categories'=>$categories
	]);

});

$app->get("/admin/categories/create",function(){

	User::verifyLogin();

	$page = new PageAdm();
	$page->setTpl("categories-create");

});

$app->post("/admin/categories/create",function(){

	User::verifyLogin();

	$category = new Category();

	$category->setData($_POST);

	$category->save();

	header('Location: /admin/categories');
	exit;

});

$app->get("/admin/categories/:idcategory/delete",function($idcategory){

	User::verifyLogin();

	$category = new Category();
	$category->get((int)$idcategory);
	$category->delete();

	header('Location: /admin/categories');
	exit;

});

$app->get("/admin/categories/:idcategory",function($idcategory){

	User::verifyLogin();

	$category = new Category();
	$category->get((int)$idcategory);

	$page = new PageAdm();
	$page->setTpl("categories-update",['category'=>$category->getValues()]);

});

$app->post("/admin/categories/:idcategory",function($idcategory){

	User::verifyLogin();

	$category = new Category();
	$category->get((int)$idcategory);

	$category->setData($_POST);
	$category->save();

	header("Location: /admin/categories");
	exit;

});

$app->get("/admin/categories/:idcategory/products",function($idcategory){

	User::verifyLogin();
	$category = new Category();
	$category->get((int)$idcategory);

	$page = new PageAdm();
	$page->setTpl("categories-products",[
		'category'=>$category->getValues(),
		'productsRelated'=>$category->getProducts(),
		'productsNotRelated'=>$category->getProducts(false)
	]);

});

$app->get("/admin/categories/:idcategory/products/:idproduct/remove",function($idcategory,$idproduct){

	User::verifyLogin();
	$category = new Category();
	$category->get((int)$idcategory);

	$products = new products();
	$products->get((int)$idproduct);

	$category->removeProducts($products);

	header("Location: /admin/categories/".$idcategory."/products");
	exit;
});

$app->get("/admin/categories/:idcategory/products/:idproduct/add",function($idcategory,$idproduct){

	$category = new Category();
	$category->get((int)$idcategory);

	$products = new Products();
	$products->get((int)$idproduct);

	$category->addProducts($products);

	header("Location: /admin/categories/".$idcategory."/products");
	exit;

});

?>