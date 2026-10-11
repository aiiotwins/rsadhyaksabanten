<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Laporan Rekapitulasi Absensi';
?>

<div class="absensi-index">
    <h3><?= Html::encode($this->title) ?></h3>

    <div class="card mb-3 p-3">
        <form method="get" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Tanggal Mulai</label>
                <input type="date" name="start_date" class="form-control" value="<?= Html::encode($startDate) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal Selesai</label>
                <input type="date" name="end_date" class="form-control" value="<?= Html::encode($endDate) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">PIN Pegawai (Opsional)</label>
                <input type="text" name="pin" class="form-control" placeholder="Contoh: 45" value="<?= Html::encode($pin) ?>">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Filter</button>
                <a href="<?= Url::to(['export-excel', 'start_date' => $startDate, 'end_date' => $endDate, 'pin' => $pin]) ?>" class="btn btn-success">
                    <i class="glyphicon glyphicon-download-alt"></i> Export Excel
                </a>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>PIN</th>
                    <th>Nama</th>
                    <th>Tanggal</th>
                    <th>Jam Masuk</th>
                    <th>Jam Pulang</th>
                    <th>Terlambat</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($dataRekap)): ?>
                    <tr><td colspan="8" class="text-center">Tidak ada data untuk periode ini.</td></tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($dataRekap as $row): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= Html::encode($row['pin']) ?></td>
                            <td><?= Html::encode($row['nama']) ?></td>
                            <td><?= Html::encode($row['tanggal']) ?></td>
                            <td><strong><?= Html::encode($row['jam_masuk']) ?></strong></td>
                            <td><strong><?= Html::encode($row['jam_pulang']) ?></strong></td>
                            <td class="<?= $row['terlambat'] > 0 ? 'text-danger font-weight-bold' : '' ?>">
                                <?= $row['terlambat'] > 0 ? $row['terlambat'] . ' menit' : '-' ?>
                            </td>
                            <td>
                                <span class="badge <?= $row['status'] === 'Terlambat' ? 'bg-danger' : 'bg-success' ?>">
                                    <?= Html::encode($row['status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>