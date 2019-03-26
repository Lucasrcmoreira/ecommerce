<?php

use \NC\Page;
use \NC\Models\Products;
use \NC\Models\Category;
use \NC\Models\Cart;
use \NC\Models\Address;
use \NC\Models\User;

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



$app->get("/products/:desurl",function($desurl){

	$product = new Products();
	$product->getFromURL($desurl);

	$page = new Page();
	$page->setTpl("product-detail",[
		'product'=>$product->getValues(),
		'categories'=>$product->getCategories()
	]);


});


$app->get("/cart",function(){

	$cart = Cart::getFromSession();

	$page = new Page();

	$page->setTpl("cart",[
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts(),
		'error'=>Cart::getMsgError()
	]);

});

$app->get("/cart/:idproduct/add",function($idproduct){

	$product = new Products();
	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();
	
	$qtd = (isset($_GET['quantity'])) ? (int)$_GET['quantity'] : 1 ;

	for ($i=0;  $i < $qtd ; $i++)  { 

		$cart->addProduct($product);
	}

	header('Location: /cart');
	exit;

});

$app->get("/cart/:idproduct/cart/Removed",function($idproduct){

	$product = new Products();
	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();
	$cart->removeProduct($product);

	header('Location: /cart');
	exit;

});

$app->get("/cart/:idproduct/all/removed",function($idproduct){

	$product = new Products();
	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();
	$cart->removeProduct($product, true);

	header('Location: /cart');
	exit;

});

$app->post("/cart/freight",function(){

	$cart = Cart::getFromSession();
	$cart->setFreight($_POST['zipcode']);

	header('Location: /cart');
	exit;

});

$app->get("/checkout",function(){

	User::verifyLogin();

	$cart =Cart::getFromSession();
	$address = new Address();
	$page = new Page();
	$page->setTpl("checkout",[	
		'cart'=>$cart->getValues(),
		'address'=>$address->getValues()

	]);


});

?>