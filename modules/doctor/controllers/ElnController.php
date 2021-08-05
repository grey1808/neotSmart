<?php


namespace app\modules\doctor\controllers;

use app\modules\doctor\models\Client;
use app\modules\doctor\models\Person;
use app\modules\doctor\models\Personfssaliases;
use app\modules\doctor\models\TempInvalidELN;
use app\modules\doctor\models\TempinvalidelnPeriod;
use SoapClient;
use SoapVar;
use XMLWriter;
use yii\httpclient\Response;
use yii\httpclient\XmlParser;
use app\modules\doctor\models\FormEln;
use Yii;
use function GuzzleHttp\Psr7\str;

class MySoapClient extends SoapClient {
        function __doRequest($request, $location, $action, $version, $one_way = 0) {
//            $request = simplexml_load_string($request);
            debug($request);
            die();

            $request = str_replace('&#13;', " ", $request);


        // здесь можете увидеть xml который отправляется, перехватить его, исправить
        // он в переменной $request
            $result = parent::__doRequest($request, $location, $action, $version, $one_way = 0);
            echo $result;
            die();
            return $result;
    }

}

class ElnController extends AppDoctorController
{

    public $wsdl = 'http://10.226.1.141:9903/FSS?wsdl';

    public function actionIndex(){

        $model = new FormEln();
        $model->dateIssue = date('d.m.Y');

        $cert = $this->actionGetCerts();

        return $this->render('index',compact('model','cert'));
    }

    public function actionView($client_id){
        return $this->render('view',compact('client_id'));
    }

    public function actionGetCerts(){

        $client = new SoapClient($this->wsdl, array("trace"=>1));

        $getCerts = json_decode(json_encode($client->getCerts()),1);
        $arr = explode(";", $getCerts['return']['message']);
        $result = array();
        foreach($arr as $key => $line){
            if (!empty($line)){
                $alias = explode(':',$line);
                $result[$key]['name'] = $alias[0];
                $result[$key]['alias'] = $alias[1];
            }
        } // преобразуем строку в ассоциативный массив


        $personfssaliases = Personfssaliases::find()->where(['person_id'=>\Yii::$app->user->getId()])->all();
        // выбраем из массива ключи, которые привязаны к пользователю
        $listUserCert = array();
        foreach ($personfssaliases as $person){
            foreach ($result as $item) {
                if ($person->alias == $item['alias'])
                {
                    $listUserCert[] = $item;
                }
            }
        }

        return $listUserCert;
    } // получаем список сертификатов локально и сравниваем его с тем что добавлено пользователю

    public function actionGetElnNum(){
        $aliasstore = Yii::$app->request->post('alias');
        $aliasstore = explode('(',$aliasstore);
        $aliasstore[1] = strstr($aliasstore[1],')',true);


        $password = Yii::$app->request->post('password');
        $alias = $aliasstore[0];
        $store = $aliasstore[1];

//        $alias = PodpisMo
//        $password = 12345678;
//        $store = 'HDImageStore';
        $checkPassword = $this->checkPassword($alias,$password,$store);

        if ($checkPassword['status'] !== 1){
            $error = $this->getError($checkPassword,'checkPassword');
            return json_encode($error);
        } // проверить пароль
        $getCertificate = $this->getCertificate($alias,$store); // получить подпись МО


        if ($getCertificate['status'] !== 1){
            $error = $this->getError($checkPassword,'getCertificate');
            return json_encode($error);
        } // проверить сертификат

        $ogrn = $this->actionloadOgrnByToken($alias,$store);

        if ($ogrn['status'] !== 1){
            $error = $this->getError($ogrn,'actionloadOgrnByToken (ogrn)');
            return json_encode($error);
        } // получает ОГРН
        $actionGetSignedDoc = $this->actionGetSignedDoc($alias,$store,$password,$ogrn['message'],$getCertificate['message']); // Получает какой то сертификат для непонятно чего

        if ($actionGetSignedDoc['status'] !== 1){
            $error = $this->getError($actionGetSignedDoc,'actionGetSignedDoc');
            return json_encode($error);
        } // получает сертификат для чего то

        $getLnNewNum = $this->actionGetLnNewNum($alias,$store,$password,$actionGetSignedDoc['message']); // получить новый номер ЭЛН

        if ($getLnNewNum['status'] !== 1){
            $error = $this->getError($getLnNewNum,'getLnNewNum');
            return json_encode($error);
        } // получает сертификат
        $result = base64_decode($getLnNewNum['message']);
        $result = (int)strstr($result,';',true);
        return json_encode($result);

    } // получить номер ЭЛН

    public function checkPassword($alias,$password,$store){
        $client = new SoapClient($this->wsdl, array("trace"=>1));

        $request = array(
            'alias'=>$alias,
            'password'=>$password,
            'store'=>$store
        );
        $array = json_decode(json_encode($client->checkPassword($request)),1);

        return $array['return'];
    } // Проверить пароль

    public function getCertificate($alias,$store){
        $client = new SoapClient($this->wsdl, array("trace"=>1));
        $request = array(
            'alias'=>$alias,
            'store'=>$store
        );
        $array = json_decode(json_encode($client->getCertificate($request)),1);
        return $array['return'];
    } // Получить сертификат

