<?php

namespace NC;

use Rain\tpl;

class Page{

	private $tpl;
	private $options = [];
	private $defaults = [
				"data"=>[]
	];

	public function __construct($opts = array()){

		$this->options = array_merge($this->defaults, $opts);

		$config = array(
			"tpl_dir"       => $_SERVER["DOCUMENT_ROOT"]."/views/",
			"cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/views-cache/",
			"debug"         => false
		);

		tpl::configure($config);

		$this->tpl = new tpl;

		$this->setData($this->options["data"]);

		$this->tpl->draw("header");
	}

	public function setTpl($name,$data =array(),$returnHTML = false){

		$this->setData($data);

		return $this->tpl->draw($name,$returnHTML);

	}

	private function setData($data= array()){

		foreach ($this->options["data"] as $key => $value) {
			
			$this->tpl->assign($key,$value);
		}
	}


	public function __destruct(){

		$this->tpl->draw("footer");


	}

}

?>