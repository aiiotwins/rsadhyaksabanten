<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\modules\attendance\models\UploadLogForm */

$this->title = 'Upload Log Absensi Fingerprint';
$this->params['breadcrumbs'][] = ['label' => 'Admin'];
$this->params['breadcrumbs'][] = ['label' => 'Kepegawaian'];
$this->params['breadcrumbs'][] = ['label' => 'Attendance'];
$this->params['breadcrumbs'][] = $this->title;

// Daftar bulan
$months = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
    '04' => 'April',   '05' => 'Mei',      '06' => 'Juni',
    '07' => 'Juli',    '08' => 'Agustus',  '09' => 'September',
    '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
];

// Daftar tahun (misal: 3 tahun ke belakang sampai tahun sekarang)
$currentYear = (int)date('Y');
$years = range($currentYear, $currentYear - 3);
$years = array_combine($years, $years);

$currentMonth = date('m');

?>

<div class="attendance-log-upload card">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center" >
        <h4 class="card-title mb-0"><?= Html::encode($this->title) ?></h4>

        <?= \yii\helpers\Html::a('<i class="fas fa-file-excel"></i> Download Report Excel', [
            'export-excel',
            'year' => date('Y'),
            'month' => date('n')
        ], [
            'class' => 'btn btn-light btn-sm text-success fw-bold',
            'data-bs-toggle' => 'modal',
            'data-bs-target' => '#modalDownloadExcel',
            'target' => '_blank'
        ]) ?>

    </div>
    <div class="card-body">
        <div class="alert alert-info" role="alert">
            <strong>Catatan:</strong> 
            Upload file ekstensi <code>.dat</code> atau <code>.txt</code> dari mesin absensi. Duplikasi scan dengan PIN dan detik yang sama akan otomatis disaring.
        </div>
        

        <?php $form = ActiveForm::begin([
            'id' => 'uploadForm',
            'action' => ['upload'],
            'options' => ['enctype' => 'multipart/form-data'],
            'enableClientValidation' => true,
            'enableAjaxValidation' => false,
        ]); ?>

        <!-- CSRF Token Yii2 -->
        <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->getCsrfToken() ?>">

        <!-- Progress Bar Container (Awalnya tersembunyi dengan style d-none) -->
        <div id="progress-wrapper" class="mb-3 d-none">
            <div class="progress" style="height: 22px;">
                <div 
                    id="progress-bar" 
                    class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                    role="progressbar" 
                    style="width: 0%;" 
                    aria-valuenow="0" 
                    aria-valuemin="0" 
                    aria-valuemax="100">0%</div>
            </div>
        </div>

        <!-- Area Notifikasi Pesan -->
        <div id="notifikasiSubmit" class="col-md-4" style="display:none;">
            <span class="badge text-bg-primary"><strong id="status_pesan"></strong></span>
        </div>  
        
        <div class="row">
            <!-- <div class="col-md-4">
                <?php //$form->field($model, 'deviceId')->textInput([
                    //'type' => 'number',
                    //'placeholder' => 'ID Mesin (Opsional)'
                //]) ?>
            </div> -->
            <div class="col-md-8">
                <?= $form->field($model, 'logFile')->fileInput([
                    'class' => 'form-control',
                    'accept' => '.dat,.txt'
                ]) ?>
            </div>
            <div class="col-md-4">
                <br>
                <?= Html::submitButton('Unggah & Ekstrak Data', [
                    'class' => 'btn btn-success',
                    'id' => 'btn-submit',
                    'data' => [
                        'confirm' => 'Mulai proses ekstraksi log fingerprint ke database?'
                    ]
                ]) ?>
            </div>               
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<div class="modal fade" id="modalDownloadExcel" tabindex="-1" aria-labelledby="modalDownloadExcelLabel" aria-hidden="true">
    <div class="modal-dialog"> <!-- Hapus modal-sm agar tidak terlalu sempit -->
        <div class="modal-content">
            <?= \yii\helpers\Html::beginForm(['export-excel'], 'get', ['target' => '_blank']) ?>
            
            <div class="modal-header">
                <h5 class="modal-title" id="modalDownloadExcelLabel">Download Report Excel</h5>
                <!-- Di Bootstrap 5 gunakan class btn-close dan taruh setelah judul -->
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <div class="mb-3">
                    <label for="year" class="form-label">Tahun</label>
                    <?= \yii\helpers\Html::dropDownList('year', $currentYear, $years, [
                        'class' => 'form-select',
                        'id' => 'year',
                        'required' => true
                    ]) ?>
                </div>

                <div class="mb-3">
                    <label for="month" class="form-label">Bulan</label>
                    <?= \yii\helpers\Html::dropDownList('month', $currentMonth, $months, [
                        'class' => 'form-select',
                        'id' => 'month',
                        'required' => true
                    ]) ?>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <?= \yii\helpers\Html::submitButton('Download', ['class' => 'btn btn-success', 'data-bs-dismiss' => 'modal']) ?>
            </div>
            
            <?= \yii\helpers\Html::endForm() ?>
        </div>
    </div>
</div>

<?php
$js = <<<JS

var alertTimer = null;

// Event tombol silang (x) untuk menutup manual sebelum 10 detik
$(document).on('click', '.alert-close', function () {
    if (alertTimer) clearTimeout(alertTimer);
    //$('#alertBox').fadeOut(300);
});

$('#uploadForm').on('beforeSubmit', function (e) {
    e.preventDefault();

    var \$form = $(this);
    var formData = new FormData(this);

    var \$btnSubmit = $('#btn-submit');
    var \$progressWrapper = $('#progress-wrapper');
    var \$progressBar = $('#progress-bar');

    // 1. Reset dan tampilkan progress bar
    \$progressWrapper.removeClass('d-none');
    \$progressBar.css('width', '0%').text('0%').attr('aria-valuenow', 0);
    \$btnSubmit.prop('disabled', true).text('Mengunggah...');

    // 2. Kirim via AJAX dengan custom XHR
    $.ajax({
        url: \$form.attr('action'),
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,

        // KUNCI: Modifikasi XHR untuk membaca event progress
        xhr: function () {
            var xhr = new window.XMLHttpRequest();
            
            xhr.upload.addEventListener('progress', function (evt) {
                if (evt.lengthComputable) {
                    var percentComplete = Math.round((evt.loaded / evt.total) * 100);
                    
                    // Update tampilan progress bar
                    \$progressBar.css('width', percentComplete + '%');
                    \$progressBar.text('Memproses Data');
                    \$progressBar.attr('aria-valuenow', percentComplete);
                }
            }, false);

            return xhr;
        },

        success: function (response) {
            // Ketika server merespons sukses
            if (response.success) {
                $('#status_pesan').text(response.message);
                \$form[0].reset(); // Reset form/input file
            } else {
                alert('Gagal: ' + response.message);
            }
        },

        error: function (xhr, status, error) {
            alert('Terjadi kesalahan saat upload: ' + error);
        },

        complete: function () {
            // Aktifkan kembali tombol setelah proses selesai (baik berhasil maupun gagal)
            \$btnSubmit.prop('disabled', false).text('Unggah Dan Ekstrak Data');
            
            $('#notifikasiSubmit').css({ display: "block" })
            $('#notifikasiSubmit').fadeOut(15000);
            // Opsional: Sembunyikan kembali progress bar setelah 2 detik
            setTimeout(function() {
                \$progressWrapper.addClass('d-none');
            }, 2000);
        }
    });

    // Wajib: return false agar Yii2 ActiveForm tidak melakukan reload halaman
    return false;
});
JS;
$this->registerJs($js);
?>