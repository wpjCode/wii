<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator wpjCode\wii\generators\crud\Generator */

/* @var $model \yii\db\ActiveRecord */
$model = new $generator->baseModelClass();
$safeAttributes = $model->safeAttributes();
if (empty($safeAttributes)) {
    $safeAttributes = $model->attributes();
}

$modelClass = ltrim($generator->modelClass, '\\');
echo <<<EOT
<?php

use \app\assets\AppAsset;

/* @var \$this yii\web\View */
/* @var \$model {$modelClass} */
/* @var \$form yii\widgets\ActiveForm */

AppAsset::addCss(\$this, '{$generator->getPageCssPath('form')}');
?>
EOT;
?>


<div class="content-container">
    <el-scrollbar style="height: 100%;">

        <el-container class="wp-form-container">
            <el-main class="">
                <el-form :model="form" :rules="formRules" ref="ruleForm" label-width="140px" class="form-600">

<?php
    $space = "";
    foreach ($generator->getColumnNames() as $attribute) {
        if (in_array($attribute, $safeAttributes)) {
            echo $space . $generator->generateActiveField($attribute) . "\n\n";
        }
    }
?>

                </el-form>
            </el-main>

            <el-footer class="bottom-button" :height="50">
                <el-button size="mini" type="primary" @click="submitAdd" v-if="setting.isAdd === true">
                    创建
                </el-button>
                <el-button size="mini" type="primary" @click="submitUpdate" v-if="setting.isAdd === false">
                    保存
                </el-button>
                <el-button size="mini" @click="cancel">取消</el-button>
            </el-footer>
        </el-container>

    </el-scrollbar>

</div>

<?= <<<EOT
<?= AppAsset::addScript(\$this, '{$generator->getPageJsPath('form')}'); ?>
<?= \$this->registerJs('
    new app();
'); ?>
EOT;
?>