    public function getError($arr,$method = null){
        $error = array(
            'status' => '',
            'message' => '',
            'method' => '',
        );
        $error['status'] = $arr['status'];
        $error['message'] = $arr['message'];
        $error['method'] = $method;
        return $error;
    } // Отработка ошибков

    public function actionGetSignedDoc($alias,$store,$password,$ogrn,$getCertificate){

        $xml = "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:fss=\"http://fss.vista.ru/\">
   <soapenv:Header/>
   <soapenv:Body>
      <fss:getSignedDoc>
         <!--Optional:-->
         <message>&lt;?xml version=&apos;1.0&apos; encoding=&apos;utf-8&apos;?&gt;
&lt;soapenv:Envelope xmlns:wsse=&quot;http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd&quot; xmlns:wsu=&quot;http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd&quot; xmlns:xsd=&quot;http://www.w3.org/2001/XMLSchema&quot; xmlns:xsi=&quot;http://www.w3.org/2001/XMLSchema-instance&quot; xmlns:ds=&quot;http://www.w3.org/2000/09/xmldsig#&quot; xmlns:soapenv=&quot;http://schemas.xmlsoap.org/soap/envelope/&quot;&gt;
  &lt;soapenv:Header&gt;
    &lt;wsse:Security soapenv:actor=&quot;http://eln.fss.ru/actor/mo/$ogrn&quot;&gt;
      &lt;ds:Signature&gt;
        &lt;SignedInfo&gt;
          &lt;CanonicalizationMethod Algorithm=&quot;http://www.w3.org/2001/10/xml-exc-c14n#&quot;/&gt;
          &lt;SignatureMethod Algorithm=&quot;urn:ietf:params:xml:ns:cpxmlsec:algorithms:gostr34102001-gostr3411&quot;/&gt;
          &lt;Reference URI=&quot;#OGRN_$ogrn&quot;&gt;
            &lt;Transforms&gt;
              &lt;Transform Algorithm=&quot;http://www.w3.org/2001/10/xml-exc-c14n#&quot;/&gt;
            &lt;/Transforms&gt;
            &lt;DigestMethod Algorithm=&quot;urn:ietf:params:xml:ns:cpxmlsec:algorithms:gostr3411&quot;/&gt;
            &lt;DigestValue/&gt;
          &lt;/Reference&gt;
        &lt;/SignedInfo&gt;
        &lt;SignatureValue/&gt;
        &lt;ds:KeyInfo&gt;
          &lt;wsse:SecurityTokenReference&gt;
            &lt;wsse:Reference URI=&quot;#http://eln.fss.ru/actor/mo/$ogrn&quot;/&gt;
          &lt;/wsse:SecurityTokenReference&gt;
        &lt;/ds:KeyInfo&gt;
      &lt;/ds:Signature&gt;
      &lt;wsse:BinarySecurityToken ValueType=&quot;http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3&quot; EncodingType=&quot;http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary&quot; wsu:Id=&quot;http://eln.fss.ru/actor/mo/$ogrn&quot;&gt;$getCertificate
&lt;/wsse:BinarySecurityToken&gt;
    &lt;/wsse:Security&gt;
    &lt;ds:X509Certificate&gt;$getCertificate
&lt;/ds:X509Certificate&gt;
  &lt;/soapenv:Header&gt;
  &lt;soapenv:Body wsu:Id=&quot;OGRN_$ogrn&quot;&gt;
    &lt;getNewLNNumRangeRequest xmlns=&quot;http://www.fss.ru/integration/types/eln/mo/v01&quot;&gt;
      &lt;ogrn&gt;$ogrn&lt;/ogrn&gt;
      &lt;cntLnNumbers&gt;1&lt;/cntLnNumbers&gt;
    &lt;/getNewLNNumRangeRequest&gt;
  &lt;/soapenv:Body&gt;
&lt;/soapenv:Envelope&gt;</message>
         <!--Optional:-->
         <alias>$alias</alias>
         <!--Optional:-->
         <password>$password</password>
         <!--Optional:-->
         <store>$store</store>
         <!--Optional:-->
         <version>3.0</version>
      </fss:getSignedDoc>
   </soapenv:Body>
</soapenv:Envelope>";

        $opts = array(
            'http'=>array(
                'method'=>'POST',
                'header'=>"Content-Type: text/xml\r\n",
                'content'=>$xml
            )
        );

        $context = stream_context_create($opts);
        $array = file_get_contents(strstr($this->wsdl,'?',true), False, $context);



        $p = xml_parser_create();
        xml_parse_into_struct($p, $array, $vals, $index);


        $result = [
            'status' => (int)$vals[4]['value'],
            'message' => $vals[5]['value'],
        ];
        return $result;
////        echo $vals;
//        die();
//
//
//        $client = new SoapClient($this->wsdl, array("trace"=>1));
//        $message = <<<XML
//&lt;?xml version=&apos;1.0&apos; encoding=&apos;utf-8&apos;?&gt;
//&lt;soapenv:Envelope xmlns:wsse=&quot;http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd&quot; xmlns:wsu=&quot;http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd&quot; xmlns:xsd=&quot;http://www.w3.org/2001/XMLSchema&quot; xmlns:xsi=&quot;http://www.w3.org/2001/XMLSchema-instance&quot; xmlns:ds=&quot;http://www.w3.org/2000/09/xmldsig#&quot; xmlns:soapenv=&quot;http://schemas.xmlsoap.org/soap/envelope/&quot;&gt;
//  &lt;soapenv:Header&gt;
//    &lt;wsse:Security soapenv:actor=&quot;http://eln.fss.ru/actor/mo/$ogrn&quot;&gt;
//      &lt;ds:Signature&gt;
//        &lt;SignedInfo&gt;
//          &lt;CanonicalizationMethod Algorithm=&quot;http://www.w3.org/2001/10/xml-exc-c14n#&quot;/&gt;
//          &lt;SignatureMethod Algorithm=&quot;urn:ietf:params:xml:ns:cpxmlsec:algorithms:gostr34102001-gostr3411&quot;/&gt;
//          &lt;Reference URI=&quot;#OGRN_$ogrn&quot;&gt;
//            &lt;Transforms&gt;
//              &lt;Transform Algorithm=&quot;http://www.w3.org/2001/10/xml-exc-c14n#&quot;/&gt;
//            &lt;/Transforms&gt;
//            &lt;DigestMethod Algorithm=&quot;urn:ietf:params:xml:ns:cpxmlsec:algorithms:gostr3411&quot;/&gt;
//            &lt;DigestValue/&gt;
//          &lt;/Reference&gt;
//        &lt;/SignedInfo&gt;
//        &lt;SignatureValue/&gt;
//        &lt;ds:KeyInfo&gt;
//          &lt;wsse:SecurityTokenReference&gt;
//            &lt;wsse:Reference URI=&quot;#http://eln.fss.ru/actor/mo/$ogrn&quot;/&gt;
//          &lt;/wsse:SecurityTokenReference&gt;
//        &lt;/ds:KeyInfo&gt;
//      &lt;/ds:Signature&gt;
//      &lt;wsse:BinarySecurityToken ValueType=&quot;http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3&quot; EncodingType=&quot;http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary&quot; wsu:Id=&quot;http://eln.fss.ru/actor/mo/$ogrn&quot;&gt;$getCertificate
//&lt;/wsse:BinarySecurityToken&gt;
//    &lt;/wsse:Security&gt;
//    &lt;ds:X509Certificate&gt;$getCertificate
//&lt;/ds:X509Certificate&gt;
//  &lt;/soapenv:Header&gt;
//  &lt;soapenv:Body wsu:Id=&quot;OGRN_$ogrn&quot;&gt;
//    &lt;getNewLNNumRangeRequest xmlns=&quot;http://www.fss.ru/integration/types/eln/mo/v01&quot;&gt;
//      &lt;ogrn&gt;$ogrn&lt;/ogrn&gt;
//      &lt;cntLnNumbers&gt;1&lt;/cntLnNumbers&gt;
//    &lt;/getNewLNNumRangeRequest&gt;
//  &lt;/soapenv:Body&gt;
//&lt;/soapenv:Envelope&gt;
//XML;
//
//        $message =
//        $request = array(
//            'message'=>$message,
//            'alias'=>$alias,
//            'password'=>$password,
//            'store'=>$store,
//            'version'=>3.0
//        );

//        $array = json_decode(json_encode($client->getSignedDoc($request)),1);
    } // этот метод дает какой то сертификат для того чтобы получить новый больничный

