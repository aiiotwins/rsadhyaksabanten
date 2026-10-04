<?php

use yii\helpers\Url;
use yii\helpers\Html;

$this->title = 'Manajemen User';
$this->params['breadcrumbs'][] = $this->title;

// Asset DataTables
$this->registerCssFile('https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css', ['depends' => [\yii\web\JqueryAsset::class]]);
$this->registerJsFile('https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);
$this->registerJsFile('https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);
?>

<div class="user-index">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0"><?= Html::encode($this->title) ?></h5>
            <?= Html::a('Tambah User', ['create'], ['class' => 'btn btn-success btn-sm']) ?>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="table-user" class="table table-bordered table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th style="width: 120px;">Status</th>
                            <th>Dibuat Pada</th>
                            <th style="width: 130px;">Aksi</th>
                        </tr>
                        <!-- Baris Input Filter per Kolom -->
                        <tr class="filter-row">
                            <th></th>
                            <th><input type="text" class="form-control form-control-sm column-search" placeholder="Cari user..." data-col="1"></th>
                            <th><input type="text" class="form-control form-control-sm column-search" placeholder="Cari email..." data-col="2"></th>
                            <th>
                                <select class="form-control form-control-sm column-search" data-col="3">
                                    <option value="">Semua</option>
                                    <option value="10">Aktif</option>
                                    <option value="9">Nonaktif</option>
                                </select>
                            </th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$ajaxUrl   = Url::to(['get-data']);
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;

$js = <<<JS
$(document).ready(function() {
    var table = $('#table-user').DataTable({
        processing: true,
        serverSide: true,
        orderCellsTop: true, // Memastikan urutan sorting tetap berada di baris judul teratas
        order: [[1, 'asc']],
        ajax: {
            url: '{$ajaxUrl}',
            type: 'POST',
            data: function(d) {
                d.{$csrfParam} = '{$csrfToken}';
            }
        },
        columns: [
            { data: 'no', orderable: false, searchable: false, className: 'text-center' },
            { data: 'username' },
            { data: 'email' },
            { data: 'status', className: 'text-center' },
            { data: 'created_at', className: 'text-center' },
            { data: 'action', orderable: false, searchable: false, className: 'text-center' }
        ]
    });

    // Mencegah sorting terpicu saat mengklik input filter
    $('.filter-row input, .filter-row select').on('click', function(e) {
        e.stopPropagation();
    });

    // Event filter text input (Username, Email) dengan debounce/delay agar tidak spam request
    var searchTimer;
    $('.filter-row input.column-search').on('keyup change', function() {
        var input = this;
        var colIndex = $(input).data('col');
        
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
            table.column(colIndex).search($(input).val()).draw();
        }, 400); // delay 400ms setelah selesai mengetik
    });

    // Event filter dropdown (Status)
    $('.filter-row select.column-search').on('change', function() {
        var colIndex = $(this).data('col');
        table.column(colIndex).search($(this).val()).draw();
    });
});
JS;
$this->registerJs($js);
?>