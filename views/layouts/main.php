<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\widgets\Alert;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\DoctorAsst;
use app\assets\ltAppAsset;

DoctorAsst::register($this);
ltAppAsset::register($this);
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <?php \Yii::$app->view->registerLinkTag(['rel' => 'icon', 'type' => 'image/png', 'href' => Url::to(['/web/img/favicon_doctor.png'])]);?> <!-- Иконка-->
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <body>
    <?php $this->beginBody() ?>
    <header>

        <nav class="navbar navbar-default">
            <div class="container-fluid">
                <!-- Brand and toggle get grouped for better mobile display -->
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>

                    <a class="navbar-brand" href="<?=Url::to(['/doctor'])?>">
                        <p>Неотложная<span> помощь</span></p>
                    </a>
                </div>

                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    <ul class="nav navbar-nav">
                        <li>
                            <a href="<?=Url::to(['/doctor/monitor/index'])?>">Поиск пациентов</a>
                        </li>
                        <li>
                            <a href="<?= Url::to(['/doctor/monitor/list']) ?>">Записанные пациенты</a>
                        </li>
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        <li><a href="#"><?= Yii::$app->user->identity->login?></a></li>
                        <li><a href="<?= Url::to(['/site/logout']) ?>">Выйти</a></li>
                </div><!-- /.navbar-collapse -->
            </div><!-- /.container-fluid -->
        </nav>
    </header>



    <div class="container-fluid">
        <content>
            <div class="row">
                <div class="content">

                    <div id="content-top" class="col-md-12">
                        <?= $content; ?>
                    </div>
                </div>



            </div><!--/.row-->
        </content>
        <div id='box_info'></div>
    </div> <!--/.container-fluid-->

    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>