<?php
namespace NC;

use Rain\Tpl;
use PHPMailer\PHPMailer\PHPMailer; 

class Mailer{

	//constantes criadas para facilitar na troca de remetente caso precise..
	const USEREMAIL = "lucas.ecommerce.shopsite@gmail.com";//email que enviará
	const USERPASSWORD = "testeecommerce";//senha do email que irá enviar
	const USERNAME = "Lucas Ramos";//nome do remetente

	private $mail;

	public function __construct($toAddress, $toName,$subject, $tplName, $data = array())
	{


		$config = array(
			"tpl_dir"       => $_SERVER["DOCUMENT_ROOT"]."/views/email/",
			"cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/views-cache/",
			"debug"         => false
		);

		tpl::configure($config);

		$tpl = new tpl;

		foreach ($data as $key => $value) {
			$tpl->assign($key,$value);
		}


		$html = $tpl->draw($tplName,true);

		//Create a new PHPMailer instance
		$this->mail = new \PHPMailer;

		//Tell PHPMailer to use SMTP
		$this->mail->isSMTP();

		//Enable SMTP debugging
		// 0 = off (for production use)
		// 1 = client messages
		// 2 = client and server messages
		$this->mail->SMTPDebug = 0;

		//Set the hostname of the mail server
		$this->mail->Host = 'smtp.gmail.com';
		// use
		// $mail->Host = gethostbyname('smtp.gmail.com');
		// if your network does not support SMTP over IPv6

		//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
		$this->mail->Port = 587;

		$this->mail->SMTPOptions = array('ssl'=>array(
														'verify_peer'=>false,
														'verify_peer_name'=>false,
														'allow_self_signed'=>true

		));

		//Set the encryption system to use - ssl (deprecated) or tls
		$this->mail->SMTPSecure = 'tls';

		//Whether to use SMTP authentication
		$this->mail->SMTPAuth = true;

		//Username to use for SMTP authentication - use full email address for gmail
		$this->mail->Username = Mailer::USEREMAIL;//EMAIL DE ENVIO 

		//Password to use for SMTP authentication
		$this->mail->Password = Mailer::USERPASSWORD;//SENHA DO EMAIL DE ENVIO

		//Set who the message is to be sent from
		$this->mail->setFrom(Mailer::USEREMAIL, Mailer::USERNAME);//QUEM ESTÁ ENVIANDO

		//Set an alternative reply-to address
		//$mail->addReplyTo('replyto@example.com', 'First Last');

		//Set who the message is to be sent to
		$this->mail->addAddress($toAddress, $toName);//REMETENTE / PARA QUEM VAI SER ENVIADO

		//Set the subject line
		$this->mail->Subject = $subject;//ASSUNTO EMAIL

		//Read an HTML message body from an external file, convert referenced images to embedded,
		//convert HTML into a basic plain-text alternative body
		$this->mail->msgHTML($html);//PEGA O CONTEUDO DE UM ARQUIVO

		//Replace the plain text body with one created manually
		$this->mail->AltBody = 'This is a plain-text message body';//MENSAGEM SECUNDÁRIA CASO O CONTEUDO ACIMA DER ERRO

	}

	public function send(){

		return $this->mail->send();
	}

}



?>