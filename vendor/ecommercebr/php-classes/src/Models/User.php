<?php
namespace NC\Models;

use \NC\DB\Sql;
use \NC\Model;
use \NC\Mailer;



class User extends Model{

	const SESSION = "User";
	const SECRET = "lucascommerce-st";
	const V_USER = "lucascommerce-vu";
	const ERROR_LOGIN = "LoginError";
	const ERROR_REGISTER = "RegisterError";

	public function getFromSession(){

		$user = new User();

		if(isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0 ){

			$user->setData($_SESSION[User::SESSION]);

		}

		return $user;

	}

	public static function checkLogin($inadmin = true)
	{
		if (
			!isset($_SESSION[User::SESSION])
			||
			!$_SESSION[User::SESSION]
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0
		) {
			//Não está logado
			return false;

		} else {
			if ($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true) {
				return true;
			} else if ($inadmin === false) {

				return true;
			} else {

				return false;
			}
		}
	}

	public static function verifyLogin($inadmin = true)
	{
		if (!User::checkLogin($inadmin)) {
			if ($inadmin) {
				header("Location: /admin/login");
			} else {
				header("Location: /login");
			}
			exit;
		}
	}


	public static function login($login,$password){

		$sql = new Sql();

		$result = $sql->select("SELECT * FROM tb_users u INNER JOIN tb_persons p ON u.idperson = p.idperson WHERE u.deslogin= :LOGIN",array(
					"LOGIN"=>$login
		));

		if(count($result) === 0)
		{
			throw new \Exception("Usuario não existe , ou senha inválida");
		}

		$data = $result[0];

		if($data['validacao'] == 0){
			throw new \Exception("Conta não validada entre em seu email para validar e siga as instruções, caso ja tenha passado de 48 horas que se registrou entre em contato com suporte");
		}

		else if(password_verify($password, $data["despassword"]) === true){
			$user = new User();
			$user->setData($data);
			$_SESSION[User::SESSION] = $user->getValues();

			return $user;
		}

		else{
			throw new \Exception("Usuario não existe , ou senha inválida");
		}
	}

	
	public static function logout(){

		$_SESSION[User::SESSION] = NULL;

	}

	public static function getErrorLogin(){
		$msgError = (isset($_SESSION[User::ERROR_LOGIN]) && $_SESSION[User::ERROR_LOGIN]) ? $_SESSION[User::ERROR_LOGIN] : '';
		User::clearErrorLogin();

		return $msgError;
	}

	public static function setErrorLogin($msgError){
		$_SESSION[User::ERROR_LOGIN] = $msgError ;
	}

	public static function clearErrorLogin(){
		$_SESSION[User::ERROR_LOGIN] = NULL ;
	}

