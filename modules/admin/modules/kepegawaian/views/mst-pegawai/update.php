<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\MstPegawai $model */

$this->title = 'Update Mst Pegawai: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Mst Pegawais', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="mst-pegawai-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
