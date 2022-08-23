<?php

/* @var $generator wpjCode\wii\generators\crud\Generator */
/* @var $model \yii\db\ActiveRecord */

use \app\assets\BackendAsset as Asset;

$model = new $generator->baseModelClass();
$safeAttributes = $generator->getTableSchema()->columns;

echo <<<EOT
<?php

use app\assets\BackendAsset as Asset;

?>
EOT;
?>

<el-container class="wp-form-container">
    <el-header class="top-wrapper no-pb bg-white" height="auto">
        <el-row :inline="true" class="button-container">
            <el-col>
                <el-breadcrumb separator="/">
                    <el-breadcrumb-item>
                        <a @click="goToIndex"><i class="el-icon-location-outline"></i>&nbsp;首页</a>
                    </el-breadcrumb-item>
                    <el-breadcrumb-item>
                        <a @click="cancel">&nbsp;<?= $generator->expName ?></a>
                    </el-breadcrumb-item>
                    <el-breadcrumb-item @dblclick.native="window.open(window.location.href)">
                        详情
                    </el-breadcrumb-item>
                </el-breadcrumb>
            </el-col>
        </el-row>
    </el-header>
    <el-main class="content-wrapper transits bg-white ph-30" v-if="!isError">
        <div class="mt-20"></div>
        <el-descriptions title="">
<?php foreach ($safeAttributes as $k => $v) {
                // tinyint 走文本
                if ($v->type == 'tinyint') {
            echo "            <el-descriptions-item label='{$v->comment}'>
                <span v-text=\"detail.{$v->name}_text\"></span>
            </el-descriptions-item>\n";
                } else if (strstr($v->name, 'image')) {
            echo "            <el-descriptions-item label='{$v->comment}'>
                <el-image class=\"thumb\" :preview-src-list=\"[detail.{$v->name}]\" fit=\"cover\"
                    :src=\"detail.{$v->name}\" v-if=\"detail.{$v->name}\">
                </el-image>
            </el-descriptions-item>\n";
                } else {
            echo "            <el-descriptions-item label='{$v->comment}'>
                <span v-text=\"detail.{$v->name}\"></span>
            </el-descriptions-item>\n";
                }
}?>
        </el-descriptions>
    </el-main>
    <el-main class="content-wrapper transits bg-white" v-else>
        <el-empty v-if="isError" :description="errorMsg" class="mt-200"></el-empty>
    </el-main>
    <el-footer class="bottom-button" :height="50">
        <el-button size="mini" type="danger" @click="cancel">
            返回
        </el-button>
    </el-footer>
</el-container>
<?php

echo <<<EOT
<?php

// Asset::addCss(\$this, "{$generator->getPageCssPath('detail')}");
Asset::addScript(\$this, "{$generator->getPageJsPath('detail')}");

\$this->registerJs('instance = new app();');

?>
EOT;
?>
