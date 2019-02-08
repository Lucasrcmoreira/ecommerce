<?php
namespace NC\Models;

use \NC\DB\Sql;
use \NC\Model;
use \NC\Mailer;



class Category extends Model{

	public static function listAll(){

		$sql = new Sql();

		$result = $sql->select("SELECT * FROM tb_categories ORDER BY descategory");

		return $result;

	}


	public function save(){

		$sql = new Sql();

		$result = $sql->query("CALL sp_categories_save(:idcategory, :descategory)",array(
			":idcategory"=>$this->getidcategory(),
			":descategory"=>$this->getdescategory()
		));

		$this->setData($result[0]);

		Category::updateFileCategory();
	}

	public function get($idcategory){

		$sql = new Sql();

		$result = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory",array(
												":idcategory"=>$idcategory
		));

		$this->setData($result[0]);

	}

	public function delete(){

		$sql = new Sql();

		$sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory",array(
									":idcategory"=>$this->getidcategory()
		));

		Category::updateFileCategory();

	}

	public static function updateFileCategory(){

		$Categories = Category::listAll();

		$Category = [];

		foreach ($Categories as $row) {
			
			array_push($Category,'<li><a href="/categories/'.$row['idcategory'].'">'.$row['descategory'].'</a></li>'.'<br>');
		}

		file_put_contents($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."categories-menu.html",implode('', $Category ));
	}


	public function getProducts($related = true){

		$sql = new Sql();

		if($related === true){

		 return	$sql->select("SELECT * FROM tb_products WHERE idproduct IN(
						  SELECT a.idproduct FROM tb_products a INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
					      WHERE b.idcategory = :idcategory
						);",[':idcategory'=>$this->getidcategory()

		]);


		}else{

			return $sql->select("SELECT * FROM tb_products WHERE idproduct NOT IN(
						  SELECT a.idproduct FROM tb_products a INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
						  WHERE b.idcategory = :idcategory
			);",[':idcategory'=>$this->getidcategory()]);
		}
	}


	public function removeProducts(Products $product){

		$sql = new Sql();

		$sql->query("DELETE FROM tb_productscategories WHERE idcategory = :idcategory AND idproduct = :idproduct", [':idcategory'=>$this->getidcategory(),
			':idproduct'=>$product->getidproduct()
			 ]);

	}

	public function addProducts(Products $product){

		$sql = new Sql();

		$sql->query("INSERT INTO tb_productscategories (idcategory, idproduct) VALUES(:idcategory, :idproduct)", [
					':idcategory'=>$this->getidcategory(),
					':idproduct'=>$product->getidproduct()
		]);
	}

}

?>