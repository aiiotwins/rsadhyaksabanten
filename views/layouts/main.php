<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var string $content */

use app\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\helpers\Html;
use app\assets\AdminAsset;

AdminAsset::register($this);

$this->render('_head');
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" dir="ltr" data-startbar="light" data-bs-theme="light">

<head>    

    <?php $this->head() ?>
    <title><?= Html::encode($this->title) ?></title>

    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">

</head>

<body>
<?php $this->beginBody() ?>
    <?= $this->render('_topbar') ?>
    
    <?= $this->render('_leftbar') ?>
  
    <div class="page-wrapper">
        <!-- Page Content-->
        <div class="page-content">
            
            <?= $content; ?>
            
            <?= $this->render('_rightbar') ?>

            <?= $this->render('_footer') ?>
            
        </div>
        <!-- end page content -->
    </div>
    <!-- end page-wrapper -->

<?php $this->endBody() ?>
</body>
<!--end body-->

</html>
<?php $this->endPage() ?>