<?php
namespace NC\Models;

use \NC\DB\Sql;
use \NC\Model;

class User extends Model{

	const SESSION = "User";
	const SECRET = "HcodePhp7_Secret";

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

	public function get($iduser){
		$sql = new Sql();
		$result = $sql->select("SELECT * FROM tb_users u INNER JOIN tb_persons p USING(idperson) WHERE u.iduser = :iduser",array(
			":iduser"=>$iduser
		));

		return $this->setData($result[0]);

	}

	public function update(){

		$sql = new Sql();

		$result = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)",array(
					":iduser"=>$this->getiduser(),
					":desperson"=>$this->getdesperson(),
					":deslogin"=>$this->getdeslogin(),
					":despassword"=>$this->getdespassword(),
					":desemail"=>$this->getdesemail(),
					":nrphone"=>$this->getnrphone(),
					":inadmin"=>$this->getinadmin()
		));

		$this->setData($result[0]);
	}

	public function delete(){
		$sql = new Sql();

		$sql->query("CALL sp_users_delete(:iduser)",array(
				":iduser"=>$this->getiduser()
		));
	}

	public static function getForgot($email){

		$sql = new Sql();

		$result = $sql->select("SELECT * FROM tb_persons p INNER JOIN tb_users u USING(idperson) WHERE p.desemail = :email",array(
						":email"=>$email
		));

		if(count($result) === 0){
			throw new \Exception("Não foi possivel recuperar a senha entre em contato com nossa central de suporte");
		}else{
			$data = $result[0];

			$resultSql = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)",array(":iduser"=>$data["iduser"],
						":desip"=>$_SERVER["REMOTE_ADDR"]
			));
			if(count($resultSql) === 0 ){
				throw new \Exception("Não foi possivel recuperar a senha");
			}else{
				$dataRecovery = $resultSql[0];

				$code = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128,User::SECRET, $dataRecovery["idrecovery"], MCRYPT_MODE_ECB));

				$link = "http://http://www.lucascommerce.com.br/admin/forgot/reset?code=$code";

				$Mailer = new Mailer($data["desemail"],$data["desperson"],"Redefinição de senha Lucas - Ecommerce Store","forgot",array(
													"name"=>$data["desperson"],
													"link"=>$link
				));

				$Mailer->send();

				return $data;
			}
		}
	}
}

?>