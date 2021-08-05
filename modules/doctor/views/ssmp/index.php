<?php
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

use app\modules\doctor\models\Smpevents;
use kartik\date\DatePicker;
?>
<?php
//debug($smpevents_form);
?>
<div class="col-md-8 content-center">

    <h2 class="animated bounceInRight">НАЙДЕННЫЕ ВЫЗОВЫ</h2>
    <?php if(!empty($smpevents)):?>
    <div class="found">
        <table class="table_blur_search table_ssmp">
            <tr>
                <th>id</th>
                <th>Номер события</th>
                <th>Номер</th>
                <th>Дата вызова</th>
                <th>ФИО</th>
                <th>Адрес</th>
                <th>Причина вызова</th>
            </tr>
        <?php foreach ($smpevents as $item):?>
            <tr <?php echo ($item['isDone'] == 0 && $item['status'] == 0) ?  "class='bg-danger'" : "class='bg-warning'";  ?>>
                <td><?=$item['id']?></td>
                <td class="eventId"><?=$item['eventId']?></td>
                <td class="callNumberId"><?=$item['callNumberId']?></td>
                <td><?=date("d.m.Y", strtotime($item['callDate']));?></td>
                <td class="fullName"><?=$item['fio']?></td>
                <td><?=$item['address']?></td>
                <td><?=$item['occasion']?></td>
                <td class="hidden status"><?=$item['status']?></td>
            </tr>
        <?php endforeach;?>
        </table>
    </div>
    <?php
    $count = count($smpevents);
    ?>
    <div class="row">
        <div class="col-md-3">
            <p class="alert alert-success" >Найдено <b><?=$count?></b>
                <?php
                if ($count == 1){
                    echo 'запись';
                }else if ($count == 2){
                    echo 'записи';
                }else if ($count == 3){
                    echo 'записи';
                }else if ($count == 4){
                    echo 'записи';
                }else{
                    echo 'записей';
                }

                ?>
            </p>
        </div>
        <div class="col-md-9 ssmpalert">
            <?php if( Yii::$app->session->hasFlash('ssmp_success') ): ?>
                <div class="alert alert-success alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <?php echo Yii::$app->session->getFlash('ssmp_success'); ?>
                </div>
            <?php endif;?>
        </div>
    </div>

    <?php else:?>
        <div class="alert alert-warning alert-dismissable">
            <strong>Нет переданных вызовов!</strong> Скорая еще не передала вызовы в неотложную помощь.
        </div>
    <?php endif;?>

    <div class="">
        <?php if(!empty($smpevents)):?>
            <div class="ssmpevents">
                <!--        форма добавить событие к вызову-->
                <div class="row">
                    <div class="form-group col-md-12">
                        <button type="submit" class="btn btn-purple btn-shadow-top btn-block addEventSsmp"><strong>Принять вызов</strong></button>
                    </div>
                </div>
                <div class="row">

                    <div class="col-md-12">
                        <?= Html::button('Поиск выбранного пациента в базе',
                            ['class' => 'btn btn-primary btn-lg btn-block search-patient',
                            'id' => 'search-client_id',
                            'data-toggle'=>'modal',
                            'data-target'=>'.modalSearch'])
                        ?>
                    </div>

                </div>
                <div class="row">
                    <?php \yii\widgets\Pjax::begin(); ?>
                    <div class="found"><!--Найденные пациенты-->
                        <?php if (isset($pacients)):?>

                            <h2 class="animated bounceInRight">НАЙДЕННЫЕ ПАЦИЕНТЫ</h2>
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
                    <?php \yii\widgets\Pjax::end(); ?>
                </div>
                <div class="form-group">
                    <label>Результат вызова</label>
                    <select class="form-control ssmpresoult">
                        <option></option>
                        <option value="1">Принят звонок от пациента</option>
                        <option value="2">Пациент отказался от вызова</option>
                        <option value="3">Отказ ПНМП</option>
                        <option value="4">Принят звонок от ПНМП</option>
                        <option value="5">Изменился адрес</option>
                        <option value="6">Вызов выполнен</option>
                        <option value="7">Вызов передан на 03</option>
                        <option value="8">Вызов безрезультатный (снят в ПНМП)</option>
                        <option value="9">Назначен ошибчный ПНМП</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Примечание</label>
                    <input class="form-control ssmpnote" placeholder="Заметка к событию вызова">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-bord btn-shadow-bottom btn-block addEventSsmpLocal" title="После добавления события вызов будет закрыт"><strong>Добавить событие к вызову</strong></button>
                </div>
            </div>
            <br>
            <br>
            <br>
        <?php endif;?>

        <div class="result_table_call"></div>
    </div>
    <div id="error"></div>




