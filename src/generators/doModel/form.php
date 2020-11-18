<?php

use wpjCode\wii\generators\model\Generator;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $generator wpjCode\wii\generators\doModel\Generator */

echo $form->field($generator, 'expName', []);

echo $form->field($generator, 'baseModelClass', [
    'hasFileSvg' => true,
    'fileSvgTitle' => $generator->langString('chose file/folder')
]);

echo $form->field($generator, 'nameSpace', [
    'hasFileSvg' => true,
    'fileSvgTitle' => $generator->langString('chose file/folder'),
    'syncFileSvg' => true,
    'syncFileTitle' => $generator->langString('Sync model class base name')
]);

echo $form->field($generator, 'isCacheModel')->checkbox();

echo $form->field($generator, 'doDbModel', [
    'hasFileSvg' => true,
    'fileSvgTitle' => $generator->langString('chose file/folder')
]);

Generator::regAssetsFile($this, 'js/custom-do-model.js');
if (!empty($_POST)) echo "<script>var isSubmit = true;</script>";
else echo "<script>var isSubmit = false;</script>";
