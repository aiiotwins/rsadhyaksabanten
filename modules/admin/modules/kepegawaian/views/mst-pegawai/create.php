<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\bootstrap5\Modal;

/** @var yii\web\View $this */
/** @var app\models\MstPegawai $model */

$this->title = 'Tambah Data Pegawai';
$this->params['breadcrumbs'][] = ['label' => 'Mst Pegawais', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="mst-pegawai-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
