<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\bootstrap\Modal;
use kartik\date\DatePicker;
?>

<div class="row">
    <div class="col-xs-8">
        <h2>Выписка больничных листов</h2>
    </div>
    <div class="col-xs-4">
        <a class="btn btn-default btn-lg pull-right" href="<?= \yii\helpers\Url::to(Yii::$app->request->referrer) ?>">Назад</a>
    </div>
</div>
<div class="row eln-form">
    <div class="col-md-12">

        <?php $form = ActiveForm::begin(); ?>

        <h2>Элексторонный лист нетрудоспособности</h2>
        <?=$form->field($model, 'elnNum', [
            'inputTemplate' => '<div class="input-group">{input}<span class="input-group-addon formeln-elnnum" data-toggle="modal" data-target=".elnNum"><i class="fa fa-plus-square text-warning" aria-hidden="true"></i></span></div>',
        ]);?>

        <?= $form->field($model,'dateIssue')
            ->label(false)->widget(DatePicker::classname(), [
                'type' => DatePicker::TYPE_COMPONENT_PREPEND,
                'value'  => date('Y-m-d'),
                'pluginOptions' => [
                    'autoclose'=>true,
                    'format' => 'dd.mm.yyyy'
                ]
            ]) ;
        ?>
        <h2>Продолжение</h2>
        <?= $form->field($model, 'continuation_eln')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'continuation_eln_current')->textInput(['maxlength' => true]) ?>

        <h2>Причина</h2>
        <?php
        $params = [
            'prompt' => 'Выберите причину...'
        ];?>
        <?= $form->field($model, 'cause')->dropDownList(
            \yii\helpers\ArrayHelper::map(\app\modules\doctor\models\Rbtempinvalidreason::find()->all(), 'code', 'fullName')
        ) ?>
        <?= $form->field($model, 'dop_cause')->dropDownList(
            \yii\helpers\ArrayHelper::map(\app\modules\doctor\models\Rbtempinvalidextrareason::find()->all(), 'id', 'fullName'),$params
        ) ?>

        <?= $form->field($model, 'diagnosis')->textInput(['maxlength' => true]) ?>


        <div class="period">
            <h2>Период</h2>
<!--            <button class="btn btn-primary" data-toggle="modal" data-target=".elnPeriod"> <i class="fa fa-plus-square-o" aria-hidden="true"></i></button>-->
            <div class="periodAlert"></div>
            <button class="btn btn-primary add"> <i class="fa fa-plus-square-o" aria-hidden="true"></i></button>
            <button class="btn btn-warning edit hidden animate__animated animate__backInRight"> <i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>
            <button class="btn btn-danger remove hidden animate__animated animate__backInRight"> <i class="fa fa-minus-square-o" aria-hidden="true"></i></button>

            <table class="table table-period">
                <tr>
                    <th>Начало</th>
                    <th>Окончание</th>
                    <th>Длительность</th>
                    <th>Врач</th>
                    <th>Состояние</th>
                </tr>
            </table>
        </div>

        <?= $form->field($model,'letswork')
            ->label(false)->widget(DatePicker::classname(), [
                'type' => DatePicker::TYPE_COMPONENT_PREPEND,
                'value'  => date('Y-m-d'),
                'pluginOptions' => [
                    'autoclose'=>true,
                    'format' => 'dd.mm.yyyy'
                ]
            ]) ;
        ?>

        <div class="row">
            <div class="col-xs-6">
                <div class="form-group">
                    <?= Html::submitButton('Подписать', ['class' => 'btn btn-info  btn-block subscribe'/*,'data-toggle'=>'modal','data-target'=>'.elnNum'*/]) ?>
                </div>
            </div>
            <div class="col-xs-6">
                <div class="form-group">
                    <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-block save','disabled'=> true,'id' => 'save']) ?>
                </div>
            </div>
            <div class="col-xs-12">
                <div class="form-group">
                    <?= Html::submitButton('Отправить', ['class' => 'btn btn-success btn-block send','disabled'=> true]) ?>
                </div>
            </div>
        </div>


        <?php ActiveForm::end(); ?>

    </div>

</div>


<!-- Modal получить новый -->
<div class="modal fade elnNum" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h2 class="modal-title" id="myModalLabel">Получить номер больничного</h2>
            </div>
            <div class="modal-body">
                <?php if (!empty($cert)):?>
                    <div class="row">
                        <div class="col-md-12">
                            <h4>Выберите подпись</h4>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <style>
                                .form-horizontal .has-feedback .form-control-feedback {
                                    right: 40px;
                                }
                            </style>
                            <form role="form" class="form-horizontal">
                                <!-- Блок для ввода ключа -->
                                <div class="form-group has-feedback">
                                    <div class="col-md-12">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                                            <select class="form-control" id="alias" required="required" name="alias" >
                                                <?php foreach ($cert as $item):?>
                                                    <option value="<?=$item['alias']?>"><?=$item['name']?></option>
                                                <?php endforeach;?>
                                            </select>
                                        </div>
                                        <span class="glyphicon form-control-feedback"></span>
                                    </div>
                                </div>
                                <!-- Блок для ввода password -->
                                <div class="form-group has-feedback">
                                    <div class="col-md-12">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-key" aria-hidden="true"></i></span>
                                            <input type="password" class="form-control" id="password" required="required" name="password">
                                        </div>
                                        <span class="glyphicon form-control-feedback"></span>
                                    </div>
                                </div>
                                <!-- Конец блока для ввода password-->
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <button class="btn btn-primary gen-eln-num" id="gen-eln-num" type="submit">Получить</button>
                                    </div>
                                </div>

                                <div id="loading">
                                    <div class="cssload-loader">
                                        <div class="cssload-inner cssload-one"></div>
                                        <div class="cssload-inner cssload-two"></div>
                                        <div class="cssload-inner cssload-three"></div>
                                    </div>
                                </div>
                            </form>
                            <div class="alerts"></div>
                        </div>

                    </div>
                <?php else:?>
                    <div class="alert alert-danger" role="alert">
                        <p><strong>К вашей учетной записи не прикреплено ни одной ЭЦП!</strong></p>
                        <p>Для получения ЭЦП обратитесь в отдел АСУ.</p>
                    </div>
                <?php endif;?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal период -->
