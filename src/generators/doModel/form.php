<?php

use wpjCode\wii\generators\model\Generator;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $generator wpjCode\wii\generators\doModel\Generator */

echo $form->field($generator, 'expName', []);

echo $form->field($generator, 'baseModelClass', [
    'hasFileSvg' => true,
    'fileSvgTitle' => $generator->langString('chose file/folder'),
    'reduceFolder' => true,
    'reduceFolderTitle' => $generator->langString('reduce folder title')
]);

echo $form->field($generator, 'nameSpace', [
    'hasFileSvg' => true,
    'fileSvgTitle' => $generator->langString('chose file/folder'),
    'syncFileSvg' => true,
    'syncFileTitle' => $generator->langString('Sync model class base name'),
    'syncExt' => 'Model', // 同步扩展结尾字符
    'reduceFolder' => true,
    'reduceFolderTitle' => $generator->langString('reduce folder title')
]);

echo $form->field($generator, 'isCacheModel')->checkbox();

echo $form->field($generator, 'doDbModel', [
    'hasFileSvg' => true,
    'fileSvgTitle' => $generator->langString('chose file/folder'),
    'syncFileSvg' => true,
    'syncFileTitle' => $generator->langString('Sync model class base name'),
    'syncExt' => 'Model', // 同步扩展结尾字符
    'reduceFolder' => true,
    'reduceFolderTitle' => $generator->langString('reduce folder title')
]);

Generator::regAssetsFile($this, 'js/custom-do-model.js');
if (!empty($_POST)) echo "<script>var isSubmit = true;</script>";
else echo "<script>var isSubmit = false;</script>";
