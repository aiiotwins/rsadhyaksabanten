<?php

use app\models\MstPegawai;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\bootstrap5\Modal;

/** @var yii\web\View $this */
/** @var app\models\MstPegawaiSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Mst Pegawais';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row justify-content-center">
    <div class="col-md-12 col-lg-12">
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center" >
                <div class="row align-items-center">
                    <div class="col">                      
                        <h4 class="card-title">Data Pegawai</h4>                
                    </div><!--end col-->
                </div>  <!--end row-->   
                <?= Html::button(' <i class="fas fa-user-plus"></i> Tambah User', [
                    'value' => Url::to(['mst-pegawai/create']),
                    'class' => 'btn btn-light btn-sm text-success fw-bold',
                    'id' => 'btn-create-user'
                ]) ?>                              
            </div><!--end card-header-->
            <div class="card-body">
                <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],

                        'kode_karyawan',
                        'nama',
                        'jenis_kelamin',
                        //'tanggal_lahir',
                        //'status_kepegawaian',
                        //'kode_departemen',
                        //'fingerprint_id',
                        //'device_id',
                        //'created_at',
                        //'updated_at',
                        [
                            'attribute' => 'penempatan_departemen',
                            'label' => 'Penempatan / Departemen',
                            'value' => function ($model) {
                                if ($model->penempatanTerbaru && $model->penempatanTerbaru->departemen) {
                                    return $model->penempatanTerbaru->departemen->nama_departemen;
                                }
                                return '-';
                            },
                        ],
                        [
                            'class' => ActionColumn::className(),
                            'urlCreator' => function ($action, MstPegawai $model, $key, $index, $column) {
                                return Url::toRoute([$action, 'id' => $model->id]);
                            }
                        ],
                    ],
                    'tableOptions' => [
                        'class' => 'table table-striped mb-0'
                    ],
                    'pager' => [
                        'options' => ['class' => 'pagination justify-content-end my-3'], // Menggeser ke kanan dan memberi jarak
                        'linkOptions' => ['class' => 'page-link'],
                        'pageCssClass' => 'page-item',
                        'prevPageCssClass' => 'page-item',
                        'nextPageCssClass' => 'page-item',
                        'firstPageCssClass' => 'page-item',
                        'lastPageCssClass' => 'page-item',
                        'activePageCssClass' => 'active',
                        'disabledPageCssClass' => 'disabled',
                        'disabledListItemSubTagOptions' => ['class' => 'page-link'],
                        'prevPageLabel' => '‹',
                        'nextPageLabel' => '›',
                        'firstPageLabel' => '«',
                        'lastPageLabel' => '»',
                    ],
                ]); ?>            
            </div><!--end card-body--> 
        </div><!--end card--> 
    </div> <!--end col-->                                                     
</div>
<?php
$js = <<<JS
// Buka modal dan muat form via Ajax
$('#btn-create-user').on('click', function() {
    var url = $(this).val();
    $('#main-modal-title').text('Tambah Data Pegawai');
    $('#modal-main').modal('show')
        .find('#modal-content-container')
        .html('<div class="text-center p-3">Memuat...</div>')
        .load(url);
});

// Tangani submit form di dalam modal
$(document).on('beforeSubmit', '#user-form', function(e) {
    var \$form = $(this);
    $.ajax({
        url: \$form.attr('action'),
        type: 'post',
        data: \$form.serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#main-modal-title').text('');
                $('#modal-main').modal('hide');
                // Reload container gridview data tanpa refresh halaman
                $.pjax.reload({container: '#user-pjax-container'});
            } else {
                // Tampilkan pesan validasi jika gagal simpan
                console.log(response.errors);
            }
        },
        error: function() {
            alert('Terjadi kesalahan saat memproses data.');
        }
    });
    return false; // Mencegah submit form bawaan browser
});
JS;
$this->registerJs($js);
?>