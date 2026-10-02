<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\modules\attendance\models\UploadLogForm */

$this->title = 'Upload Log Absensi Fingerprint';
$this->params['breadcrumbs'][] = ['label' => 'Attendance', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
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
        <!-- <div id="alertBox" class="alert d-none" role="alert"></div> -->
        <div id="notifikasiSubmit" class="col-md-4" style="display:none;">
            <span class="badge text-bg-primary"><strong>Data Berhasil Diupload !</strong></span>
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
<?php
$js = <<<JS

var alertTimer = null;
function showAlert(message, type = 'success') {
    //var \$alert = $('#alertBox');

    // 1. Bersihkan timer lama jika user memicu alert sebelum 10 detik selesai
    if (alertTimer) {
        clearTimeout(alertTimer);
    }

    // 2. Set pesan dan tipe warna (success / danger)
    //\$alert.find('.alert-message').text(message);
    //\$alert.removeClass('alert-success alert-danger').addClass('alert-' + type);

    // 3. Tampilkan dengan efek fadeIn
    //\$alert.stop(true, true).fadeIn(400);

    // 4. Set waktu otomatis hilang setelah 10 detik (10.000 ms)
    alertTimer = setTimeout(function () {
        //\$alert.fadeOut(500); // Menghilang perlahan selama 0.5 detik
    }, 10000);
}

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
                showAlert(response.message, 'success');
                //alert(response.message || 'File berhasil diunggah!');
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
            $('#notifikasiSubmit').fadeOut(3000);
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