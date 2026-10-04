<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */

/** @var app\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Login to your account';
$this->params['breadcrumbs'][] = $this->title;
$this->params['meta_description'] = 'Log in to access your Yii2 application account.';
$this->params['meta_keywords'] = 'yii, yii2, login, sign in, authentication';
$htmlIcon = <<<HTML
{label}<div class="input-group"><span class="input-group-text" aria-hidden="true">%s</span>{input}</div>{error}{hint}
HTML;
$labelOptions = ['class' => 'form-label fw-semibold small'];
?>
<div class="card-body">                                    
    <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

    <div class="mb-3">
        <?= $form->field($model, 'username', [
            'options' => ['class' => 'mb-0'],
            'template' => sprintf($htmlIcon, '&#128100;'),
            'inputOptions' => [
                'class' => 'form-control',
                'placeholder' => 'username',
                'autofocus' => true,
            ],
        ])->textInput()->label('Your Username', $labelOptions) ?>
    </div>

    <div class="mb-3">
        <?= $form->field($model, 'password', [
            'options' => ['class' => 'mb-0'],
            'template' => sprintf($htmlIcon, '&#128274;'),
            'inputOptions' => [
                'class' => 'form-control',
                'placeholder' => 'Password',
            ],
        ])->passwordInput()->label('Your Password', $labelOptions) ?>
    </div>

    <div class="mb-4">
        <?= $form->field($model, 'rememberMe')->checkbox() ?>
    </div>

    <div class="d-grid">
        <?= Html::submitButton(
            'Login',
            [
                'class' => 'btn login-btn btn-lg rounded-3 text-white',
                'name' => 'login-button',
            ],
        ) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div><!--end card-body-->
