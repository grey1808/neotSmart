<?php
use yii\helpers\Html;
?>
<?php if(isset($client_id)):?>
<div class="row">
    <div class="col-md-6">
        <?= Html::a('Создать ЭЛН', ['eln/index', 'client_id' => $client_id], ['class' => 'btn btn-warning btn-lg btn-block','id' => 'new-eln']) ?>
    </div>
    <br>
    <div class="col-md-6">
        <?= Html::a('Анулировать', ['eln/сancel ', 'client_id' => $client_id], ['class' => 'btn btn-danger btn-lg btn-block','id' => 'cancel-eln']) ?>
    </div>
    <!--
    <div class="col-md-6">
        <?= Html::a('Получить ЭЛН', ['eln/get-eln ', 'client_id' => $client_id], ['class' => 'btn btn-danger btn-lg btn-block','id' => 'get-eln']) ?>
    </div>
    -->
</div>
<?php endif;?>