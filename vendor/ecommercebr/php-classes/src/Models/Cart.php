<?php
namespace NC\Models;

use \NC\DB\Sql;
use \NC\Model;
use \NC\Mailer;
use \NC\Models\User;



class Cart extends Model{

	const SESSION = "Cart";

	public static function getFromSession(){

		$cart = new Cart();

		if(isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0 ){

			$cart->get((int)$_SESSION[Cart::SESSION]['idcart']);

		} else {

			$cart->getFromSessionID();

			if(!(int)$cart->getidcart() > 0) {

				$data = ['dessessionid'=>session_id()];

				if(User::checkLogin(false)){

					$user = User::getFromSession();
					$data['iduser'] = $user->getiduser();
				}

				$cart->setData($data);
				$cart->save();
				$cart->setToSession();
			}
		}

		return $cart;
	}

	public function setToSession(){

		$_SESSION[Cart::SESSION] = $this->getValues();
	}

	public function getFromSessionID(){

		$sql = new Sql();
		$results = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid",[
			':dessessionid'=>session_id()
		]);

		if (count($results) > 0) {
			
			$this->setData($results[0]);
		}
	}

	public function get(int $idcart){

		$sql = new Sql();
		$results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart",[
			':idcart'=>$idcart
		]);

		if (count($results) > 0) {

			$this->setData($results[0]);
		}
		

	}

	public function save(){

		$sql = new Sql();

		$results = $sql->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)",[
			':idcart'=>$this->getidcart(),
			':dessessionid'=>$this->getdessessionid(),
			':iduser'=>$this->getiduser(),
			':deszipcode'=>$this->getdeszipcode(),
			':vlfreight'=>$this->getvlfreight(),
			':nrdays'=>$this->getnrdays()
		]);
		
		$this->setData($results[0]);
	}

	public function addProduct(Products $product){

		$sql =new Sql();

		$sql->query("INSERT INTO tb_cartsproducts(idcart, idproduct) Values(:idcart , :idproduct)",[
			':idcart'=>$this->getidcart(),
			':idproduct'=>$product->getidproduct()
		]);
	}

	public function removeProduct(Products $product, $all = false){

		$sql = new Sql();

		if($all){
			$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL",[
				':idcart'=>$this->getidcart(),
				':idproduct'=>$product->getidproduct()
			]);
		} else {
			$sql->query("UPDATE tb_cartsproducts SET  dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL LIMIT 1",[
				':idcart'=>$this->getidcart(),
				':idproduct'=>$product->getidproduct()
			]);
		}
	}

	public function getProducts(){

		$sql = new Sql();

		$result = $sql->select("SELECT p.idproduct, p.desproduct, p.vlprice,p.vlwidth, p.vlheight, p.vllength, p.vlweight,p.desurl, COUNT(*) AS nrqtd, SUM(p.vlprice) AS vltotal 
			FROM tb_cartsproducts cp INNER JOIN tb_products p ON cp.idproduct = p.idproduct WHERE cp.idcart = :idcart
		    AND cp.dtremoved IS NULL 
		    GROUP BY p.idproduct, p.desproduct, p.vlprice,p.vlwidth, p.vlheight, p.vllength, p.vlweight 
		    ORDER BY p.desproduct",[
		    	':idcart'=>$this->getidcart()
		    ]);

		return Products::checkList($result);
	}

}

?>