    public function actionloadOgrnByToken($alias,$store){
        $client = new SoapClient($this->wsdl, array("trace"=>1));
        $request = array(
            'alias'=>$alias,
            'store'=>$store
        );
        $array = json_decode(json_encode($client->loadOgrnByToken($request)),1);
        return $array['return'];
    } // получить ОГРН

    public function actionGetLnNewNum($alias,$store,$password,$podpis_mo){
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<SOAP-ENV:Envelope xmlns:ns0=\"http://fss.vista.ru/\" xmlns:ns1=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:SOAP-ENV=\"http://schemas.xmlsoap.org/soap/envelope/\">
   <SOAP-ENV:Header/>
   <ns1:Body>
      <ns0:getLnNewNum>
         <message>$podpis_mo</message>
         <alias>$alias</alias>
         <password>$password</password>
         <store>$store</store>
         <version>3.0</version>
      </ns0:getLnNewNum>
   </ns1:Body>
</SOAP-ENV:Envelope>";
        $opts = array(
            'http'=>array(
                'method'=>'POST',
                'header'=>"Content-Type: text/xml\r\n",
                'content'=>$xml
            )
        );

        $context = stream_context_create($opts);
        $array = file_get_contents(strstr($this->wsdl,'?',true), False, $context);


        $p = xml_parser_create();
        xml_parse_into_struct($p, $array, $vals, $index);


        $result = [
            'status' => (int)$vals[4]['value'],
            'message' => $vals[5]['value'],
        ];
        return $result;
    } // получить номер больничного