	public static function getErrorRegister(){
		$msgError = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER] : '' ; 
		User::clearErrorRegister();
		return $msgError;
	}

	public static function setErrorRegister($msgError){
		$_SESSION[User::ERROR_REGISTER] =$msgError ;
	}

	public static function clearErrorRegister(){
		$_SESSION[User::ERROR_REGISTER] = NULL;
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
					":despassword"=>User::getPasswordHash($this->getdespassword()),
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
					":despassword"=>User::getPasswordHash($this->getdespassword()),
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

	public static function getForgot($email)
	{
    	$sql = new Sql();

    	$results = $sql->select("SELECT * FROM tb_persons p INNER JOIN tb_users u 
    							 USING(idperson) WHERE p.desemail = :email;",
    							 array(":email"=>$email));

    	if(count($results) === 0 ){

    		throw new \Exception("não foi possivel recuperar senha, entrar em contato com suporte");
    		
    	}
    	else{

    		$data = $results[0];
    		$resultQuery = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)",
    				array(":iduser"=>$data["iduser"],
    					  ":desip"=>$_SERVER["REMOTE_ADDR"] 
    					));

    		if(count($resultQuery) === 0){

    			throw new \Exception("não foi possivel recuperar senha, entrar em contato com suporte");
    		}
    		else{

    			$dataRecovery = $resultQuery[0];
				$Cifra =  'AES-256-CBC';
				$IV = random_bytes(openssl_cipher_iv_length($Cifra));
				$Texto = openssl_encrypt($dataRecovery["idrecovery"], $Cifra, User::SECRET, OPENSSL_RAW_DATA, $IV);
				$code = base64_encode($IV.$Texto);
				$link = "http://www.lucascommerce.com.br/admin/forgot/reset?code=$code";
				$mailer = new Mailer($data["desemail"],$data["desperson"],"redefinir senha ecommerce", "forgot", array("name"=>$data["desperson"],"link"=>$link));
				$mailer->send();

				return $data;

    		}

    	}
 	}

 	public static function getForgotUser($email)
	{
    	$sql = new Sql();

    	$results = $sql->select("SELECT * FROM tb_persons p INNER JOIN tb_users u 
    							 USING(idperson) WHERE p.desemail = :email;",
    							 array(":email"=>$email));

    	if(count($results) === 0 ){
    		throw new \Exception("Email já cadastrado");
    		
    	}else{
    		
    	$data = $results[0];
    	$resultQuery = $sql->select("CALL sp_usersregister_create(:iduser,:desip)",array(
    		':iduser'=>$data['iduser'],
    		':desip'=>$_SERVER['REMOTE_ADDR']
    	));

    		if(count($resultQuery) === 0){
    			throw new \Exception("Erro ao Cadastrar Tente Novamente á alguns minutos");
    			
    		}else{
    			$dataRecovery = $resultQuery[0];
    			$Cifra =  'AES-256-CBC';
				$IV = random_bytes(openssl_cipher_iv_length($Cifra));
				$Texto = openssl_encrypt($dataRecovery["idrecovery"], $Cifra, User::V_USER, OPENSSL_RAW_DATA, $IV);
				$code = base64_encode($IV.$Texto);
				$code = str_replace('+', '-', $code);
				$link = "http://www.lucascommerce.com.br/forgot-sucess-Register?code=$code";
				$mailer = new Mailer($data["desemail"],$data["desperson"],"Validação email ecommerce", "forgot-validacao", array("name"=>$data["desperson"],"link"=>$link));
				$mailer->send();

				$sql->query("UPDATE tb_usersregister SET ulink = :link WHERE iduser = :iduser ",array(":iduser"=>$data['iduser'],
					":link"=>$code
		));

				return $data;

    		}
    	}
    	
 	}

	public static function validForgot($Resultado)
 	{
	     $Resultado = base64_decode($Resultado);
	     $Cifra =  'AES-256-CBC';
		 $TextoCifrado = mb_substr($Resultado, openssl_cipher_iv_length($Cifra), null, '8bit');		
		 $IV = mb_substr($Resultado, 0, openssl_cipher_iv_length($Cifra), '8bit');
		 $idrecovery = openssl_decrypt($TextoCifrado, $Cifra, User::SECRET, OPENSSL_RAW_DATA, $IV);

	     $sql = new Sql();
	     $results = $sql->select("
	         SELECT *
	         FROM tb_userspasswordsrecoveries a
	         INNER JOIN tb_users b USING(iduser)
	         INNER JOIN tb_persons c USING(idperson)
	         WHERE
	         a.idrecovery = :idrecovery
	         AND
	         a.dtrecovery IS NULL
	         AND
	         DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();
	     ", array(
	         ":idrecovery"=>$idrecovery
	     ));
	     if (count($results) === 0)
	     {
	         throw new \Exception("Não foi possível recuperar a senha.");
	     }
	     else
	     {
	         return $results[0];
	     }
 	}

 	public static function validForgotUser($Resultado)
 	{
 		 $Rec = $Resultado;
 	     $sql = new Sql();
	     $results = $sql->select("
	         SELECT *
	         FROM tb_usersregister a
	         INNER JOIN tb_users b USING(iduser)
	         INNER JOIN tb_persons c USING(idperson)
	         WHERE
	         a.ulink = :ulink
	         AND
	         a.dtrecovery IS NULL
	         AND
	         DATE_ADD(a.dtregister, INTERVAL 48 HOUR) >= NOW();
	     ", array(
	         ":ulink"=>$Rec
	     ));

	     if (count($results) === 0)
	     {	

	         throw new \Exception("Não foi possivel Validar seu código, Entre em contato com Suporte");
	     }
	     else
	     {
	         return $results[0];
	     }
 	}

 	public static function setForgotUsedUser($idrecovery){

		$sql = new Sql();
		$sql->query("UPDATE tb_usersregister SET dtrecovery = NOW() WHERE idrecovery = :idrecovery ",array(":idrecovery"=>$idrecovery));

	}

	public static function setForgotUsed($idrecovery){

		$sql = new Sql();
		$sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery ",array(":idrecovery"=>$idrecovery
		));

	}

	public function setValidacaoUser($idperson){

		$sql = new Sql();
		$sql->query("UPDATE tb_persons SET validacao = 1 WHERE idperson = :idperson",
			array( ":idperson"=>$idperson
		));

	}

	public function setPassword($password){

		$sql = new Sql();
		$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser",
			array(":password"=>$password,
				  ":iduser"=>$this->getiduser()
		));
	}

	public static function getPasswordHash($password){

		return password_hash($password, PASSWORD_DEFAULT,['cost'=>12]);
	}

	public static function checkLoginExist($login){
		$sql = new Sql();

		$result = $sql->select("SELECT deslogin FROM tb_users WHERE deslogin = :deslogin",[
			':deslogin'=>$login
		]);

		return (count($result) > 0);
	}


}

?>