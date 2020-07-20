<?php

if(!class_exists('ISService')){
	include_once $GLOBALS["REDSYS_API_PATH"]."/Service/ISOperationService.php";
	include_once $GLOBALS["REDSYS_API_PATH"]."/Service/Impl/ISDCCConfirmationService.php";
	include_once $GLOBALS["REDSYS_API_PATH"]."/Model/Impl/ISDCCConfirmationMessage.php";
	include_once $GLOBALS["REDSYS_API_PATH"]."/Model/Impl/ISResponseMessage.php";
	include_once $GLOBALS["REDSYS_API_PATH"]."/Model/Impl/ISRequestElement.php";
	include_once $GLOBALS["REDSYS_API_PATH"]."/Utils/ISSignatureUtils.php";
	include_once $GLOBALS["REDSYS_API_PATH"]."/Utils/ISLogger.php";
	
	class ISService extends ISOperationService{
		private $request;
		function __construct($signatureKey, $env){
			parent::__construct($signatureKey, $env);
		}

		public function createRequestMessage($message){
			$this->request=$message;
			$req=new ISRequestElement();
			$req->setDatosEntrada($message);
			
			$tagDE=$message->toXml();
			
			$signatureUtils=new ISSignatureUtils();
			$localSignature=$signatureUtils->createMerchantSignatureHostToHost($this->getSignatureKey(), $tagDE);
			
			$req->setSignature($localSignature);
			
			return $req->toXml();
		}
		
		public function createResponseMessage($trataPeticionResponse){
			$response=new ISResponseMessage();
			$dccElem=$response->getTagContent(ISConstants::$RESPONSE_DCC_MARGIN_TAG, $trataPeticionResponse);
			
			if($dccElem!==NULL && strlen($dccElem)){
				$dccService=new ISDCCConfirmationService($this->getSignatureKey(), $this->getEnv());
				$dccResponse=$dccService->unMarshallResponseMessage($trataPeticionResponse);
				ISLogger::debug("Received ".ISLogger::beautifyXML($dccResponse->toXml()));
				
				$dccConfirmation=new ISDCCConfirmationMessage();
				$currency="";
				$amount="";
				if($this->request->isDcc()){
					$currency=$dccResponse->getDcc0()->getCurrency();
					$amount=$dccResponse->getDcc0()->getAmount();
				}
				else{
					$currency=$dccResponse->getDcc1()->getCurrency();
					$amount=$dccResponse->getDcc1()->getAmount();
				}
				
				$dccConfirmation->setCurrencyCode($currency, $amount);
				$dccConfirmation->setMerchant($this->request->getMerchant());
				$dccConfirmation->setTerminal($this->request->getTerminal());
				$dccConfirmation->setOrder($this->request->getOrder());
				$dccConfirmation->setSesion($dccResponse->getSesion());
				
				$response=$dccService->sendOperation($dccConfirmation);
			}
			else{
				$acsElem=$response->getTagContent(ISConstants::$RESPONSE_ACS_URL_TAG, $trataPeticionResponse);
				$response->parseXml($trataPeticionResponse);
				ISLogger::debug("Received ".ISLogger::beautifyXML($response->toXml()));

				if($acsElem!==NULL && strlen($acsElem)){
					if($response->getApiCode()!==ISConstants::$RESP_CODE_OK
							|| !$this->checkSignature($response->getOperation()))
						{
							$response->setResult(ISConstants::$RESP_LITERAL_KO);
						}
						else{
							$response->setResult(ISConstants::$RESP_LITERAL_AUT);
						}
				}
				else{
					$transType = $response->getTransactionType();
					if(!$this->checkSignature($response->getOperation())
							|| $response->getApiCode()!==ISConstants::$RESP_CODE_OK)
					{
						$response->setResult(ISConstants::$RESP_LITERAL_KO);
					}
					else{
						switch ((int)$response->getOperation()->getResponseCode()){
							case ISConstants::$AUTHORIZATION_OK: $response->setResult(($transType==ISConstants::$AUTHORIZATION || $transType==ISConstants::$PREAUTHORIZATION)?ISConstants::$RESP_LITERAL_OK:ISConstants::$RESP_LITERAL_KO); break;
							case ISConstants::$CONFIRMATION_OK: $response->setResult(($transType==ISConstants::$CONFIRMATION || $transType==ISConstants::$REFUND)?ISConstants::$RESP_LITERAL_OK:ISConstants::$RESP_LITERAL_KO);  break;
							case ISConstants::$CANCELLATION_OK: $response->setResult($transType==ISConstants::$CANCELLATION?ISConstants::$RESP_LITERAL_OK:ISConstants::$RESP_LITERAL_KO);  break;
							default: $response->setResult(ISConstants::$RESP_LITERAL_KO);
						}
					}
				}				
			}
			
			if($response->getResult()==ISConstants::$RESP_LITERAL_OK){
				ISLogger::info("Operation finished successfully");
			}
			else{
				if($response->getResult()==ISConstants::$RESP_LITERAL_AUT){
					ISLogger::info("Operation requires autentication");
				}
				else{
					ISLogger::info("Operation finished with errors");
				}
			}
			return $response;
		}
		
		public function unMarshallResponseMessage($message){
			$response=new ISResponseMessage();
			$response->parseXml($message);
			return $response;
		}
	}
}