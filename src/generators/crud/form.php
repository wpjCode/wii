<?php
/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $generator wpjCode\wii\generators\crud\Generator */

echo $form->field($generator, 'expName');
echo $form->field($generator, 'baseModelClass', [
    'hasFileSvg' => true,
    'fileSvgTitle' => $generator->langString('chose file/folder')
]);
echo $form->field($generator, 'modelClass', [
    'hasFileSvg' => true,
    'fileSvgTitle' => $generator->langString('chose file/folder')
]);
echo $form->field($generator, 'controllerShowClass', [
    'hasFileSvg' => true,
    'fileSvgTitle' => $generator->langString('chose file/folder')
]);
echo $form->field($generator, 'controllerDoClass', [
    'hasFileSvg' => true,
    'fileSvgTitle' => $generator->langString('chose file/folder')
]);
echo $form->field($generator, 'viewPath', [
    'hasFileSvg' => true,
    'fileSvgTitle' => $generator->langString('chose file/folder'),
    'useAlias' => true
]);
echo $form->field($generator, 'jsPath', [
    'hasFileSvg' => true,
    'fileSvgTitle' => $generator->langString('chose file/folder'),
    'useAlias' => true
]);
echo $form->field($generator, 'cssPath', [
    'hasFileSvg' => true,
    'fileSvgTitle' => $generator->langString('chose file/folder'),
    'useAlias' => true
]);
echo $form->field($generator, 'controllerShowLayout', [
    'hasFileSvg' => true,
    'fileSvgTitle' => $generator->langString('chose file/folder'),
    'useAlias' => true
]);
echo $form->field($generator, 'baseControllerClass', [
    'hasFileSvg' => true,
    'fileSvgTitle' => $generator->langString('chose file/folder')
]);

