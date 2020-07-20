<?php
if (! class_exists ( 'ISOperationElement' )) {
	include_once $GLOBALS ["REDSYS_API_PATH"] . "/Model/ISGenericXml.php";
	
	/**
	 * @XML_ELEM=OPERACION
	 */
	class ISOperationElement extends ISGenericXml {
		/**
		 * @XML_ELEM=Ds_Amount
		 */
		private $amount;
		
		/**
		 * @XML_ELEM=Ds_Currency
		 */
		private $currency;
		
		/**
		 * @XML_ELEM=Ds_Order
		 */
		private $order;
		
		/**
		 * @XML_ELEM=Ds_Signature
		 */
		private $signature;
		
		/**
		 * @XML_ELEM=Ds_MerchantCode
		 */
		private $merchant;
		
		/**
		 * @XML_ELEM=Ds_Terminal
		 */
		private $terminal;
		
		/**
		 * @XML_ELEM=Ds_Response
		 */
		private $responseCode;
		
		/**
		 * @XML_ELEM=Ds_AuthorisationCode
		 */
		private $authCode;
		
		/**
		 * @XML_ELEM=Ds_TransactionType
		 */
		private $transactionType;
		
		/**
		 * @XML_ELEM=Ds_SecurePayment
		 */
		private $securePayment;
		
		/**
		 * @XML_ELEM=Ds_Language
		 */
		private $language;
		
		/**
		 * @XML_ELEM=Ds_MerchantData
		 */
		private $merchantData;
		
		/**
		 * @XML_ELEM=Ds_Card_Country
		 */
		private $cardCountry;
		
		/**
		 * @XML_ELEM=Ds_CardNumber
		 */
		private $cardNumber;
		
		/**
		 * @XML_ELEM=Ds_ExpiryDate
		 */
		private $expiryDate;
		
		/**
		 * @XML_ELEM=Ds_Merchant_Identifier
		 */
		private $merchantIdentifier;
		
		/**
		 * @XML_ELEM=Ds_Card_Brand
		 */
		private $cardBrand;
		
		/**
		 * @XML_ELEM=Ds_Card_Type
		 */
		private $cardType;
		
		/**
		 * @XML_ELEM=Ds_AcsUrl
		 */
		private $acsUrl;
		
		/**
		 * @XML_ELEM=Ds_PaRequest
		 */
		private $paRequest;
		
		/**
		 * @XML_ELEM=Ds_MD
		 */
		private $autSession;
		
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
		public function getOrder() {
			return $this->order;
		}
		public function setOrder($order) {
			$this->order = $order;
			return $this;
		}
		public function getSignature() {
			return $this->signature;
		}
		public function setSignature($signature) {
			$this->signature = $signature;
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
		public function getResponseCode() {
			return $this->responseCode;
		}
		public function setResponseCode($responseCode) {
			$this->responseCode = $responseCode;
			return $this;
		}
		public function getAuthCode() {
			return $this->authCode;
		}
		public function setAuthCode($authCode) {
			$this->authCode = $authCode;
			return $this;
		}
		public function getTransactionType() {
			return $this->transactionType;
		}
		public function setTransactionType($transactionType) {
			$this->transactionType = $transactionType;
			return $this;
		}
		public function getSecurePayment() {
			return $this->securePayment;
		}
		public function setSecurePayment($securePayment) {
			$this->securePayment = $securePayment;
			return $this;
		}
		public function getLanguage() {
			return $this->language;
		}
		public function setLanguage($language) {
			$this->language = $language;
			return $this;
		}
		public function getMerchantData() {
			return $this->merchantData;
		}
		public function setMerchantData($merchantData) {
			$this->merchantData = $merchantData;
			return $this;
		}
		public function getCardCountry() {
			return $this->cardCountry;
		}
		public function setCardCountry($cardCountry) {
			$this->cardCountry = $cardCountry;
			return $this;
		}
		public function getCardNumber() {
			return $this->cardNumber;
		}
		public function setCardNumber($cardNumber) {
			$this->cardNumber = $cardNumber;
			return $this;
		}
		public function getExpiryDate() {
			return $this->expiryDate;
		}
		public function setExpiryDate($expiryDate) {
			$this->expiryDate = $expiryDate;
			return $this;
		}
		public function getMerchantIdentifier() {
			return $this->merchantIdentifier;
		}
		public function setMerchantIdentifier($merchantIdentifier) {
			$this->merchantIdentifier = $merchantIdentifier;
			return $this;
		}
		public function getCardBrand(){
			return $this->cardBrand;
		}
		public function setCardBrand($cardBrand){
			$this->cardBrand = $cardBrand;
			return $this;
		}
		public function getCardType(){
			return $this->cardType;
		}
		public function setCardType($cardType){
			$this->cardType = $cardType;
			return $this;
		}
		public function getAcsUrl() {
			return $this->acsUrl;
		}
		public function setAcsUrl($acsUrl) {
			$this->acsUrl = $acsUrl;
			return $this;
		}
		public function getPaRequest() {
			return $this->paRequest;
		}
		public function setPaRequest($paRequest) {
			$this->paRequest = preg_replace( "/\r|\n/", "", $paRequest );
			return $this;
		}
		public function getAutSession() {
			return $this->autSession;
		}
		public function setAutSession($autSession) {
			$this->autSession = $autSession;
			return $this;
		}
		public function __toString() {
			$string = "ISOperationElement{";
			$string .= 'amount: ' . $this->getAmount () . ', ';
			$string .= 'currency: ' . $this->getCurrency () . ', ';
			$string .= 'order: ' . $this->getOrder () . ', ';
			$string .= 'signature: ' . $this->getSignature () . ', ';
			$string .= 'merchant: ' . $this->getMerchant () . ', ';
			$string .= 'terminal: ' . $this->getTerminal () . ', ';
			$string .= 'responseCode: ' . $this->getResponseCode () . ', ';
			$string .= 'authCode: ' . $this->getAuthCode () . ', ';
			$string .= 'transactionType: ' . $this->getTransactionType () . ', ';
			$string .= 'securePayment: ' . $this->getSecurePayment () . ', ';
			$string .= 'language: ' . $this->getLanguage () . ', ';
			$string .= 'merchantData: ' . $this->getMerchantData () . ', ';
			$string .= 'cardCountry: ' . $this->getCardCountry () . ', ';
			$string .= 'cardNumber: ' . $this->getCardNumber () . ', ';
			$string .= 'expiryDate: ' . $this->getExpiryDate () . ', ';
			$string .= 'merchantIdentifier: ' . $this->getMerchantIdentifier () . '';
			$string .= 'acsUrl: ' . $this->getAcsUrl () . ', ';
			$string .= 'paRequest: ' . $this->getPaRequest () . ', ';
			$string .= 'autSession: ' . $this->getAutSession () . '';
			return $string . "}";
		}
	}

}
