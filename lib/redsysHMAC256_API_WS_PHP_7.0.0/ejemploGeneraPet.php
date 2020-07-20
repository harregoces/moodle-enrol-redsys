<?php

// Se incluye la librer
include 'apiRedsysWs.php';

function getTagContent($tag, $xml){
	$retorno=NULL;

	if($tag && $xml){
		$ini=strpos($xml, "<".$tag.">");
		$fin=strpos($xml, "</".$tag.">");
		if($ini!==false && $fin!==false){
			$ini=$ini+strlen("<".$tag.">");
			if($ini<=$fin){
				$retorno=substr($xml, $ini, $fin-$ini);
			}
		}
	}
		
	return $retorno;
}

// Se crea Objeto
$miObj = new RedsysAPIWs;

$fuc="999008881";
$terminal="131";
$moneda="978";
$trans="A";

$pan="4548810000000003";
$cvv="123";
$expire="491";
$url="";
	
$id=time();
$amount="145";


//XML DE ENTRADA
$datosEnt="<DATOSENTRADA>
<DS_MERCHANT_MERCHANTNAME>Comercio de Prue</DS_MERCHANT_MERCHANTNAME>
<DS_MERCHANT_AMOUNT>200</DS_MERCHANT_AMOUNT>
<DS_MERCHANT_CURRENCY>978</DS_MERCHANT_CURRENCY>
<DS_MERCHANT_MERCHANTURL>".$url."</DS_MERCHANT_MERCHANTURL>
<DS_MERCHANT_TRANSACTIONTYPE>".$trans."</DS_MERCHANT_TRANSACTIONTYPE>
<DS_MERCHANT_TERMINAL>871</DS_MERCHANT_TERMINAL>
<DS_MERCHANT_MERCHANTCODE>999008881</DS_MERCHANT_MERCHANTCODE>
<DS_MERCHANT_ORDER>".$id."</DS_MERCHANT_ORDER>
<DS_MERCHANT_PAN>4548810000000003</DS_MERCHANT_PAN>
<DS_MERCHANT_EXPIRYDATE>4912</DS_MERCHANT_EXPIRYDATE>
<DS_MERCHANT_CVV2>123</DS_MERCHANT_CVV2>
</DATOSENTRADA>";


//Nueva versión HMAC
$kc = 'zDcz/9zoYrcw9mbWeH/ZnZdJC3l2bquC';
$nuevaFirma = $miObj->createMerchantSignatureHostToHost($kc,$datosEnt,$id);

$nuevaEntrada = "<REQUEST>".$datosEnt."<DS_SIGNATUREVERSION>HMAC_SHA256_V1</DS_SIGNATUREVERSION><DS_SIGNATURE>".$nuevaFirma."</DS_SIGNATURE></REQUEST>";
print "<xmp>".$nuevaEntrada."</xmp>";
//$nuevaEntrada = str_replace("+","%2B",$nuevaEntrada);

  $soap_request  = "<?xml version=\"1.0\"?>\n";
  $soap_request .= '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:web="http://webservice.sis.sermepa.es">';
  $soap_request .= '<soapenv:Header/>';
  $soap_request .= '<soapenv:Body>';
  $soap_request .= '<web:trataPeticion>';
  $soap_request .= '<web:datoEntrada><![CDATA['.$nuevaEntrada.']]></web:datoEntrada>';
  $soap_request .= '</web:trataPeticion>';
  $soap_request .= '</soapenv:Body>';
  $soap_request .= '</soapenv:Envelope>';
  
  $header = array(
    "Content-type: text/xml;charset=\"utf-8\"",
    "Accept: text/xml",
    "Cache-Control: no-cache",
    "Pragma: no-cache",
    "SOAPAction: \"run\"",
    "Content-length: ".strlen($soap_request),
  );
 
  $soap_do = curl_init();
  curl_setopt($soap_do, CURLOPT_URL, "https://sis-i.redsys.es:25443/sis/services/SerClsWSEntrada" );
  curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10);
  curl_setopt($soap_do, CURLOPT_TIMEOUT,        10);
  curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true );
  curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, 0);
  curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($soap_do, CURLOPT_POST,           true );
  curl_setopt($soap_do, CURLOPT_POSTFIELDS,     $soap_request);
  curl_setopt($soap_do, CURLOPT_HTTPHEADER,     $header);
	
	$data = curl_exec($soap_do);
 
  if($data === false) {
    $err = 'Curl error: ' . curl_error($soap_do);
    curl_close($soap_do);
    print $err;
  } else {
  	$tag = array ();
  	preg_match ( "/<p[0-9]+:trataPeticionReturn>/", $data, $tag );
  	$result = htmlspecialchars_decode ( getTagContent ( str_replace ( "<", "", str_replace ( ">", "", $tag [0] ) ), $data ) );
  	
	print "<xmp>".$result."</xmp>";
    curl_close($soap_do);

    $signatureValues = $miObj->getTagContent($result,"Ds_Amount");
    $signatureValues .= $id;
    $signatureValues .= $miObj->getTagContent($result,"Ds_MerchantCode");
    $signatureValues .= $miObj->getTagContent($result,"Ds_Currency");
    $signatureValues .= $miObj->getTagContent($result,"Ds_Response");
    $signatureValues .= $miObj->getTagContent($result,"Ds_TransactionType");
    $signatureValues .= $miObj->getTagContent($result,"Ds_SecurePayment");
    
    echo $signatureValues."<br/>";
    echo $miObj->createMerchantSignatureResponseHostToHost($kc, $signatureValues, $id);
  }

	
?>
