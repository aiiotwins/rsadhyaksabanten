<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Upload Log Absensi Fingerprint';
?>

<div class="absensi-import">
    <h4><?= Html::encode($this->title) ?></h4>

    <div class="card p-4 col-md-6">
        <!-- Form wajib menggunakan enctype multipart/form-data -->
        <form method="post" enctype="multipart/form-data">
            <!-- CSRF Token Yii2 -->
            <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

            <div class="mb-3">
                <label class="form-label">Pilih Berkas (.txt atau .dat)</label>
                <!-- Nama input harus sama dengan UploadedFile::getInstanceByName('file_log') -->
                <input type="file" name="file_log" class="form-control" accept=".txt,.dat" required>
                <div class="form-text">Contoh file: GD E.txt, GD F 2.dat, atau 1_attlog.dat</div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="glyphicon glyphicon-upload"></i> Proses & Simpan ke Database
            </button>
            <a href="<?= \yii\helpers\Url::to(['index']) ?>" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>