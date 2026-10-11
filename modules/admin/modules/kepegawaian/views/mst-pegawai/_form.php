<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\date\DatePicker;

/** @var yii\web\View $this */
/** @var app\models\MstPegawai $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="mst-pegawai-form">

    <?php $form = ActiveForm::begin([
        'id' => 'mst-pegawai-form',
        'enableAjaxValidation' => false,
        'enableClientValidation' => true,
    ]); ?>

    <div class="row">
        <div class="col-6">
            <div class="card">
                <div class="card-body">

                    <?= $form->field($model, 'kode_karyawan')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($model, 'nama')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($model, 'jenis_kelamin')->widget(Select2::classname(), [
                        'data' => [
                            'Pria' => 'Pria',
                            'Wanita' => 'Wanita',
                        ],
                        'options' => ['placeholder' => 'Pilih Jenis Kelamin'],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]); ?>

                </div><!--end card-body--> 
            </div><!--end card--> 
        </div> <!--end col-->  
        <div class="col-6">
            <div class="card">
                <div class="card-body">

                    <?= $form->field($model, 'tanggal_lahir')->widget(DatePicker::classname(), [
                        'options' => ['placeholder' => 'Pilih tanggal...'],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd', // Sesuaikan format penyimpanan database
                            'todayHighlight' => true,
                        ],
                    ]) ?>

                    <?= $form->field($model, 'status_kepegawaian')->widget(Select2::classname(), [
                        'data' => [
                            'CPNS' => 'CPNS',
                            'PPPK' => 'PPPK',
                        ],
                        'options' => ['placeholder' => 'Pilih Jenis Kelamin'],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]); ?>

                    <?= $form->field($model, 'kode_departemen')->textInput() ?>

                </div><!--end card-body--> 
            </div><!--end card--> 
        </div> <!--end col-->                                                   
    </div>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>    

     <?php ActiveForm::end(); ?>
</div>