    /**
     * Подписать больничный врачом старт
     * @param integer $aliasstore название контейнера PodpisMo и HDImageStore
     * @param integer $eln номер больничного
     * @param integer $client_id идентификатор клиента
     * @param integer $reason_incapacity_work причина трудоспособности
     * @param array $period массив с периодами
     * @param string $password пароль
     * @param string $mkb код заболевания
     * @param number $eln номер больничного
     *
     * @return string зашифрованная подпись врача
     */
    public function actionGetPodpisDoctor(){
        $eln = (int)Yii::$app->request->post('eln');
        $period = Yii::$app->request->post('period');
        $mkb = Yii::$app->request->post('mkb');
        $reason_incapacity_work = Yii::$app->request->post('reason_incapacity_work');
        $client_id = Yii::$app->request->post('client_id');
        $period = json_decode($period,true);
        $aliasstore = Yii::$app->request->post('alias');
        $aliasstore = explode('(',$aliasstore);
        $aliasstore[1] = strstr($aliasstore[1],')',true);
        $password = (int)Yii::$app->request->post('password');
        $alias = $aliasstore[0];
        $store = $aliasstore[1];

        $checkPassword = $this->checkPassword($alias,$password,$store);  // проверить пароль
        if ($checkPassword['status'] !== 1){
            $error = $this->getError($checkPassword,'checkPassword');
            return json_encode($error);
        } // проверить пароль

        $ogrn = $this->actionloadOgrnByToken($alias,$store);

        if ($ogrn['status'] !== 1){
            $error = $this->getError($ogrn,'actionloadOgrnByToken (ogrn)');
            return json_encode($error);
        } // получает ОГРН

        $lnHash = Yii::$app->getSecurity()->generatePasswordHash(date('Y-m-d H:i:s'));
        $getSignedByDoc = $this->actionGetSignedByDoc($alias,$store,$password,$eln,$period,$ogrn['message'],$mkb,$reason_incapacity_work,$client_id,$lnHash);  // проверить пароль
        if (isset($getSignedByDoc['status']) && (int)$getSignedByDoc['status'] !== 1){

            $error = $this->getError($getSignedByDoc,'actionGetSignedByDoc');
            return json_encode($error);
        } // получает подпись врача

        $session = Yii::$app->session;
        $session->open();
        $session->set('getSignedByDoc', $getSignedByDoc['message']);
        $session->set('ogrn', $ogrn['message']);
        $session->set('lnHash', $lnHash);

        return json_encode($getSignedByDoc);


    } // подписать больничный врачом, первая функция, потом запускается вторая - actionGetSignedByDoc, которая получает непосредственно подпись

