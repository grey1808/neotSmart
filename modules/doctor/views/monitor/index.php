<?php

use app\modules\doctor\models\Person;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\bootstrap\ActiveForm;
use app\models\Callspr1;
use app\models\Callspr2;
use app\models\Callspr3;
?>


<div class="col-md-3 ">
    <?php if (isset($search)):?>

        <div class="">
            <h2>ВЫБЕРИТЕ ПАРАМЕТРЫ</h2>
            <?php $form = ActiveForm::begin([

                'method' => 'post',
                'action' => ['monitor/index'],
                'id' => 'search',
                'class' => 'form-horizontal',
            ])?>
            <?= $form->field($search,'ID')
                ->textInput([
                    'placeholder' => 'ID',
                ])->label(false);
            ?>
            <?= $form->field($search,'surname')
                ->textInput([
                    'placeholder' => 'Фамилия',
                ])->label(false);
            ?>
            <?= $form->field($search,'name')
                ->textInput([
                    'placeholder' => 'Имя',
                ])->label(false);
            ?>
            <?= $form->field($search,'patronymic')
                ->textInput([
                    'placeholder' => 'Отчество',
                ])->label(false);
            ?>
            <?= $form->field($search,'birthdate')->widget(\yii\widgets\MaskedInput::className(), [
                'mask' => '99.99.9999',
            ])->textInput(['placeholder' => 'Дата рождения',])->label(false)
            ?>

            <?= $form->field($search,'snils')->widget(\yii\widgets\MaskedInput::className(), [
                'mask' => '999-999-999-99',
            ])->textInput(['placeholder' => 'СНИЛС',])->label(false)
            ?>

            <div class="form-group">

                <?= Html::a('Очистить', ['index'], ['class' => 'btn btn-default']) ?>
                <?= Html::submitButton('Поиск',[
                    'class' => 'btn btn-default',
                    'type' => 'submit',
                ])?>
            </div>

            <?php $form = ActiveForm::end()?>


        </div><!--Конец div content-right-->
    <?php endif;?>
</div>

<div class="col-md-9  content-center monitor">

    <h2 class="animated bounceInRight">НАЙДЕННЫЕ ПАЦИЕНТЫ</h2>
    <div class="found"><!--Найденные пациенты-->
        <?php if (isset($pacients)):?>
        <table class="table_blur_search">
            <tr>
                <th>ФИО</th>
                <th>Дата рождения</th>
                <th>Пол</th>
                <th width="auto">Адрес проживания</th>
                <th>Котактный телефон</th>
            </tr>

            <?php foreach ($pacients as $pacient): ?>

                <tr>
                    <td class="td_display" id="getid"><?=ArrayHelper::getValue($pacient,'id')?></td>
                    <td class="fullname"><?=ArrayHelper::getValue($pacient,'lastName').' '.ArrayHelper::getValue($pacient,'firstName').' '.ArrayHelper::getValue($pacient,'patrName')?></td>
                    <td><?=ArrayHelper::getValue($pacient,'birthDate')?></td>
                    <td><?php
                        if(ArrayHelper::getValue($pacient,'sex') == 1){
                            echo 'M';
                        }else{
                            echo 'Ж';
                        }
                        ArrayHelper::getValue($pacient,'sex')

                        ?></td>
                    <td><?=
                        ArrayHelper::getValue($pacient,'name_type').'. '.
                        ArrayHelper::getValue($pacient,'name_city').' '.
                        ArrayHelper::getValue($pacient,'street_type').'. '.
                        ArrayHelper::getValue($pacient,'street_name').' '.
                        ArrayHelper::getValue($pacient,'number').' '.
                        ArrayHelper::getValue($pacient,'corpus')?></td>
                    <td><?=ArrayHelper::getValue($pacient,'contact')?></td>
                </tr>
            <?php endforeach; ?>


        </table>
        <?php endif;?>
    </div><!--Конец found-->

    <?php if( Yii::$app->session->hasFlash('addEvent') ): ?>
        <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <?php echo Yii::$app->session->getFlash('addEvent'); ?>
        </div>
    <?php endif;?>
    <?php if( Yii::$app->session->hasFlash('addEventError') ): ?>
        <div class="alert alert-danger alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <?php echo Yii::$app->session->getFlash('addEventError'); ?>
        </div>
    <?php endif;?>

    <?php if( Yii::$app->session->hasFlash('ssmp_success') ): ?>
        <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <?php echo Yii::$app->session->getFlash('ssmp_success'); ?>
        </div>
    <?php endif;?>
    <?php if( Yii::$app->session->hasFlash('ssmp_error') ): ?>
        <div class="alert alert-danger alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <?php echo Yii::$app->session->getFlash('ssmp_error'); ?>
        </div>
    <?php endif;?>
    <div class="lead">
        <!-- Кнопка пуска модальное окно новое обращение -->
        <button type="button" class="btn btn-primary btn-lg hidden" data-toggle="modal" data-target="#myModal">
            Новое обращение
        </button>
        <div class="row btn-group-list">
            <div class="col-md-6">
                <?= Html::a('Новое обращение', ['monitor/set-form'], ['class' => 'btn btn-primary btn-lg btn-block','id' => 'setForm']) ?>
            </div>
            <div class="col-md-6">
                <?= Html::a('Запись на вакцинацию', ['appointment/doctor-list'], ['class' => 'btn btn-danger btn-lg btn-block','id' => 'set-form-DoctorList']) ?>
            </div>
            <div class="col-md-6">
                <?= Html::a('Портал врача', [''], ['class' => 'btn btn-info btn-lg btn-block','id' => 'portaldoctor','target' => '_blank']) ?>
                <div id="loading">
                    <div class="cssload-loader">
                        <div class="cssload-inner cssload-one"></div>
                        <div class="cssload-inner cssload-two"></div>
                        <div class="cssload-inner cssload-three"></div>
                    </div>
                </div>
            </div>
            <!--
            <div class="col-md-6">
                <?= Html::a('Больничные листы (в разработке)', ['eln/index'], ['class' => 'btn btn-warning btn-lg btn-block','id' => 'eln']) ?>
            </div>-->