</div>
<div class="col-md-4">
    <div class="content-right-history">

        <h2>ВЫБЕРИТЕ ПАРАМЕТРЫ</h2>
        <?php
        $form = ActiveForm::begin([
            'id' => 'filter-ssmp',
        ]) ?>
        <?= $form->field($smpevents_form, 'callNumberId') ?>
        <?= $form->field($smpevents_form, 'callDate')->widget(DatePicker::class)?>
        <?= $form->field($smpevents_form, 'fio') ?>
        <?= $form->field($smpevents_form,'occasion')
            ->dropDownList(ArrayHelper::map(Smpevents::find()->asArray()->groupBy('occasion')->all(), 'occasion', function ($element){
                return $element['occasion'];
            }),['prompt'=>''])
        ?>
        <?= $form->field($smpevents_form,'isDone')
            ->dropDownList([
                '' => 'Все',
                '0' => 'Активные',
                '1' => 'Завершенные',
            ])
        ?>


        <div class="form-group">
            <?= Html::submitButton('Поиск', ['class' => 'btn btn-primary']) ?>
            <?= Html::resetInput('Очистить', ['class' => 'btn btn-defoult']) ?>
        </div>
        <?php ActiveForm::end() ?>
    </div>
    <div class="clearfix"></div>


</div><!--/.col-md-7 content-center-->


<!-- Modal -->
<div class="modal fade modalSearch" id="modalSearch" tabindex="-1" role="dialog" aria-labelledby="modalSearch">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Поиск выбранного пациента в базе</h4>
            </div>
            <div class="modal-body">
                <?php if (isset($search)): ?>
                    <?php $form = ActiveForm::begin([

                        'method' => 'post',
                        'action' => ['monitor/index'],
                        'id' => 'search',
                        'class' => 'form-horizontal',
                    ])?>
                    <?= $form->field($search,'surname')
                        ->textInput([
                            'placeholder' => 'Фамилия',
                        ])->label(false)
                    ?>
                    <?= $form->field($search,'name')
                        ->textInput([
                            'placeholder' => 'Имя',
                        ])->label(false)
                    ?>
                    <?= $form->field($search,'patronymic')
                        ->textInput([
                            'placeholder' => 'Отчество',
                        ])->label(false)
                    ?>
                    <?= $form->field($search,'birthdate')->widget(\yii\widgets\MaskedInput::className(), [
                        'mask' => '99.99.9999',
                    ])->textInput(['placeholder' => 'Дата рождения',])->label(false)
                    ?>

                    <?= $form->field($search,'snils')->widget(\yii\widgets\MaskedInput::className(), [
                        'mask' => '999-999-999-99',
                    ])->textInput(['placeholder' => 'СНИЛС',])->label(false)
                    ?>

                    <?= $form->field($search,'callNumberId')
                        ->textInput([
                            'placeholder' => 'Номер вызова ССМП',
                        ])->label(false)
                    ?>

                    <div class="form-group">

                        <?= Html::submitButton('Поиск',[
                            'class' => 'btn btn-primary',
                            'type' => 'submit',
                        ])?>
                    </div>

                    <?php $form = ActiveForm::end()?>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>