<div class="modal fade elnPeriod" id="myModalPeriod" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h2 class="modal-title" id="myModalLabel">Добавить период</h2>
            </div>
            <div class="modal-body">
                <?php if (!empty($cert)):?>
                    <div class="row">
                        <div class="col-md-12">
                            <h4>Выберите дату</h4>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <style>
                                .form-horizontal .has-feedback .form-control-feedback {
                                    right: 40px;
                                }
                            </style>
                            <form role="form" class="form-horizontal">
                                <!-- Блок для ввода даты начала -->
                                <div class="form-group has-feedback">
                                    <div class="col-md-12">
                                        <div class="input-group">
                                            <?=
                                            DatePicker::widget([
                                                'id' => 'date_period_one',
                                                'name' => 'date_period_one',
                                                'value' => date('d.m.Y'),
                                                'options' => ['placeholder' => 'Дата начала ...'],
                                                'pluginOptions' => [
                                                    'format' => 'dd.mm.yyyy',
                                                    'todayHighlight' => true
                                                ]
                                            ]);
                                            ?>
                                        </div>
                                        <span class="glyphicon form-control-feedback"></span>
                                    </div>
                                </div>
                                <!-- Блок для ввода даты окончание -->
                                <div class="form-group has-feedback">
                                    <div class="col-md-12">
                                        <div class="input-group">
                                            <?=
                                            DatePicker::widget([
                                                'id' => 'date_period_two',
                                                'name' => 'date_period_two',
                                                'value' => date('d.m.Y', strtotime('+5 days')),
                                                'options' => ['placeholder' => 'Дата окончания ...'],
                                                'pluginOptions' => [
                                                    'format' => 'dd.mm.yyyy',
                                                    'todayHighlight' => true
                                                ]
                                            ]);
                                            ?>
                                        </div>
                                        <span class="glyphicon form-control-feedback"></span>
                                    </div>
                                </div>
                                <!-- Блок кнопок-->
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <button class="btn btn-success btn-block set-eln-period" id="set-eln-num" type="submit">Добавить период</button>
                                        <button class="btn btn-success btn-block edit-eln-period hidden" id="edit-eln-num" type="submit">Сохранить</button>
                                    </div>
                                </div>

                                <div id="loading">
                                    <div class="cssload-loader">
                                        <div class="cssload-inner cssload-one"></div>
                                        <div class="cssload-inner cssload-two"></div>
                                        <div class="cssload-inner cssload-three"></div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php else:?>
                    <div class="alert alert-danger" role="alert">
                        <p><strong>К вашей учетной записи не прикреплено ни одной ЭЦП!</strong></p>
                        <p>Для получения ЭЦП обратитесь в отдел АСУ.</p>
                    </div>
                <?php endif;?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal получить новый -->
<div class="modal fade myModalPodpisDoctor" id="myModalPodpisDoctor" tabindex="-1" role="dialog" aria-labelledby="myModalPodpisDoctor">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h2 class="modal-title" id="myModalLabel">Подписать ЭЛН</h2>
            </div>
            <div class="modal-body">
                <?php if (!empty($cert)):?>
                    <div class="row">
                        <div class="col-md-12">
                            <h4>Выберите подпись</h4>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <style>
                                .form-horizontal .has-feedback .form-control-feedback {
                                    right: 40px;
                                }
                            </style>
                            <form role="form" class="form-horizontal">
                                <!-- Блок для ввода ключа -->
                                <div class="form-group has-feedback">
                                    <div class="col-md-12">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                                            <select class="form-control" id="alias_podpis_doctor" required="required" name="alias" >
                                                <?php foreach ($cert as $item):?>
                                                    <option value="<?=$item['alias']?>"><?=$item['name']?></option>
                                                <?php endforeach;?>
                                            </select>
                                        </div>
                                        <span class="glyphicon form-control-feedback"></span>
                                    </div>
                                </div>
                                <!-- Блок для ввода password -->
                                <div class="form-group has-feedback">
                                    <div class="col-md-12">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-key" aria-hidden="true"></i></span>
                                            <input type="password" class="form-control" id="password_podpis_doctor" required="required" name="password">
                                        </div>
                                        <span class="glyphicon form-control-feedback"></span>
                                    </div>
                                </div>
                                <!-- Конец блока для ввода password-->
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <button class="btn btn-primary gen-eln-num-doctor" id="gen-eln-num-doctor" type="submit">Подписать</button>
                                    </div>
                                </div>

                                <div id="loading">
                                    <div class="cssload-loader">
                                        <div class="cssload-inner cssload-one"></div>
                                        <div class="cssload-inner cssload-two"></div>
                                        <div class="cssload-inner cssload-three"></div>
                                    </div>
                                </div>
                            </form>

                            <div class="alerts"></div>
                        </div>

                    </div>
                <?php else:?>
                    <div class="alert alert-danger" role="alert">
                        <p><strong>К вашей учетной записи не прикреплено ни одной ЭЦП!</strong></p>
                        <p>Для получения ЭЦП обратитесь в отдел АСУ.</p>
                    </div>
                <?php endif;?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>
