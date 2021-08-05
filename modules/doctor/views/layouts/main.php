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
use app\assets\SsmpAsset;
use app\assets\ltAppAsset;


ltAppAsset::register($this);

$url = Url::to();
if(stristr($url, 'ssmp') == true) {
    SsmpAsset::register($this);
}else{
    DoctorAsst::register($this);
}


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
<!--    <link rel="manifest" href="{% static "@web/js/manifest.json" %}">-->
<!--    <link rel="manifest" href="@web/js/manifest.json">-->
    <link rel="manifest" href="/web/js/manifest.json">

    <?php $this->head() ?>
</head>
<body>

<!-- Прелоадер -->
<div id="p_prldr"><div class="contpre"><span class="svg_anm"></span><br>Подождите<br><small>идет загрузка</small></div></div>

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

                <a class="navbar-brand" href="<?=Url::base(true)?>">
                    <p>Неотложная<span> помощь</span></p>
                </a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
               <ul class="nav navbar-nav">
                    <li>
                        <a href="<?= Url::to(['/list']) ?>">Очередь</a>
                    </li>
                    <li>
                        <a href="<?=Url::to(['/monitor'])?>">Поиск</a>
                    </li>
                    <li>
                        <a href="<?=Url::to(['/ssmp'])?>">ССМП</a>
                    </li>

                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="#"><?= Yii::$app->user->identity->login?></a></li>
                    <li><a href="<?= Url::to(['/site/logout']) ?>">Выйти</a></li>
                </ul>
            </div><!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
</header>
<script>
    // для мобильного приложения, кэширует страницу
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('/web/js/service-worker.js').then(function(registration) {
                // Registration was successful
                console.log('ServiceWorker registration successful with scope: ', registration.scope);
            }, function(err) {
                // registration failed :(
                console.log('ServiceWorker registration failed: ', err);
            }).catch(function(err) {
                console.log(err)
            });
        });
    } else {
        console.log('service worker is not supported');
    }
</script>


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
</div> <!--/.container-fluid-->

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>