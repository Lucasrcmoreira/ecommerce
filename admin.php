<?php

use \NC\PageAdm;
use \NC\Models\User;

//ROTAS ADMINISTRADOR LOGIN ******************************************************

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

//ESQUECI SENHA **************************************************


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

?>