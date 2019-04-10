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

// ####################### Categorias #############################

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

// ####################### Carrinho de Compras #############################

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
	if (isset($_POST['zipcode']) || $_POST['zipcode'] === '') {
		Cart::setMsgError("OPS !! preencha campo CEP  para continuar !!");
		header('Location: /cart');
		exit;
	}

	$cart->setFreight($_POST['zipcode']);

	header('Location: /cart');
	exit;

});

// ####################### Login Usuario  #############################

$app->get("/checkout",function(){

	User::verifyLogin(false);

	$address = new Address();
	$cart =Cart::getFromSession();

	if(isset($_GET['zipcode'])){
		$_GET['deszipcode'] =$cart->getdeszipcode();
	}

	if(isset($_GET['zipcode'])){
		$nmrcep = str_replace("-", "", $_GET['zipcode']);
		$address->loadFromCEP($nmrcep);
		$cart->setdeszipcode($nmrcep);
		$cart->save();

		$cart->getCalcTotalFreight();
	}

	if(!$address->getdesaddress()) $address->getdesaddress('');
	if(!$address->getdescomplement()) $address->getdescomplement('');
	if(!$address->getdesdistrict()) $address->getdesdistrict('');
	if(!$address->getdescity()) $address->getdescity('');
	if(!$address->getdesstate()) $address->getdesstate('');
	if(!$address->getdescountry()) $address->getdescountry('');
	if(!$address->getdeszipcode()) $address->getdeszipcode('');

	$page = new Page();
	$page->setTpl("checkout",[	
		'cart'=>$cart->getValues(),
		'address'=>$address->getValues(),
		'products'=>$cart->getProducts(),
		'error'=>Address::getMsgError()
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

// ####################### Cadastro Usuario com email de verificação #############################
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

// ####################### Esqueci a Senha Usuario #############################

$app->get("/forgot",function(){

	$page = new Page();

	$page->setTpl("forgot");

});

$app->post("/forgot",function(){

	$user = User::getForgot($_POST["email"],false);

	header("Location: /forgot/send");
	exit;
});

$app->get("/forgot/send",function(){

	$page = new Page();

	$page->setTpl("forgot-sent");

});

$app->get("/forgot/reset",function(){

	$code = $_GET["code"];
	$code = str_replace(' ', '+', $code);

	$user = User::validForgot($code);

	$page = new Page();
	$page->setTpl("forgot-reset",array(
								"name"=>$user["desperson"],
								"code"=>$_GET["code"]
	));

});

$app->post("/forgot/reset",function(){

	$code = $_POST["code"];
	$code = str_replace(' ', '+', $code);
	$forgot = User::validForgot($code);

	User::setForgotUsed($forgot["idrecovery"]);

	$password = password_hash($_POST["password"], PASSWORD_DEFAULT, ["cost"=>12]);

	$user = new User();
	$user->get((int)$forgot["iduser"]);
	$user->setPassword($password);

	$page = new Page();

	$page->setTpl("forgot-reset-success");

});

// ####################### Minha conta Profile Usuario #############################

$app->get("/profile",function(){
	User::verifyLogin(false);

	$user= User::getFromSession();

	$page =new Page();

	$page->setTpl("profile",[
		'user'=>$user->getValues(),
		'profileMsg'=>User::getMsgSuccess(),
		'profileError'=>User::getError()
	]);

});

$app->post("/profile",function(){
	User::verifyLogin(false);

	if(!isset($_POST['desperson']) || $_POST['desperson'] ===''){
		User::setError(" OPS !! Preencha o seu NOME ");
		header('Location: /profile');
		exit;
	}
	if(!isset($_POST['desemail']) || $_POST['desemail'] ===''){
		User::setError(" OPS !! Preencha o seu E-MAIL ");
		header('Location: /profile');
		exit;
	}

	$user = User::getFromSession();

	if($_POST['desemail'] !== $user->getdesemail() ){
		if (User::checkLoginExist($_POST['desemail']) === true){
			User::setError(" OPS !! Este E-MAIL já está cadastrado");
			header('Location: /profile');
		    exit;
		}
	}

	$_POST['iduser'] = $user->getiduser();
	$_POST['inadmin'] = $user->getinadmin();
	$_POST['despassword'] = $user->getdespassword();
	$_POST['deslogin'] = $_POST['desemail'];
	$user->setData($_POST);
	$user->update();

	User::setMsgSuccess(" Dados alterados com sucesso !! ");
	$_SESSION[User::SESSION] = $user->getValues();

	header("Location: /profile");
	exit;

});

// ####################### WEB SERVICE CEP #############################

$app->post("/checkout",function(){

	User::verifyLogin(false);

	if(!isset($_POST['zipcode']) || $_POST['zipcode'] === ''){
		Address::setMsgError("OPS !! preencha o campo CEP ");
		header('Location: /checkout');
		exit;
	}
	if(!isset($_POST['desaddress']) || $_POST['desaddress'] === ''){
		Address::setMsgError("OPS !! preencha o campo ENDEREÇO ");
		header('Location: /checkout');
		exit;
	}

	if(!isset($_POST['desdistrict']) || $_POST['desdistrict'] === ''){
		Address::setMsgError("OPS !! preencha o campo BAIRRO ");
		header('Location: /checkout');
		exit;
	}
	if(!isset($_POST['descity']) || $_POST['descity'] === ''){
		Address::setMsgError("OPS !! preencha o campo CIDADE ");
		header('Location: /checkout');
		exit;
	}
	if(!isset($_POST['desstate']) || $_POST['desstate'] === ''){
		Address::setMsgError("OPS !! preencha o campo ESTADO ");
		header('Location: /checkout');
		exit;
	}
	if(!isset($_POST['descountry']) || $_POST['descountry'] === ''){
		Address::setMsgError("OPS !! preencha o campo PAÍS ");
		header('Location: /checkout');
		exit;
	}


	$user = User::getFromSession();

	$address = new Address();

	$_POST['deszipcode'] = $_POST['zipcode'];
	$_POST['idperson'] = $user->getidperson();

	$address->setData($_POST);

	$address->save();

	header("Location: /order");
	exit;

});

?>