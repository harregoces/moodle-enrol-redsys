<?php
if (! class_exists ( 'ISAuthenticationMessage' )) {
	include_once $GLOBALS ["REDSYS_API_PATH"] . "/Model/ISGenericXml.php";
	include_once $GLOBALS ["REDSYS_API_PATH"] . "/Model/ISRequestInterface.php";
	
	/**
	 * @XML_ELEM=DATOSENTRADA
	 */
	class ISAuthenticationMessage extends ISGenericXml implements ISRequestInterface {
		/**
		 * @XML_ELEM=DS_MERCHANT_PARESPONSE
		 */
		private $parResponse;
		
		/**
		 * @XML_ELEM=DS_MERCHANT_MD
		 */
		private $autSession;
		
		/**
		 * @XML_ELEM=DS_MERCHANT_ORDER
		 */
		private $order;
		
		/**
		 * @XML_ELEM=DS_MERCHANT_AMOUNT
		 */
		private $amount;
		
		/**
		 * @XML_ELEM=DS_MERCHANT_CURRENCY
		 */
		private $currency;
		
		/**
		 * @XML_ELEM=DS_MERCHANT_MERCHANTCODE
		 */
		private $merchant;
		
		/**
		 * @XML_ELEM=DS_MERCHANT_TERMINAL
		 */
		private $terminal;
		
		/**
		 * @XML_ELEM=DS_MERCHANT_TRANSACTIONTYPE
		 */
		private $transactionType;
		public function getParResponse() {
			return $this->parResponse;
		}
		public function setParResponse($parResponse) {
			$this->parResponse = preg_replace ( "/\r|\n/", "", $parResponse );
			return $this;
		}
		public function getAutSession() {
			return $this->autSession;
		}
		public function setAutSession($autSession) {
			$this->autSession = $autSession;
			return $this;
		}
		public function getOrder() {
			return $this->order;
		}
		public function setOrder($order) {
			$this->order = $order;
			return $this;
		}
		public function getAmount() {
			return $this->amount;
		}
		public function setAmount($amount) {
			$this->amount = $amount;
			return $this;
		}
		public function getCurrency() {
			return $this->currency;
		}
		public function setCurrency($currency) {
			$this->currency = $currency;
			return $this;
		}
		public function getMerchant() {
			return $this->merchant;
		}
		public function setMerchant($merchant) {
			$this->merchant = $merchant;
			return $this;
		}
		public function getTerminal() {
			return $this->terminal;
		}
		public function setTerminal($terminal) {
			$this->terminal = $terminal;
			return $this;
		}
		public function getTransactionType() {
			return $this->transactionType;
		}
		public function setTransactionType($transactionType) {
			$this->transactionType = $transactionType;
			return $this;
		}
	}
}
?>