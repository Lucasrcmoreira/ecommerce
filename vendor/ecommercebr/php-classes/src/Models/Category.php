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


}

?>