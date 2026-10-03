<?php

namespace app\modules\admin\modules\kepegawaian;

/**
 * kepegawaian module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\admin\modules\kepegawaian\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
        // $this->modules = [
        //     'attendance' => [
        //         'class' => 'app\modules\admin\modules\kepegawaian\modules\attendance\Module',
        //     ],
        // ];
    }
}
