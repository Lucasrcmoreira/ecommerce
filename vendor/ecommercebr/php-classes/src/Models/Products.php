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

		$result = $sql->query("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)",array(
			":idproduct"=>$this->getidproduct(),
			":desproduct"=>$this->getdesproduct(),
			":vlprice"=>$this->getvlprice(),
			":vlwidth"=>$this->getvlwidth(),
			"vlheight"=>$this->getvlheight(),
			"vllength"=>$this->getvllength(),
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

	public function checkPhoto(){

		if(file_exists($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR."site".DIRECTORY_SEPARATOR."img".DIRECTORY_SEPARATOR."produtos".DIRECTORY_SEPARATOR.$this->getidproduct().".jpg")){

			$url = "/site/img/produtos/".$this->getidproduct().".jpg";

		} else {

			$url = "/site/img/produtos/product.jpg";

		}

		return $this->setdesphoto($url);

	}

	public function getValues(){

		$this->checkPhoto();

		$values = parent::getValues();

		return $values;
	}

	public function  setPhoto($file){

		$extension = explode('.', $file['name']);
		$extension = end($extension);

		switch ($extension) {
			case 'jpg':
			case 'jpeg':
				$image = imagecreatefromjpeg($file["tmp_name"]);
				break;

			case 'gif':
				$image = imagecreatefromgif($file["tmp_name"]);
				break;

			case 'png':
				$image = imagecreatefrompng($file["tmp_name"]);
				break;
		}

		$img = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR."site".DIRECTORY_SEPARATOR."img".DIRECTORY_SEPARATOR."produtos".DIRECTORY_SEPARATOR.$this->getidproduct().".jpg";

		imagejpeg($image, $img);

		imagedestroy($image);

		$this->checkPhoto();
	}

}

?>