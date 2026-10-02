<?php

namespace app\modules\admin\modules\kepegawaian\modules\attendance;

/**
 * attendance module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\admin\modules\kepegawaian\modules\attendance\controllers';

    /**
     * Default route
     */
    public $defaultRoute = 'log/upload';
    
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
}
