<?php
/**
 * Created by PhpStorm.
 * User: Belya
 * Date: 11.03.2019
 * Time: 11:56
 */

namespace app\controllers;
use app\models\Callspr1;
use yii\web\Controller;
use yii\filters\AccessControl;
use app\modules\admin\models\Callconfig;


class AppController extends Controller
{




    public function behaviors()
    {
        return [

            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@']
                    ]
                ]
            ]
        ];
    }
}