<?php 

session_start();

require_once("vendor/autoload.php");

use \Slim\Slim;
use \NC\Page;
use \NC\PageAdm;
use \NC\Models\User;

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

$app->run();

 ?>