<!--
            <div class="col-md-6">
                <?= Html::a('Больничные листы2 (в разработке)', ['eln/view'], ['class' => 'btn btn-warning btn-lg btn-block','id' => 'elnlist']) ?>
            </div>
            -->
        </div>
    </div>

    <!-- Модальное окно новое обращение -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="myModalLabel">Создание обращения</h4>
                </div>

                <div class="modal-body">
                    <div>
<!--                        --><?php //var_dump(Yii::$app->session->hasFlash('orgstructure_id')); die();?>
                        <?php if( Yii::$app->session->hasFlash('orgstructure_id') ): ?>
                            <div class="alert alert-danger alert-dismissible" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <?php echo Yii::$app->session->getFlash('orgstructure_id'); ?>
                            </div>
                        <?php else:?>
                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs" role="tablist">
                                <li role="presentation" class="active"><a href="#home" aria-controls="home" role="tab" data-toggle="tab">Обращение</a></li>
                                <li role="presentation"><a href="#dnevnic" aria-controls="dnevnic" role="tab" data-toggle="tab">Дневник</a></li>
                            </ul>

                            <?php $form = ActiveForm::begin()?>
                            <!-- Tab panes -->
                            <div class="tab-content">
                                <div role="tabpanel" class="tab-pane active" id="home">
                                    <h2>Создание нового обращения</h2>
                                    <?= $form->field($model,'person_id')->textInput(['maxlength' => true])?>
                                    <?= $form->field($model,'client_id')->textInput(['maxlength' => true])?>
                                    <?= $form->field($model,'setDate')->textInput(['maxlength' => true])->widget(\kartik\datetime\DateTimePicker::class, [
                                        'type' => \kartik\datetime\DateTimePicker::TYPE_COMPONENT_PREPEND,
                                        'pluginOptions' => [
                                            'autoclose'      => true,
                                            'format'         => 'yyyy-mm-dd hh:ii',
                                            'minuteStep'     => 1,
                                            'todayHighlight' => true,
                                            'changeYear'     => true,
                                            'changeMonth'    => true,
                                        ]
                                    ])?>
                                    <?= $form->field($model,'mkb')->textInput(['maxlength' => true])?>
                                    <?= $form->field($model,'orgstructure')->textInput(['maxlength' => true])?>
                                    <?= $form->field($model, 'region')->dropDownList([
                                        '0' => 'Краевой',
                                        '1' => 'Инокраевой',
                                    ]); ?>
                                    <?php
                                    $eventType = ArrayHelper::map($eventType,'id','name');
                                    ?>
                                    <?= $form->field($model,'eventType')->dropDownList($eventType);?>

                                </div>
                                <div role="tabpanel" class="tab-pane" id="dnevnic">
                                    <h2>Дневник</h2>
                                    <?php $dnevnic = \app\modules\doctor\models\ActionType::find()->where(['name'=>'Дневник неотложной помощи','deleted'=>0])->one();?>
                                    <?php $dinamicform = \app\modules\doctor\models\ActionPropertyType::find()->where(['actionType_id'=>$dnevnic->id,'deleted'=>0])->all();?>
                                    <?php $count = 0?>
                                    <?php foreach ($dinamicform as $item):?>
                                        <?php $nameRus = translit($item->name);?>
                                        <?= Html::beginTag('div', ['class' => 'form-group field-dnevnic-cc']) ?>
                                        <?= Html::label("$item->name", "$nameRus", ['class' => 'control-label']) ?>
                                        <?= Html::input("text", "Dnevnic[$count][id]","$item->id", ['id' => "dnevnic-$nameRus-id", 'class' => 'form-control hidden']) ?>
                                        <?php if ($item->typeName == 'Text'):?>
                                            <?= Html::textarea("Dnevnic[$count][content]",'',['id' => "dnevnic-$nameRus-content", 'class' => 'form-control']) ?>
                                        <?php else:;?>
                                            <?= Html::input("text", "Dnevnic[$count][content]",'', ['id' => "dnevnic-$nameRus-content", 'class' => 'form-control']) ?>
                                        <?php endif;?>
                                        <?= Html::endTag('div') ?>
                                        <?php $count ++?>
                                    <?php endforeach;?>
                                </div>
                            </div>

                            <?= Html::submitButton('Сохранить',[
                                'class' => 'btn  btn-success',
                                'type' => 'submit',
                            ])?>
                            <?php $form = ActiveForm::end()?>
                        <?php endif;?>
                    </div>

                </div>




                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>



    <div class="result_table_all"></div>
    <div class="result_table_call"></div>
    <div id="error_found_result"></div>
</div><!--/.col-sm-7 content-center-->


