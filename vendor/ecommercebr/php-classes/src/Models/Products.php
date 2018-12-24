<?php
namespace NC\Models;

use \NC\DB\Sql;
use \NC\Model;



class Products extends Model{

	public static function listAll(){

		$sql = new Sql();

		$result = $sql->select("SELECT * FROM tb_products ORDER BY desproduct");

		return $result;

	}


	public function save(){

		$sql = new Sql();

		$result = $sql->query("CALL sp_products_save(:idproduct, :desproduct,:vlprice, :vlwidth, :vlheight, :vllenght, :vlweight, :desurl)",array(
			":idproduct"=>$this->getidproduct(),
			":desproduct"=>$this->getdesproduct(),
			":vlprice"=>$this->getvlprice(),
			":vlwidth"=>$this->getvlwidth(),
			"vlheight"=>$this->getvlheight(),
			"vllenght"=>$this->getvllenght(),
			"vlweight"=>$this->getvlweight(),
			"desurl"=>$this->getdesurl()
		));

		$this->setData($result[0]);
	}

	public function get($idproduct){

		$sql = new Sql();

		$result = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct",array(
												":idproduct"=>$idproduct
		));

		$this->setData($result[0]);

	}

	public function delete(){

		$sql = new Sql();

		$sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct",array(
									":idproduct"=>$this->getidproduct()
		));

	}

}

?>