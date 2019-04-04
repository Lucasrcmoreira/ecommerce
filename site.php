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

	User::verifyLogin(false);

	$cart =Cart::getFromSession();
	$address = new Address();
	$page = new Page();
	$page->setTpl("checkout",[	
		'cart'=>$cart->getValues(),
		'address'=>$address->getValues()

	]);

});

$app->get("/login",function(){

	$page = new Page();
	$page->setTpl("login", [
		'Error_Login'=>User::getErrorLogin(),
		'Error_Register'=>User::getErrorRegister(),
		'ValuesRegister'=>(isset($_SESSION['ValuesRegister'])) ? $_SESSION['ValuesRegister'] : ['name'=>'','email'=>'','phone'=>'']
	]);
});

$app->post("/login",function(){

	try {
		User::Login($_POST['login'],$_POST['password']);


	} catch (Exception $e) {
		
		User::setErrorLogin($e->getMessage());
	}

	header("Location: /checkout");
	exit;
	
});

$app->get("/logout",function(){

	User::logout();

	header("location: /login");
	exit;

});

$app->post("/register",function(){

	$_SESSION['ValuesRegister'] = $_POST;

	if(!isset($_POST['name']) || $_POST['name'] == ''){

		User::setErrorRegister("OPS ! o SR(A) esqueceu do campo nome");
		header('Location: /login');
		exit;

	}else if(!isset($_POST['email']) || $_POST['email'] == ''){
		User::setErrorRegister("OPS ! o SR(A) esqueceu do campo EMAIL");
		header('Location: /login');
		exit;
	}else if(!isset($_POST['phone']) || $_POST['phone'] == ''){	
		User::setErrorRegister("OPS ! o SR(A) esqueceu do campo TELEFONE");
		header('Location: /login');
		exit;
	}else if(!isset($_POST['password']) || $_POST['password'] == ''){
		User::setErrorRegister("OPS ! o SR(A) esqueceu do campo SENHA");
		header('Location: /login');
		exit;
	}else if(User::checkLoginExist($_POST['email']) === true){
		User::setErrorRegister("OPS ! Este endereço de EMAIL já está cadastrado ");
		header('Location: /login');
		exit;
	}else{

	$user= new User();
	$user->setData([
		'inadmin'=>0,
		'deslogin'=>$_POST['email'],
		'desperson'=>$_POST['name'],
		'desemail'=>$_POST['email'],
		'nrphone'=>$_POST['phone'],
		'despassword'=>$_POST['password']
	]);

	$user->save();
	
	$user = User::getForgotUser($_POST['email']);

	header('Location: /forgot-sent-Register');
	exit;
	}

});

$app->get("/forgot-sent-Register",function(){


	$page = new Page(["header"=>false,
						 "footer"=>false
	]);

	$page->setTpl("forgot-sent-Register");
});

$app->get("/forgot-sucess-Register",function(){


	$code = $_GET['code'];

	

	$forgotUser = User::validForgotUser($code);

	User::setForgotUsedUser($forgotUser["idrecovery"]);

	$user = new User();

	$user->get((int)$forgotUser["iduser"]);

	$user->setValidacaoUser($forgotUser["idperson"]);


	$page = new Page(["header"=>false,
						 "footer"=>false
	]);

	$page->setTpl("forgot-success-Register");



});

$app->post("/forgot-sucess-Register",function(){

	
});

?>