    /**
     * Подписать больничный врачом
     * @param string $alias название контейнера первая часть PodpisMo
     * @param string $store название контейнера вторая часть HDImageStore
     * @param string $password пароль
     * @param number $eln номер больничного
     *
     * @return string зашифрованная подпись врача
     */
    public function actionGetSignedByDoc($alias,$store,$password,$eln,$period,$ogrn,$mkb,$reason_incapacity_work,$client_id,$lnHash){

        $count_period = 2;
        $person = Person::findOne(Yii::$app->user->identity->id);
        $rbpost = $person->rbpost->name;
        $treatDoctor = $person->lastName. ' ' .mb_substr($person->firstName, 0, 1). ' ' .mb_substr($person->patrName, 0, 1);
        $treatDoctor = mb_strtoupper($treatDoctor);
//        return $treatDoctor;
        $lpuName = Yii::$app->params['name'];
        $lpuAddress = Yii::$app->params['address'];
        $snils = $person->SNILS;

        $periods = '';
        foreach ($period as $key => $item){
            $count_period++;
            if($key == 0){
                $lnDate = Yii::$app->formatter->asDate($item['date_period_one'],'php:Y-d-m');
            }
            $date_period_one = Yii::$app->formatter->asDate($item['date_period_one'],'php:Y-d-m');
            $date_period_two = Yii::$app->formatter->asDate($item['date_period_two'],'php:Y-d-m');
            $periods .= "          
              &lt;treatFullPeriod&gt;
                &lt;treatPeriod xmlns=&quot;http://www.fss.ru/integration/types/eln/v01&quot; wsu:Id=&quot;ELN_".$eln."_".$count_period."_doc&quot;&gt;
                  &lt;treatDt1&gt;$date_period_one&lt;/treatDt1&gt;
                  &lt;treatDt2&gt;$date_period_two&lt;/treatDt2&gt;
                  &lt;treatDoctorRole&gt;$rbpost&lt;/treatDoctorRole&gt;
                  &lt;treatDoctor&gt;$treatDoctor&lt;/treatDoctor&gt;
                  &lt;idDoctor&gt;$snils&lt;/idDoctor&gt;
                &lt;/treatPeriod&gt;
              &lt;/treatFullPeriod&gt;
              ";
        }

        $client = Client::findOne($client_id);
        $client->lastName = mb_strtoupper($client->lastName);
        $client->firstName = mb_strtoupper($client->firstName);
        $client->patrName = mb_strtoupper($client->patrName);
        $gender = (int)!$client->sex;
//        if (!$client->SNILS){ return $this->getArrayMessage(0,'Не указан СНИЛС пациента!');}
//        return $treatDoctor;
//        return Yii::$app->params['name'];


        $xml = "<SOAP-ENV:Envelope xmlns:ns0=\"http://fss.vista.ru/\" xmlns:ns1=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:SOAP-ENV=\"http://schemas.xmlsoap.org/soap/envelope/\">
   <SOAP-ENV:Header/>
   <ns1:Body>
      <ns0:getSignedDocByDoc>
         <message>&lt;?xml version=&apos;1.0&apos; encoding=&apos;utf-8&apos;?&gt;
&lt;soapenv:Envelope xmlns:wsse=&quot;http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd&quot; xmlns:wsu=&quot;http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd&quot; xmlns:xsd=&quot;http://www.w3.org/2001/XMLSchema&quot; xmlns:xsi=&quot;http://www.w3.org/2001/XMLSchema-instance&quot; xmlns:ds=&quot;http://www.w3.org/2000/09/xmldsig#&quot; xmlns:soapenv=&quot;http://schemas.xmlsoap.org/soap/envelope/&quot; signAlgorithm=&quot;urn:ietf:params:xml:ns:cpxmlsec:algorithms:gostr34102012-gostr34112012-256&quot;&gt;
  &lt;soapenv:Header&gt;
    &lt;wsse:Security soapenv:actor=&quot;http://eln.fss.ru/actor/doc/".$eln."_".$count_period."_doc&quot;&gt;
      &lt;ds:Signature&gt;
        &lt;SignedInfo&gt;
          &lt;CanonicalizationMethod Algorithm=&quot;http://www.w3.org/2001/10/xml-exc-c14n#&quot;/&gt;
          &lt;SignatureMethod Algorithm=&quot;urn:ietf:params:xml:ns:cpxmlsec:algorithms:gostr34102012-gostr34112012-256&quot;/&gt;
          &lt;Reference URI=&quot;#ELN_".$eln."_".$count_period."_doc&quot;&gt;
            &lt;Transforms&gt;
              &lt;Transform Algorithm=&quot;http://www.w3.org/2001/10/xml-exc-c14n#&quot;/&gt;
            &lt;/Transforms&gt;
            &lt;DigestMethod Algorithm=&quot;urn:ietf:params:xml:ns:cpxmlsec:algorithms:gostr34112012-256&quot;/&gt;
            &lt;DigestValue/&gt;
          &lt;/Reference&gt;
        &lt;/SignedInfo&gt;
        &lt;SignatureValue/&gt;
        &lt;ds:KeyInfo&gt;
          &lt;wsse:SecurityTokenReference&gt;
            &lt;wsse:Reference URI=&quot;#http://eln.fss.ru/actor/doc/".$eln."_".$count_period."_doc&quot;/&gt;
          &lt;/wsse:SecurityTokenReference&gt;
        &lt;/ds:KeyInfo&gt;
      &lt;/ds:Signature&gt;
      &lt;wsse:BinarySecurityToken ValueType=&quot;http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3&quot; EncodingType=&quot;http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary&quot; wsu:Id=&quot;http://eln.fss.ru/actor/doc/".$eln."_".$count_period."_doc&quot;&gt;MIIIpzCCCFSgAwIBAgIRAc+w6ABsq42NTEkIzQa1PW8wCgYIKoUDBwEBAwIwggHWMRgwFgYFKoUD
ZAESDTEwOTc3NDYyOTM4ODYxGjAYBggqhQMDgQMBARIMMDA3NzI5NjMzMTMxMQswCQYDVQQGEwJS
VTEbMBkGA1UECAwSNzcg0LMu0JzQvtGB0LrQstCwMRUwEwYDVQQHDAzQnNC+0YHQutCy0LAxbTBr
BgNVBAkMZNGD0LvQuNGG0LAg0JvQtdC90LjQvdGB0LrQuNC1INCT0L7RgNGLLCDQtNC+0LwgMSwg
0YHRgtGA0L7QtdC90LjQtSA3Nywg0LrQvtC80L3QsNGC0LAgMTksINGN0YLQsNC2IDMxMDAuBgNV
BAsMJ9Cj0LTQvtGB0YLQvtCy0LXRgNGP0Y7RidC40Lkg0YbQtdC90YLRgDGBgDB+BgNVBAoMd9Ce
0LHRidC10YHRgtCy0L4g0YEg0L7Qs9GA0LDQvdC40YfQtdC90L3QvtC5INC+0YLQstC10YLRgdGC
0LLQtdC90L3QvtGB0YLRjNGOICLQrdC70LXQutGC0YDQvtC90L3Ri9C5INGN0LrRgdC/0YDQtdGB
0YEiMTkwNwYDVQQDDDDQntCe0J4gItCt0LvQtdC60YLRgNC+0L3QvdGL0Lkg0Y3QutGB0L/RgNC1
0YHRgSIwHhcNMjAwMjI1MTM1ODAwWhcNMjEwMjI1MTQwODAwWjCCAUQxGzAZBgNVBAQMEtCh0LDQ
u9Cw0L3Qs9C40L3QsDE/MD0GA1UEAww20KHQsNC70LDQvdCz0LjQvdCwINCh0LLQtdGC0LvQsNC9
0LAg0JHQvtGA0LjRgdC+0LLQvdCwMRowGAYIKoUDA4EDAQESDDIzMDIxMjQ2MTE2NzEkMCIGA1UE
Bwwb0L/Qs9GCLtCc0L7RgdGC0L7QstGB0LrQvtC5MS8wLQYDVQQIDCYyMyDQmtGA0LDRgdC90L7Q
tNCw0YDRgdC60LjQuSDQutGA0LDQuTELMAkGA1UEBhMCUlUxHjAcBgkqhkiG9w0BCQEWD21vc3Rj
cmJAbWFpbC5ydTEWMBQGBSqFA2QDEgswMjk3ODkzNTgyMjEsMCoGA1UEKgwj0KHQstC10YLQu9Cw
0L3QsCDQkdC+0YDQuNGB0L7QstC90LAwZjAfBggqhQMHAQEBATATBgcqhQMCAiQABggqhQMHAQEC
AgNDAARAJBBjdtXEQZhOcrOZgG9B2yq93KQSXJKKRaJ30c1iuYzJy6uApJkF5LrLoLcBpo3+P61I
nQ4FCxP3OUNnu+C4lKOCBIIwggR+MA4GA1UdDwEB/wQEAwIE8DAwBgNVHSUEKTAnBgcqhQMCAiIG
BggrBgEFBQcDAgYIKwYBBQUHAwQGCCqFAwOBAgMMMB0GA1UdDgQWBBREFV7flfvvExjZOC05/ZUa
fOGw9TCBggYIKwYBBQUHAQEEdjB0MDQGCCsGAQUFBzABhihodHRwOi8vb2NzcC10c3AuZ2FyYW50
LnJ1L29jc3A3L29jc3Auc3JmMDwGCCsGAQUFBzAChjBodHRwOi8vY2EuZ2FyYW50LnJ1L2NhL2Fj
Y3JlZGl0ZWQvZ2FyYW50X2FjOS5jZXIwEwYDVR0gBAwwCjAIBgYqhQNkcQEwKwYDVR0QBCQwIoAP
MjAyMDAyMjUxMzU4MDBagQ8yMDIxMDIyNTEzNTgwMFowggEwBgUqhQNkcASCASUwggEhDCsi0JrR
gNC40L/RgtC+0J/RgNC+IENTUCIgKNCy0LXRgNGB0LjRjyA0LjApDCwi0JrRgNC40L/RgtC+0J/R
gNC+INCj0KYiICjQstC10YDRgdC40LggMi4wKQxf0KHQtdGA0YLQuNGE0LjQutCw0YIg0YHQvtC+
0YLQstC10YLRgdGC0LLQuNGPINCk0KHQkSDQoNC+0YHRgdC40Lgg0KHQpC8xMjQtMzM4MSDQvtGC
IDExLjA1LjIwMTgMY9Ch0LXRgNGC0LjRhNC40LrQsNGCINGB0L7QvtGC0LLQtdGC0YHRgtCy0LjR
jyDQpNCh0JEg0KDQvtGB0YHQuNC4IOKEliDQodCkLzEyOC0zNTkzINC+0YIgMTcuMTAuMjAxODA2
BgUqhQNkbwQtDCsi0JrRgNC40L/RgtC+0J/RgNC+IENTUCIgKNCy0LXRgNGB0LjRjyA0LjApMIGD
BgNVHR8EfDB6MDegNaAzhjFodHRwOi8vY2EuZ2FyYW50LnJ1L2NkcC9hY2NyZWRpdGVkL2dhcmFu
dF9hYzkuY3JsMD+gPaA7hjlodHRwOi8vd3d3LmdhcmFudGV4cHJlc3MucnUvY2RwL2FjY3JlZGl0
ZWQvZ2FyYW50X2FjOS5jcmwwggFgBgNVHSMEggFXMIIBU4AU4onPabLEUDNMMRcrhmGaDa3Jhyeh
ggEspIIBKDCCASQxHjAcBgkqhkiG9w0BCQEWD2RpdEBtaW5zdnlhei5ydTELMAkGA1UEBhMCUlUx
GDAWBgNVBAgMDzc3INCc0L7RgdC60LLQsDEZMBcGA1UEBwwQ0LMuINCc0L7RgdC60LLQsDEuMCwG
A1UECQwl0YPQu9C40YbQsCDQotCy0LXRgNGB0LrQsNGPLCDQtNC+0LwgNzEsMCoGA1UECgwj0JzQ
uNC90LrQvtC80YHQstGP0LfRjCDQoNC+0YHRgdC40LgxGDAWBgUqhQNkARINMTA0NzcwMjAyNjcw
MTEaMBgGCCqFAwOBAwEBEgwwMDc3MTA0NzQzNzUxLDAqBgNVBAMMI9Cc0LjQvdC60L7QvNGB0LLR
j9C30Ywg0KDQvtGB0YHQuNC4ggsAqbJSAAAAAAADBzAKBggqhQMHAQEDAgNBAAr7vBaDkMRKh47/
RVGaf7vnEWHV/oOPMTKYKI+4YxLMuXCE2fF7dHfB5J5lFqKa1dTE+TSX9C1jYb6chcNYLKI=
&lt;/wsse:BinarySecurityToken&gt;
    &lt;/wsse:Security&gt;
    &lt;wsse:Security soapenv:actor=&quot;http://eln.fss.ru/actor/mo/$ogrn/ELN_".$eln."&quot;&gt;
      &lt;ds:Signature&gt;
        &lt;SignedInfo&gt;
          &lt;CanonicalizationMethod Algorithm=&quot;http://www.w3.org/2001/10/xml-exc-c14n#&quot;/&gt;
          &lt;SignatureMethod Algorithm=&quot;urn:ietf:params:xml:ns:cpxmlsec:algorithms:gostr34102012-gostr34112012-256&quot;/&gt;
          &lt;Reference URI=&quot;#ELN_".$eln."&quot;&gt;
            &lt;Transforms&gt;
              &lt;Transform Algorithm=&quot;http://www.w3.org/2001/10/xml-exc-c14n#&quot;/&gt;
            &lt;/Transforms&gt;
            &lt;DigestMethod Algorithm=&quot;urn:ietf:params:xml:ns:cpxmlsec:algorithms:gostr34112012-256&quot;/&gt;
            &lt;DigestValue/&gt;
          &lt;/Reference&gt;
        &lt;/SignedInfo&gt;
        &lt;SignatureValue/&gt;
        &lt;ds:KeyInfo&gt;
          &lt;wsse:SecurityTokenReference&gt;
            &lt;wsse:Reference URI=&quot;#http://eln.fss.ru/actor/mo/$ogrn/ELN_".$eln."&quot;/&gt;
          &lt;/wsse:SecurityTokenReference&gt;
        &lt;/ds:KeyInfo&gt;
      &lt;/ds:Signature&gt;
      &lt;wsse:BinarySecurityToken ValueType=&quot;http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3&quot; EncodingType=&quot;http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary&quot; wsu:Id=&quot;http://eln.fss.ru/actor/mo/$ogrn/ELN_".$eln."&quot;&gt;{certmo}&lt;/wsse:BinarySecurityToken&gt;
    &lt;/wsse:Security&gt;
    &lt;ds:X509Certificate&gt;{certmo}&lt;/ds:X509Certificate&gt;
  &lt;/soapenv:Header&gt;
  &lt;soapenv:Body&gt;
    &lt;prParseFilelnlpuRequest xmlns=&quot;http://www.fss.ru/integration/types/eln/mo/v01&quot;&gt;
      &lt;ogrn&gt;$ogrn&lt;/ogrn&gt;
      &lt;pXmlFile&gt;
        &lt;rowset xmlns:ns0=&quot;http://www.fss.ru/integration/types/eln/v01&quot; ns0:email=&quot;&quot; ns0:author=&quot;&quot; ns0:software=&quot;VistaKK(kul edition)&quot; ns0:version_software=&quot;27463_kul_edition&quot; ns0:phone=&quot;&quot; ns0:version=&quot;2.0&quot;&gt;
          &lt;row wsu:Id=&quot;ELN_".$eln."&quot;&gt;
            &lt;unconditional&gt;false&lt;/unconditional&gt;
            &lt;writtenAgreementFlag&gt;true&lt;/writtenAgreementFlag&gt;
            &lt;lnCode&gt;".$eln."&lt;/lnCode&gt;
            &lt;primaryFlag&gt;1&lt;/primaryFlag&gt;
            &lt;duplicateFlag&gt;0&lt;/duplicateFlag&gt;
            &lt;lpuOgrn&gt;{current_ogrn}&lt;/lpuOgrn&gt;
            &lt;gender&gt;$gender&lt;/gender&gt;
            &lt;reason1&gt;$reason_incapacity_work&lt;/reason1&gt;
            &lt;reason2/&gt;
            &lt;diagnos&gt;$mkb&lt;/diagnos&gt;
            &lt;mseDt1 xsi:nil=&quot;true&quot;/&gt;
            &lt;mseDt2 xsi:nil=&quot;true&quot;/&gt;
            &lt;mseDt3 xsi:nil=&quot;true&quot;/&gt;
            &lt;mseInvalidGroup xsi:nil=&quot;true&quot;/&gt;
           <!-- &lt;date1&gt;2021-03-15&lt;/date1&gt;-->
            &lt;date1 xsi:nil=&quot;true&quot;/&gt;
            &lt;date2 xsi:nil=&quot;true&quot;/&gt;
            &lt;snils&gt;$client->SNILS&lt;/snils&gt;
            &lt;surname&gt;$client->lastName&lt;/surname&gt;
            &lt;name&gt;$client->firstName&lt;/name&gt;
            &lt;patronymic&gt;$client->patrName&lt;/patronymic&gt;
            &lt;lnDate&gt;$lnDate&lt;/lnDate&gt;
            &lt;lpuName&gt;$lpuName&lt;/lpuName&gt;
            &lt;lpuAddress&gt;$lpuAddress&lt;/lpuAddress&gt;
            &lt;birthday&gt;$client->birthDate&lt;/birthday&gt;
            &lt;lnState&gt;010&lt;/lnState&gt;
            &lt;lnHash&gt;$lnHash&lt;/lnHash&gt;
            &lt;intermittentMethodFlag&gt;false&lt;/intermittentMethodFlag&gt;
            &lt;treatPeriods&gt;$periods
            &lt;/treatPeriods&gt;
            &lt;pregn12wFlag&gt;0&lt;/pregn12wFlag&gt;
          &lt;/row&gt;
        &lt;/rowset&gt;
      &lt;/pXmlFile&gt;
    &lt;/prParseFilelnlpuRequest&gt;
  &lt;/soapenv:Body&gt;
&lt;/soapenv:Envelope&gt;
</message>
         <eln>$eln</eln>
         <num>$count_period</num>
         <alias>$alias</alias>
         <password>$password</password>
         <store>$store</store>
         <version>3.0</version>
      </ns0:getSignedDocByDoc>
   </ns1:Body>
</SOAP-ENV:Envelope>";
//        return $xml;
        $opts = array(
            'http'=>array(
                'method'=>'POST',
                'header'=>"Content-Type: text/xml\r\n",
                'content'=>$xml
            )
        );

        $context = stream_context_create($opts);
        $array = file_get_contents(strstr($this->wsdl,'?',true), False, $context);


        $status = $this->get_string_between($array,'<status>','</status>');
        $message = $this->get_string_between($array,'<message>','</message>');

        $result = [
            'status' => $status,
            'message' => $message,
        ];
        return $result;
    } // Подписать врачом

