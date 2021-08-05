<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Вход в программу';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">
            <?= Html::encode($this->title) ?></h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-sm-12 login-box">


        <?php $form = ActiveForm::begin([
            'id' => 'login-form',
            'layout' => 'horizontal',
            'fieldConfig' => [
                'template' => "{label}\n<div class=\"col-lg-10\">{input}</div>\n<div class='col-md-2'></div><div class=\"col-lg-10\">{error}</div>",
                'labelOptions' => ['class' => 'col-lg-2 control-label'],
            ],
        ]); ?>

        <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>

        <?= $form->field($model, 'password')->passwordInput() ?>
            </div>
        </div>


    </div>


    <div class="panel-footer">
        <div class="row">
            <div class="col-sm-12">
                <?= Html::submitButton(Html::tag('span', Html::tag('i', '', ['class' => 'glyphicon glyphicon-ok']), ['class' => 'btn-label']) .' Войти', ['class' => 'btn btn-labeled btn-success right']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>
