<?php 

session_start();

require_once("vendor/autoload.php");

use \Slim\Slim;
use \NC\Page;
use \NC\PageAdm;
use \NC\Models\User;
use \NC\Models\Category;

$app = new Slim();

$app->config('debug', true);

$app->get('/', function() {
    
	$page =new Page();

	$page->setTpl("index");

});

$app->get('/admin', function() {
    
    User::verifyLogin();

	$page =new PageAdm();

	$page->setTpl("index");

});

$app->get('/admin/login',function(){

	$page = new PageAdm(["header"=>false,"footer"=>false
	
	]);

	$page->setTpl("login");

});

$app->get('/admin/logout',function(){

	User::logout();

	header("Location: /admin/login");
	exit;

});

$app->post('/admin/login', function(){

	User::login($_POST["login"], $_POST["password"]);

	header("Location: /admin");
	exit;

});

$app->get("/admin/users",function(){

	User::verifyLogin();
	$users = User::listAll();
	$page = new PageAdm();
	$page->setTpl("users",array(
								"users"=>$users
	));

});

$app->get("/admin/users/create",function(){

	User::verifyLogin();
	$page = new PageAdm();
	$page->setTpl("users-create");

});

$app->get("/admin/users/:iduser/delete",function($iduser){

	User::verifyLogin();

	$user = new User();
	$user->get((int)$iduser);
	$user->delete();

	header("Location: /admin/users");
	exit;

});

$app->get("/admin/users/:iduser",function($iduser){

	User::verifyLogin();
	$user = new User();
	$user->get((int)$iduser);
	$page = new PageAdm();
	$page->setTpl("users-update",array(
			"user"=>$user->getValues()
	));

});

$app->post("/admin/users/create",function(){

	User::verifyLogin();

	$user = new User();
	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;
	$user->setData($_POST);
	$user->save();

	header("Location: /admin/users");
	exit;

});

$app->post("/admin/users/:iduser",function($iduser){

	User::verifyLogin();
	$user = new User();
	$user->get((int)$iduser);
	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;
	$user->setData($_POST);
	$user->update();

	header("Location: /admin/users");
	exit;
});

$app->get("/admin/forgot",function(){

	$page = new PageAdm(["header"=>false,
						 "footer"=>false
	
	]);

	$page->setTpl("forgot");

});

$app->post("/admin/forgot",function(){

	$user = User::getForgot($_POST["email"]);

	header("Location: /admin/forgot/send");
	exit;
});

$app->get("/admin/forgot/send",function(){

	$page = new PageAdm(["header"=>false,"footer"=>false]);

	$page->setTpl("forgot-sent");

});

$app->get("/admin/forgot/reset",function(){

	$user = User::validForgot($_GET["code"]);

	$page = new PageAdm(["header"=>false,
					 	 "footer"=>false
	
	]);
	$page->setTpl("forgot-reset",array(
								"name"=>$user["desperson"],
								"code"=>$_GET["code"]
	));

});

$app->post("/admin/forgot/reset",function(){

	$forgot = User::validForgot($_POST["code"]);

	User::setForgotUsed($forgot["idrecovery"]);

	$password = password_hash($_POST["password"], PASSWORD_DEFAULT, ["cost"=>12]);

	$user = new User();
	$user->get((int)$forgot["iduser"]);
	$user->setPassword($password);

	$page = new PageAdm(["header"=>false,
						 "footer"=>false
	]);

	$page->setTpl("forgot-reset-success");

});

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

$app->get("/categories/:idcategory",function($idcategory){

	$category = new Category();
	$category->get((int)$idcategory);

	$page = new Page();

	$page-> setTpl("category",['category'=>$category->getValues(),
							   'products'=>[]
]);

});

$app->run();

 ?>