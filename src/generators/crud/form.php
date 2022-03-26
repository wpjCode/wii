<?php

use \wpjCode\wii\Generator;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $generator wpjCode\wii\generators\crud\Generator */
echo $form->field($generator, 'expName');
echo $form->field($generator, 'baseModelClass', [
    'hasFileSvg' => true,
    'fileSvgTitle' => $generator->langString('chose file/folder')
]);
echo $form->field($generator, 'controllerShowClass', [
    'hasFileSvg' => true,
    'fileSvgTitle' => $generator->langString('chose file/folder'),
    'syncFileSvg' => true,
    'syncFileTitle' => $generator->langString('Sync model class base name'),
    'syncExt' => 'Controller', // 同步扩展结尾字符
    'reduceFolder' => true,
    'reduceFolderTitle' => $generator->langString('reduce folder title')
]);
echo $form->field($generator, 'controllerDoClass', [
    'hasFileSvg' => true,
    'fileSvgTitle' => $generator->langString('chose file/folder'),
    'syncFileSvg' => true,
    'syncFileTitle' => $generator->langString('Sync model class base name'),
    'syncExt' => 'Controller', // 同步扩展结尾字符
    'reduceFolder' => true,
    'reduceFolderTitle' => $generator->langString('reduce folder title')
]);
echo $form->field($generator, 'viewPath', [
    'hasFileSvg' => true,
    'fileSvgTitle' => $generator->langString('chose file/folder'),
    'useAlias' => true,
    'syncFileSvg' => true,
    'syncFileTitle' => $generator->langString('Sync model class base name'),
    'syncExt' => '', // 同步扩展结尾字符
    'reduceFolder' => true,
    'reduceFolderTitle' => $generator->langString('reduce folder title')
]);
echo $form->field($generator, 'jsPath', [
    'hasFileSvg' => true,
    'fileSvgTitle' => $generator->langString('chose file/folder'),
    'useAlias' => true,
    'syncFileSvg' => true,
    'syncFileTitle' => $generator->langString('Sync model class base name'),
    'syncExt' => '', // 同步扩展结尾字符
    'reduceFolder' => true,
    'reduceFolderTitle' => $generator->langString('reduce folder title')
]);
echo $form->field($generator, 'cssPath', [
    'hasFileSvg' => true,
    'fileSvgTitle' => $generator->langString('chose file/folder'),
    'useAlias' => true,
    'syncFileSvg' => true,
    'syncFileTitle' => $generator->langString('Sync model class base name'),
    'syncExt' => '', // 同步扩展结尾字符
    'reduceFolder' => true,
    'reduceFolderTitle' => $generator->langString('reduce folder title')
]);
echo $form->field($generator, 'controllerShowLayout', [
    'hasFileSvg' => true,
    'fileSvgTitle' => $generator->langString('chose file/folder'),
    'useAlias' => true
]);
/* echo $form->field($generator, 'baseControllerClass', [
    'hasFileSvg' => true,
    'fileSvgTitle' => $generator->langString('chose file/folder')
]); */

Generator::regAssetsFile($this, 'js/custom-crud.js');
