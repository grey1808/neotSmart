<?php


namespace app\modules\doctor\controllers;


use yii\httpclient\Client;
use SoapClient;
use Yii;
class PortalDoctorController extends AppDoctorController
{
    public $wsdl = 'http://10.0.1.179/EMK/PixService.svc?wsdl';
    public $guid = '1BA2D440-509F-4FDA-8E24-249DE9E32E96';
    public $idLPU = 'bf9c247f-810c-41c9-9d9f-a2a2e1d3ec86';
    public $url_token = 'http://10.0.1.179/acs2/acs/connect/token';

    public function actionGetUrl()
    {
        $client_id = Yii::$app->request->post('client_id');

        $client = new SoapClient($this->wsdl, array("trace"=>1));

        $request = array(
            'guid'=>$this->guid,
            'idLPU'=>$this->idLPU,
            'patient'=>array(
                'IdPatientMIS' => $client_id
//                'IdPatientMIS' => 3647128 // Григорович
//                'IdPatientMIS' => 5026467 // Ассаметс
//                'IdPatientMIS' => 879378 // Губин
//                'IdPatientMIS' => 2315336 // Беляков
            ),
            'idSource' =>'Reg'
        );

        $getPatient = json_decode(json_encode($client->GetPatient($request)),1);
//        debug($getPatient);
//        die();
        if (empty($getPatient['GetPatientResult'])){
            return json_encode(null);
        }

        if (!isset($getPatient['GetPatientResult']['PatientDto']['IdGlobal'])){
            foreach ($getPatient['GetPatientResult']['PatientDto'] as $item){
                $idGlobal = $item['IdGlobal'];
            }
        }else{
            $idGlobal = $getPatient['GetPatientResult']['PatientDto']['IdGlobal'];
        }
//        debug($idGlobal);
//        die();
        $token = $this->getToken($idGlobal);
        $url = "http://10.0.1.91/EMKUI/Patient/$idGlobal/Encounters?access_token=$token";
//        debug($idGlobal);
//        die();
        return json_encode($url);
    } // Генерация URL




