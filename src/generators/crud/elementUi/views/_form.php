<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator wpjCode\wii\generators\crud\Generator */

/* @var $baseModelClass \yii\db\ActiveRecord */
$baseModelClass = new $generator->baseModelClass();
$safeAttributes = $baseModelClass->safeAttributes();

$baseModelNS = ltrim($generator->baseModelClass, '\\');
echo <<<EOT
<?php

use \app\assets\BackendAsset;

/* @var \$this yii\web\View */
/* @var \$model {$baseModelNS} */
/* @var \$form yii\widgets\ActiveForm */

BackendAsset::addCss(\$this, '{$generator->getPageCssPath('form')}');
?>
EOT;
?>

<el-container class="wp-form-container">
    <el-main class="">
        <el-form :model="form" :rules="formRules" ref="ruleForm" label-width="140px"
                 class="form-600">

<?php
$space = "";
foreach ($generator->getTableSchema()->columns as $attribute) {
    // 键略过
    if ($attribute->isPrimaryKey) continue;
    if (in_array($attribute->name, $safeAttributes)) {
        echo $space . $generator->generateActiveField($attribute->name) . "\n\n";
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

<?= <<<EOT
<?= BackendAsset::addScript(\$this, '{$generator->getPageJsPath('form')}'); ?>
<?= \$this->registerJs('
    new app();
'); ?>
EOT;
?>
