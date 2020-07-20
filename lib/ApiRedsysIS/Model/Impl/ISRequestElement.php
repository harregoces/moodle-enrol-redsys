<?php
if (! class_exists ( 'ISRequestElement' )) {
	include_once $GLOBALS["REDSYS_API_PATH"] . "/Model/ISGenericXml.php";
	include_once $GLOBALS["REDSYS_API_PATH"] . "/Model/Impl/ISOperationMessage.php";
	include_once $GLOBALS["REDSYS_API_PATH"] . "/Constants/ISConstants.php";
	
	/**
	 * @XML_ELEM=REQUEST
	 */
	class ISRequestElement extends ISGenericXml {
		/**
		 * @XML_CLASS=ISOperationMessage
		 */
		private $datosEntrada = null;
		
		/**
		 * @XML_ELEM=DS_SIGNATUREVERSION
		 */
		private $signatureVersion = null;
		
		/**
		 * @XML_ELEM=DS_SIGNATURE
		 */
		private $signature = null;
		function __construct() {
			$this->signatureVersion = ISConstants::$REQUEST_SIGNATUREVERSION_VALUE;
		}
		public function getDatosEntrada() {
			return $this->datosEntrada;
		}
		public function setDatosEntrada($datosEntrada) {
			$this->datosEntrada = $datosEntrada;
			return $this;
		}
		public function getSignatureVersion() {
			return $this->signatureVersion;
		}
		public function setSignatureVersion($signatureVersion) {
			$this->signatureVersion = $signatureVersion;
			return $this;
		}
		public function getSignature() {
			return $this->signature;
		}
		public function setSignature($signature) {
			$this->signature = $signature;
			return $this;
		}
		public function __toString() {
			$string = "ISRequestElement{";
			$string .= 'datosEntrada: ' . $this->getDatosEntrada () . ', ';
			$string .= 'signatureVersion: ' . $this->getSignatureVersion () . ', ';
			$string .= 'signature: ' . $this->getSignature () . '';
			return $string . "}";
		}
	}
}
