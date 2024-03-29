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

use \app\assets\BackendAsset as Asset;
use \app\models\\tableModel\AdminRoleModel;

/* @var \$this yii\web\View */
/* @var \$model {$baseModelNS} */
/* @var \$form yii\widgets\ActiveForm */

?>
EOT;
?>

<el-main class="content-wrapper no-pl no-pr no-border bg-gray">
    <el-form :model="form" :rules="formRules" ref="ruleForm" label-width="140px"
             class="form-400" label-position="left" :validate-on-rule-change="false">

<?php
$space = "";
foreach ($generator->getTableSchema()->columns as $attribute) {
// 键略过
if ($attribute->isPrimaryKey) continue;
if (in_array($attribute->name, $safeAttributes)) {
    echo '        <el-card shadow="hover">' . "\n";
    echo $space . $generator->generateActiveField($attribute->name);
    echo "\n" . '        </el-card>' . "\n\n";
}
}
?>
    </el-form>
    <div class="form-bottom-free"></div>
</el-main>

<el-footer class="bottom-button" :height="50">
    <el-button size="mini" type="danger" @click="cancel" v-show="pageDialog.isIframe">
        取消
    </el-button>
    <?= '<?php if (AdminRoleModel::checkAuth(\'create\')) { ?>' . "\n" ?>
    <el-button size="mini" type="success" @click="submit" v-if="setting.isCreate === true">
        创建
    </el-button>
    <?= '<?php } ?>' . "\n" ?>
    <?= '<?php if (AdminRoleModel::checkAuth(\'update\')) { ?>' . "\n" ?>
    <el-button size="mini" type="primary" @click="submit" v-if="setting.isCreate === false">
        保存
    </el-button>
    <?= '<?php } ?>' . "\n" ?>
</el-footer>

<?= <<<EOT

<?php
Asset::addCss(\$this, '{$generator->getPageCssPath('form')}');
Asset::addScript(\$this, '{$generator->getPageJsPath('form')}');

\$isCreate = !isset(\$isCreate) || !\$isCreate ? 'false' : 'true'; // 是否新建
\$this->registerJs('instance = new app(' . \$isCreate . ');');
?>
EOT;
?>
