<?php
/*
$string = '

<?xml version="1.0" encoding="utf-8"?>
<API3G>
	<Result>000</Result>
	<ResultExplanation>Transaction created</ResultExplanation>
	<TransToken>3D3E2D3C-6CA4-4660-8A1D-AD8920799814</TransToken>
	<TransRef>R34396448</TransRef>
</API3G>

';

$string = str_replace('<?xml version="1.0" encoding="utf-8"?>', '', $string);

$xml = simplexml_load_string($string);

echo $xml->Result;
*/

$now_dt = date_create()->format('Y/m/d H:i');    
    
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://secure.3gdirectpay.com/API/v6/',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'<?xml version="1.0" encoding="utf-8"?>
<API3G>
    <CompanyToken>8D3DA73D-9D7F-4E09-96D4-3D44E7A83EA3</CompanyToken>
    <Request>createToken</Request>
    <Transaction>
        <PaymentAmount>200</PaymentAmount>
        <PaymentCurrency>RWF</PaymentCurrency>
        <CompanyRef>49FKEOA</CompanyRef>
        <RedirectURL>http://www.gbdelivering.com/payurl.php</RedirectURL>
        <BackURL>http://www.gbdelivering.com/backurl.php </BackURL>
        <CompanyRefUnique>0</CompanyRefUnique>
        <PTL>5</PTL>
    </Transaction>
    <Services>
        <Service>
            <ServiceType>3854</ServiceType>
            <ServiceDescription>Bought Gbdelivering Product(s)</ServiceDescription>
            <ServiceDate>'.$now_dt.'</ServiceDate>
        </Service>
    </Services>
</API3G>',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/xml'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
//echo $response;
    
$response = str_replace('<?xml version="1.0" encoding="utf-8"?>', '', $response);

$xml = simplexml_load_string($response);

echo $xml->TransToken;

?>