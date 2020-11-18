<?php
use yii\helpers\Html;
use \wpjCode\wii\Generator;

/* @var $this \yii\web\View */
/* @var $generators wpjCode\wii\Generator[] */
/* @var $CrudModel wpjCode\wii\generators\crud\Generator */
/* @var $content string */

$generators = Yii::$app->controller->module->generators;
?>
<div class="default-index">
    <h1 class="border-bottom pb-3 mb-3">
        <?=Yii::t(I18nCategory, 'Welcome to Wii');?>
        <small class="text-muted">
            <?=Yii::t(I18nCategory, 'Welcome to Wii Hint');?>
        </small>
    </h1>

    <p class="lead mb-5">
        <?=Yii::t(I18nCategory, 'Start enjoy')?>
    </p>

    <div class="row">
        <?php foreach ($generators as $id => $generator): ?>
        <div class="generator col-lg-4">
            <h3><?= Html::encode($generator->getName()) ?></h3>
            <p><?= $generator->getDescription() ?></p>
            <p><?= Html::a(Yii::t(I18nCategory, 'Start Button Text') . ' &raquo;', [
                    'default/view',
                    'id' => $id
                ], [
                        'class' => [
                                'btn',
                            'btn-outline-secondary'
                        ]
                ]) ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <p><a class="btn btn-success" href="http://www.yiiframework.com/extensions/?tag=gii">Get More Generators</a></p>

</div>