    function getToken($idGlobal){

        $lastname = Yii::$app->user->identity->lastName;
        $firstname = Yii::$app->user->identity->firstName;
        $partname = Yii::$app->user->identity->patrName;
        $snils = Yii::$app->user->identity->SNILS;

        $xml =<<<XML
<xacml-samlp:XACMLAuthzDecisionQuery xmlns:saml2p="urn:oasis:names:tc:SAML:2.0:protocol"
                                     xmlns:saml2="urn:oasis:names:tc:SAML:2.0:assertion"
                                     xmlns:xacml-samlp="urn:oasis:names:tc:xacml:3.0:profile:saml2.0:v2:schema:protocol:wd-14"
                                     xmlns:xacml-context="urn:oasis:names:tc:xacml:3.0:core:schema:wd-17"
                                     xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                                     ID="_fc31b400-e529-4ac0-a616-10f1e17c5b8b" Version="2.0"
                                     IssueInstant="2017-04-19T15:54:55.1061156Z"
                                     xsi:schemaLocation=" urn:oasis:names:tc:xacml:3.0:profile:saml2.0:v2:schema:protocol:wd-14 http://login-test.zdrav.netrika.ru:8090/xacml-3.0-profile-saml2.0-v2-schema-protocol-wd-14.xsd n3-healthcare-2018-06-21.xsd">
    <xacml-context:Request xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                           xmlns="urn:oasis:names:tc:xacml:3.0:core:schema:wd-17"
                           xmlns:n3="urn:netrika.ru:healthcare:n3:2018-06-21"
                           xsi:schemaLocation="urn:oasis:names:tc:xacml:3.0:core:schema:wd-17 http://docs.oasis-open.org/xacml/3.0/xacml-core-v3-schema-wd-17.xsd n3-healthcare-2018-06-21.xsd"
                           ReturnPolicyIdList="false" CombinedDecision="false">
        <xacml-context:Attributes Category="urn:oasis:names:tc:xacml:1.0:subject-category:access-subject">
            <xacml-context:Content>
                <n3:Identifier тип="медицинский работник">
                    <n3:System oid="urn:oid:1.2.643.2.69.1.1.1.84">
                        <n3:СНИЛС номер="$snils"/>
                        <n3:ФИО фамилия="$lastname" имя="$firstname" отчество="$partname"/>
                    </n3:System>
                </n3:Identifier>
            </xacml-context:Content>
        </xacml-context:Attributes>
        <xacml-context:Attributes Category="urn:oasis:names:tc:xacml:1.0:subject-category:recipient-subject">
        </xacml-context:Attributes>
        <xacml-context:Attributes Category="urn:oasis:names:tc:xacml:1.0:subject-category:intermediary-subject">
            <xacml-context:Content>
                <n3:Identifier тип="медицинская организация">
                    <n3:System oid="urn:oid:1.2.643.2.69.1.1.1.64">
                        <n3:Организация guid="bf9c247f-810c-41c9-9d9f-a2a2e1d3ec86"/>
                    </n3:System>
                </n3:Identifier>
            </xacml-context:Content>
        </xacml-context:Attributes>
        <xacml-context:Attributes Category="urn:oasis:names:tc:xacml:1.0:subject-category:codebase">
            <xacml-context:Content>
                <n3:Identifier тип="медицинская информационная система">
                    <n3:System oid="urn:oid:1.2.643.2.69.1.2">
                        <n3:ИнформационнаяСистема oid="urn:oid:1.2.643.2.69.1.2.1"/>
                    </n3:System>
                </n3:Identifier>
            </xacml-context:Content>
        </xacml-context:Attributes>
        <xacml-context:Attributes Category="urn:oasis:names:tc:xacml:1.0:subject-category:requesting-machine">
        </xacml-context:Attributes>
        <xacml-context:Attributes Category="urn:oasis:names:tc:xacml:3.0:attribute-category:resource">
            <xacml-context:Content>
                <n3:Identifier тип="пациент">
                    <n3:System oid="urn:oid:1.2.643.2.69.1.1.4">
                        <n3:IdGlobal value="$idGlobal"/>
                    </n3:System>
                </n3:Identifier>
            </xacml-context:Content>
        </xacml-context:Attributes>
        <xacml-context:Attributes Category="urn:oasis:names:tc:xacml:3.0:attribute-category:action">
            <xacml-context:Content>
                <n3:Identifier тип="действие">
                    <n3:System oid="urn:oid:1.2.643.2.69.1.1.4">
                        <n3:Метод имя="читать"/>
                    </n3:System>
                </n3:Identifier>
            </xacml-context:Content>
        </xacml-context:Attributes>
        <xacml-context:Attributes Category="urn:oasis:names:tc:xacml:3.0:attribute-category:environment">
        </xacml-context:Attributes>
    </xacml-context:Request>
</xacml-samlp:XACMLAuthzDecisionQuery>
XML;
        $text = base64_encode($xml);
        $her = urlencode(mb_convert_encoding($text,'UTF-8'));
        $req_template = "grant_type=urn:ietf:params:oauth:client-assertion-type:saml2-bearer&assertion=".$her."&scope=iemk_portal+openid";



        $client = new Client(
            ['baseUrl' => $this->url_token]
        );
        $response = $client->createRequest()
            ->setMethod('POST')
//            ->setUrl('/connect/token')
            ->setFormat(Client::FORMAT_JSON)
            ->addHeaders([
                'content-type' => 'application/x-www-form-urlencoded',
                'accept' => 'application/json',
                'authorization' => 'Basic VmlzdGFNZWQ6VldrWkszMGE='
            ])
            ->setContent($req_template)
            ->send();
        if ($response->isOk) {
            return $response->data['access_token'];
        }else{
            return $response->data['access_token'] = 'I don\'t get token' ;
        }


/*
        //процедурный стиль, тупо php
        $opts = array(
            'http' =>
                array(
                    'method'  => 'POST',
                    'header'  =>
                        "content-Type: application/x-www-form-urlencoded\r\n".
                        "accept: application/json\r\n".
                        "authorization: Basic VmlzdGFNZWQ6VldrWkszMGE=\r\n",
                    'content' => $req_template
                )
        );

        $context  = stream_context_create($opts);
        $result = file_get_contents($this->url_token, false, $context);

        print_r($result);
        die();

*/
    } // посылаем get запрос после успешного получения

}