    function get_string_between($string, $start, $end){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    } // распарсить строку для получения данных из xml


    /**
     * Создать массив на запрос
     * @param string $status статус сообщения
     * @param string $store текст сообщения
     *
     * @return string зашифрованная подпись врача
     */
    public function getArrayMessage($status,$message){
        return array(
            'status' => $status,
            'message' => $message,
        );
    } // создать массив на запрос



    public function actionSaveEln(){

        $eln = (int)Yii::$app->request->post('eln');
        $isEln = TempInvalidELN::find()->where(['number' => $eln])->one();
        if (!empty($isEln)){
            return json_encode([
                'status' => 0,
                'message' => 'Больничный с таким номером уже eсть в базе данных!'
            ]);
        }

        $period = Yii::$app->request->post('period');
        $mkb = Yii::$app->request->post('mkb');
        $letswork = Yii::$app->request->post('letswork');
        if (!$letswork == null){
            $letswork = Yii::$app->formatter->asDate($letswork,'php:Y-m-d');
        }
        $reason_incapacity_work = Yii::$app->request->post('reason_incapacity_work');
        $client_id = Yii::$app->request->post('client_id');
        $client = Client::findOne($client_id);
        $period = json_decode($period,true);
        $session = Yii::$app->session;
        $session->open();

        $periods = '';
        $count_period = 0;
        $countDays = 0;
        foreach ($period as $key => $item){
            $count_period++;
            if($key == 0){
                $lnDate = Yii::$app->formatter->asDate($item['date_period_one'],'php:Y-m-d');
            }
            $countDays += (int)$item['countDays'];
            $date_period_one = Yii::$app->formatter->asDate($item['date_period_one'],'php:Y-m-d');
            $date_period_two = Yii::$app->formatter->asDate($item['date_period_two'],'php:Y-m-d');
            $endDate = $date_period_two;
        }

        $templinvalid = new TempInvalidELN();
        $templinvalid->createDatetime =  date("Y-m-d H:i:s"); ;
        $templinvalid->createPerson_id = Yii::$app->user->identity->getId();
        $templinvalid->modifyDatetime =  Yii::$app->formatter->asDate(date('Y-m-d'),'php:Y-m-d');
        $templinvalid->modifyPerson_id = Yii::$app->user->identity->getId();
        $templinvalid->number = $eln;
        $templinvalid->lnDate = Yii::$app->formatter->asDate(date('Y-m-d'),'php:Y-m-d');
        $templinvalid->lpu_name = Yii::$app->params['name'];
        $templinvalid->lpu_address = Yii::$app->params['address'];
        $templinvalid->lpu_ogrn = $session->get('ogrn');
        $templinvalid->caseDate = $lnDate;
        $templinvalid->parent_id = 0;
        $templinvalid->client_id = $client_id;
        $templinvalid->lastName = $client->lastName;
        $templinvalid->firstName = $client->firstName;
        $templinvalid->patrName = $client->patrName;
        $templinvalid->birthDate = $client->birthDate;
        $templinvalid->sex = (int)!$client->sex;
        $templinvalid->SNILS = $client->SNILS;
        $templinvalid->isStationary = 0;
        $templinvalid->reason1_id = $reason_incapacity_work;
        $templinvalid->reason2_id = NULL;
        $templinvalid->letswork = $letswork;
        $templinvalid->diagnos = $mkb;
        $templinvalid->begDate = $lnDate;
        $templinvalid->endDate = $endDate;
        $templinvalid->duration = $countDays;
        $templinvalid->closed = $letswork == null ? 0 : 1;
        $templinvalid->person_id = Yii::$app->user->identity->getId();
        $templinvalid->signedMessage = $session->get('getSignedByDoc');
        $templinvalid->ln_hash = $session->get('lnHash');
        $templinvalid->note = 'Запись сделана черз версию для планшета';
        $templinvalid->save();

        foreach ($period as $key => $item){
            $date_period_one = Yii::$app->formatter->asDate($item['date_period_one'],'php:Y-m-d');
            $date_period_two = Yii::$app->formatter->asDate($item['date_period_two'],'php:Y-m-d');
            $templinvalidperiod = new TempinvalidelnPeriod();
            $templinvalidperiod->master_id = $templinvalid->id;
            $templinvalidperiod->begDate = $date_period_one;
            $templinvalidperiod->endDate = $date_period_two;
            $templinvalidperiod->doctor = Yii::$app->user->identity->getId();
            $templinvalidperiod->state = 1;
            $templinvalidperiod->save();
        }

        return json_encode([
            'status' => 1,
            'message' => 'ЭЛН успешно сохранен!',
        ]);



    } // сохранить (добавить) ЭЛН в БД

}