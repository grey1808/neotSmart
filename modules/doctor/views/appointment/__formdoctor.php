<?php
use yii\helpers\Html;
?>

<h2>Выберите врача</h2>
<?php if (!empty($resoults)):?>

    <?php foreach ($resoults as $item):?>
        <?= Html::a($item['doctor'].'<br> Доступно номерков - '.$item['count_num'] .' <br>'.$item['otdelenie'],
            ['appointment/date-time?idSpeciality='.$item['spec_id'].'&id_doctor='.$item['id_doctor']],
            ['class' => 'btn btn-default btn-lg btn-block','id' => 'set-form-datetime']
        ) ?>
    <?php endforeach;?>
<?php else:?>
    <div class="alert alert-info" role="alert">Нет расписания на вакцинацию COVID-19</div>
<?php endif;?>
