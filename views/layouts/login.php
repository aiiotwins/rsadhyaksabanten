<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var string $content */

use app\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use app\assets\LoginAsset;

LoginAsset::register($this);

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

    
    <!-- Top Bar Start -->
    <body>
    <?php $this->beginBody() ?>
        <div class="container-xxl">
            <div class="row vh-100 d-flex justify-content-center">
                <div class="col-12 align-self-center">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-4 mx-auto">
                                <div class="card">
                                    <div class="card-body p-0 bg-black auth-header-box rounded-top">
                                        <div class="text-center p-3">
                                            <a href="index.html" class="logo logo-admin">
                                                <img src="assets/images/logo-sm.png" height="50" alt="logo" class="auth-logo">
                                            </a>
                                            <h4 class="mt-3 mb-1 fw-semibold text-white fs-18">Let's Get Started Dastone</h4>   
                                            <p class="text-muted fw-medium mb-0">Sign in to continue to Dastone.</p>  
                                        </div>
                                    </div>
                                    <?= $content ?>
                                    
                                </div><!--end card-->
                            </div><!--end col-->
                        </div><!--end row-->
                    </div><!--end card-body-->
                </div><!--end col-->
            </div><!--end row-->                                        
        </div><!-- container -->
    <?php $this->endBody() ?>
    </body>
    <!--end body-->
</html>
<?php $this->endPage() ?>