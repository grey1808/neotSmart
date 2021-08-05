<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

?>
<?php if( Yii::$app->session->hasFlash('orgstructure_id') ): ?>
    <div class="alert alert-danger alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <?php echo Yii::$app->session->getFlash('orgstructure_id'); ?>
    </div>
<?php else:?>

    <?php $form = ActiveForm::begin([
            'action' => 'list'
    ])?>
    <div class="row">

        <div class="col-xs-8">
            <h2>Создание нового обращения</h2></div>
        <div class="col-xs-4">
            <a class="btn btn-default btn-lg pull-right" href="<?= \yii\helpers\Url::to(Yii::$app->request->referrer) ?>">Назад</a>
        </div>
    </div>
    <?= $form->field($model,'person_id')->label(false)->textInput(['maxlength' => true,'class'=>'hidden'])?>
    <?= $form->field($model,'client_id')->label(false)->textInput(['maxlength' => true,'class'=>'hidden'])?>
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
    ])->label(false)->textInput(['class'=>'hidden'])?>
    <?= $form->field($model,'mkb')->textInput(['maxlength' => true])?>
    <?= $form->field($model,'orgstructure')->textInput(['maxlength' => true,'readonly'=>'readonly'])?>
    <?= $form->field($model, 'region')->dropDownList([
        '0' => 'Краевой',
        '1' => 'Инокраевой'
    ],['class'=>'hidden'])->label(false) ?>
    <?php
    $eventType = ArrayHelper::map($eventType,'id','name');
    ?>
    <?= $form->field($model,'eventType')->dropDownList($eventType,['class' => 'hidden'])->label(false)?>
    <hr>
    <h2 class="text-success">Дневник</h2>
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

    <?= Html::submitButton('Сохранить',[
        'class' => 'btn  btn-success',
        'type' => 'submit',
    ])?>

    <?php $form = ActiveForm::end()?>
<?php endif;?>
<br>
<br>

