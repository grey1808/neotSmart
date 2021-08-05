<?php
use yii\helpers\Html;
?>

    <h2>Выберите время врача</h2>

<?php
$res = array();
if(!empty($resoults)){
    $res = '<ul>';
    foreach ($resoults as $resoult){
        $res .= '<li class="col-md-2 GetAvaibleAppointmentsDateLocal" value="'
            .$resoult['EventId'].'">
                    <i class="hidden orgstructureThis">'.$resoult['orgstructure'].'</i>
                    <i class="hidden ActionPropertyThis">'.$resoult['ActionProperty'].'</i>
                    <i class="hidden apqaIndexThis">'.$resoult['apqaIndex'].'</i>
                    <i class="hidden curdateThis">'.date("Y-m-d H:i:s").'</i>
                    <i class="hidden personidThis">'.\Yii::$app->user->identity->id.'</i>
                    <i class="hidden cabinetThis">'.$resoult['cabinet'].'</i>
                    <i class="hidden setDateThis">'.$resoult['setDate'].' '.$resoult['time_priem'].'</i>
                    <span>'.$resoult['date_priem'] .' '.$resoult['time_priem'].'</span></li>';
    }
    $res .= '</ul>';
}else{
    $res = false;
}
?>
<?php if (!empty($resoults)):?>

    <?php foreach ($resoults as $item):?>
        <?= Html::a($item['date_priem'].' '.$item['time_priem'],
            ['appointment/set-appointment-local-covid'.
                '?orgstructure_id='.$item['orgstructure'].
                '&actionProperty_id='.$item['ActionProperty'].
                '&apqaIndex='.$item['apqaIndex'].
                '&cabinet='.$item['cabinet'].
                '&doctor_id='.$item['id_doctor'].
                '&setDate='.$item['setDate'].' '.$item['time_priem']
            ],
            ['class' => 'btn btn-default btn-lg',
                'id' => 'set-appointment-local-covid',
                'style' => 'margin-bottom: 10px',
            ]
        ) ?>
    <!--
        <li class="col-md-2 GetAvaibleAppointmentsDateLocal btn btn-default" style="margin-bottom: 10px" value="'
            <?=$item['EventId']?>">
            <i class="hidden orgstructureThis"><?=$item['orgstructure']?></i>
            <i class="hidden ActionPropertyThis"><?=$item['ActionProperty']?></i>
            <i class="hidden apqaIndexThis"><?=$item['apqaIndex']?></i>
            <i class="hidden curdateThis"><?=date("Y-m-d H:i:s")?></i>
            <i class="hidden personidThis"><?=\Yii::$app->user->identity->id?></i>
            <i class="hidden cabinetThis"><?=$item['cabinet']?></i>
            <i class="hidden setDateThis"><?=$item['setDate'].' ' . $item['time_priem']?></i>
            ></li>-->
    <?php endforeach;?>
<?php else:?>
    <div class="alert alert-info" role="alert">Нет расписания на врача COVID-19</div>
<?php endif;?>