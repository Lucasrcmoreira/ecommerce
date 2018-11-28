<?php
namespace NC\Models;

use \NC\DB\Sql;
use \NC\Model;

class User extends Model{

	const SESSION = "User";

	public static function login($login,$password){

		$sql = new Sql();

		$result = $sql->select("SELECT * FROM tb_users WHERE deslogin= :LOGIN",array(
					"LOGIN"=>$login
		));

		if(count($result) === 0)
		{
			throw new \Exception("Usuario não existe , ou senha inválida");
		}

		$data = $result[0];

		if(password_verify($password, $data["despassword"]) === true){

			$user = new User();

			$user->setData($data);

			$_SESSION[User::SESSION] = $user->getValues();

			return $user;

		}else{
			throw new \Exception("Usuario não existe , ou senha inválida");
		}
	}

	public static function verifyLogin($inadmin = true){

		if(!isset($_SESSION[User::SESSION]) 
			|| 
			!$_SESSION[User::SESSION] 
			|| 
			!(int)$_SESSION[User::SESSION]["iduser"] > 0 
			|| 
			(bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin
		){
			header("Location: /admin/login");
			exit;
		}
	}

	public static function logout(){

		$_SESSION[User::SESSION] = NULL;

	}

	public static function listAll(){

		$sql = new Sql();

		$result = $sql->select("SELECT * FROM tb_users u INNER JOIN tb_persons p USING(idperson) ORDER BY p.desperson");

		return $result;

	}

	public function save(){

		$sql = new Sql();

		$result = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)",array(
					":desperson"=>$this->getdesperson(),
					":deslogin"=>$this->getdeslogin(),
					":despassword"=>$this->getdespassword(),
					":desemail"=>$this->getdesemail(),
					":nrphone"=>$this->getnrphone(),
					":inadmin"=>$this->getinadmin()
		));

		$this->setData($result[0]);

	}
}

?>