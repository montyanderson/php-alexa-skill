<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class AlexaSkill {
	public function __construct() {
		$this->intents = [];
	}

	public function intent($name, $handler) {
		$this->intents[$name] = $handler;
	}

	public function run() {
		ob_start();
		$body = file_get_contents("php://input");
		$req = json_decode($body);

		$signatureCertChainUrl = $_SERVER["HTTP_SIGNATURECERTCHAINURL"];
		$signature = $_SERVER["HTTP_SIGNATURE"];

		// validate url

		$urlParts = parse_url($signatureCertChainUrl);

		if($urlParts["scheme"] !== "https")
			exit(1);

		if($urlParts["host"] !== "s3.amazonaws.com")
			exit(1);

		if(strpos($urlParts["path"], "/echo.api/", 0) !== 0)
		 	exit(1);

		if(array_key_exists("port", $urlParts) && $urlParts["port"] !== 443)
			exit(1);

		// decode signature
		$encrypted = base64_decode($signature);

		// download certificate from valid url
		$cert = file_get_contents($signatureCertChainUrl);

		// get general info about cert
		$certInfo = openssl_x509_parse($cert);
		var_dump($certInfo);

		// extract public key
		$publicKey = openssl_pkey_get_public($cert);

		// decrypt signature using public key
		$hash = "";
		openssl_public_decrypt($encrypted, $hash, $publicKey);

		// compare Amazon's hash with the actual hash
		if(substr($hash, -20) !== sha1($body, true))
			exit(1);

		$intent = $req->request->intent->name;

		if(array_key_exists($intent, $this->intents)) {
			$res = [
				"version" => "1.0",
				"sessionAttributes" => [],
				"response" => $this->intents[$intent]($req)
			];

			$debug = ob_get_clean();
			file_put_contents("/tmp/alexa/debug.txt", $debug);

			header("Content-Type: application/json");
			echo json_encode($res);
		} else {
			http_response_code(404);
			exit(0);
		}
	}
}
