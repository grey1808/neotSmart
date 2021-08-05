<?php


namespace app\modules\doctor\controllers;


use app\controllers\AppController;

class AppDoctorController extends AppController
{
    public function actionCalDate($date){
        $t = $date;
        $d = date("Y", strtotime($t)).'-'.date("n", strtotime($t)).'-'.date("d", strtotime($t));
        $days = floor((strtotime(date("Y-m-d")) - strtotime($d)) / 86400);

        $firstDate = date("Y-m-d");
        $secondDate = $d;

        $firstDateTimeObject = \DateTime::createFromFormat('Y-m-d', $firstDate);
        $secondDateTimeObject = \DateTime::createFromFormat('Y-m-d', $secondDate);

        $delta = $secondDateTimeObject->diff($firstDateTimeObject);
        $result = array();
        $result['years'] = $delta->format('%y');
        $result['mounts'] = $delta->format('%m');
        $result['days'] = $delta->format('%d');
        return $result;
    }

    static public function getTranslit($str) {
        $rus = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я', ' ');
        $lat = array('a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya', '_');
        return str_replace($rus, $lat, $str);
    }
}