<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\MstPegawai $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Mst Pegawais', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="mst-pegawai-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'kode_karyawan',
            'nama',
            'jenis_kelamin',
            'tanggal_lahir',
            'status_kepegawaian',
            'kode_departemen',
            'fingerprint_id',
            'device_id',
            'created_at',
            'updated_at',
        ],
    ]) ?>

</div>
