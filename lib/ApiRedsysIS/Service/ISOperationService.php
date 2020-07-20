<?php
if (! class_exists ( 'ISOperationService' )) {
	include_once $GLOBALS["REDSYS_API_PATH"] . "/Model/ISResponseInterface.php";
	include_once $GLOBALS["REDSYS_API_PATH"] . "/Model/ISRequestInterface.php";
	include_once $GLOBALS["REDSYS_API_PATH"] . "/Utils/ISSignatureUtils.php";
	include_once $GLOBALS["REDSYS_API_PATH"] . "/Utils/ISLogger.php";
	abstract class ISOperationService {
		private $signatureKey = null;
		private $env;
		function __construct($signatureKey, $env) {
			$this->signatureKey = $signatureKey;
			$this->env = $env;
		}
		public function createRequestSOAPMessage($message) {
			$messageXml = $this->createRequestMessage ( $message );
			$soap_request = "<?xml version=\"1.0\"?>\n";
			$soap_request .= '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:web="http://webservice.sis.sermepa.es">';
			$soap_request .= '<soapenv:Header/>';
			$soap_request .= '<soapenv:Body>';
			$soap_request .= '<web:trataPeticion>';
			$soap_request .= '<web:datoEntrada><![CDATA[' . $messageXml . ']]></web:datoEntrada>';
			$soap_request .= '</web:trataPeticion>';
			$soap_request .= '</soapenv:Body>';
			$soap_request .= '</soapenv:Envelope>';

			ISLogger::debug("Sending ".ISLogger::beautifyXML($messageXml));
			return $soap_request;
		}
		public function sendOperation($message) {
			$result="";
			$soap_request = $this->createRequestSOAPMessage ( $message );
			$header = array (
					"Content-type: text/xml;charset=\"utf-8\"",
					"Accept: text/xml",
					"Cache-Control: no-cache",
					"Pragma: no-cache",
					"SOAPAction: \"run\"",
					"Content-length: " . strlen ( $soap_request ) 
			);
			$url_ws = ISConstants::$SANDBOX_ENDPOINT;
			if ($this->env == ISConstants::$ENV_PRODUCTION)
				$url_ws = ISConstants::$PRODUCTION_ENDPOINT;
			
			$soap_do = curl_init ();
			curl_setopt ( $soap_do, CURLOPT_URL, $url_ws );
			curl_setopt ( $soap_do, CURLOPT_CONNECTTIMEOUT, ISConstants::$CONNECTION_TIMEOUT_VALUE );
			curl_setopt ( $soap_do, CURLOPT_TIMEOUT, ISConstants::$READ_TIMEOUT_VALUE );
			curl_setopt ( $soap_do, CURLOPT_RETURNTRANSFER, true );
			curl_setopt ( $soap_do, CURLOPT_SSL_VERIFYHOST, 0 );
			curl_setopt ( $soap_do, CURLOPT_SSL_VERIFYPEER, 0 );
			curl_setopt ( $soap_do, CURLOPT_SSLVERSION, ISConstants::$SSL_TLSv12 );
			curl_setopt ( $soap_do, CURLOPT_POST, true );
			curl_setopt ( $soap_do, CURLOPT_POSTFIELDS, $soap_request );
			curl_setopt ( $soap_do, CURLOPT_HTTPHEADER, $header );
			
			ISLogger::info("Performing request to '".$url_ws."'");
			$tmp = curl_exec ( $soap_do );
			$httpCode=curl_getinfo($soap_do,CURLINFO_HTTP_CODE);
			
			if($tmp !== false && $httpCode==200){
				$tag = array ();
				preg_match ( "/<p[0-9]+:trataPeticionReturn>/", $tmp, $tag );
				$result = htmlspecialchars_decode ( $message->getTagContent ( str_replace ( "<", "", str_replace ( ">", "", $tag [0] ) ), $tmp ) );
			}
			else{
				$strError="Request failure ".(($httpCode!=200)?"[HttpCode: '".$httpCode."']":"").((curl_error($soap_do))?" [Error: '".curl_error($soap_do)."']":"");
				ISLogger::error($strError);
			}
			
			curl_close( $soap_do );
			return $this->createResponseMessage ( $result );
		}
		public abstract function createRequestMessage($message);
		public abstract function createResponseMessage($trataPeticionResponse);
		public abstract function unMarshallResponseMessage($message);
		protected function checkSignature($op) {
			$result = false;
			if (null != $op && null != $op->getSignature ()) {
				$signatureValues = "";
				$calcSignature = null;
				$order = $op->getOrder ();
				
				$signatureValues .= $op->getAmount ();
				$signatureValues .= $order;
				$signatureValues .= $op->getMerchant ();
				$signatureValues .= $op->getCurrency ();
				if (null != $op->getResponseCode ()) {
					$signatureValues .= $op->getResponseCode ();
				}
				if (null != $op->getCardNumber ()) {
					$signatureValues .= $op->getCardNumber ();
				}
				$signatureValues .= $op->getTransactionType ();
				if (null != $op->getSecurePayment ()) {
					$signatureValues .= $op->getSecurePayment ();
				}
				if (null != $op->getAutSession ()) {
					$signatureValues .= $op->getAutSession ();
				}
				
				$calcSignature = ISSignatureUtils::createMerchantSignatureResponseHostToHost ( $this->getSignatureKey(), $signatureValues, $order );
				$result = $op->getSignature () == $calcSignature;
				if(!$result)
					ISLogger::error("Signature doesnt match: '".$op->getSignature ()."' <> '".$calcSignature."'");
				else
					ISLogger::debug("Signature matches");
			}
			return $result;
		}
		public function getSignatureKey() {
			return $this->signatureKey;
		}
		public function getEnv() {
			return $this->env;
		}
		public function __toString() {
			$rc=new ReflectionClass(get_class($this));
			$string = $rc->getName()."{";
			$string .= 'signatureKey: ' . $this->getSignatureKey () . ', ';
			$string .= 'env: ' . $this->getEnv () . '';
			return $string . "}";
		}
	}
}