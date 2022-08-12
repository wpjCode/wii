<?php

/* @var $generator wpjCode\wii\generators\crud\Generator */
/* @var $model \yii\db\ActiveRecord */

use \app\assets\BackendAsset as Asset;

$model = new $generator->baseModelClass();
$safeAttributes = $generator->getTableSchema()->columns;
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
                echo "             ";
                // tinyint 走文本
                if ($v->type == 'tinyint') {
                    echo "<el-descriptions-item label='{$v->comment}'><?=\$detail['{$v->name}_text']?></el-descriptions-item>\n";
                } else {
                    echo "<el-descriptions-item label='{$v->comment}'><?=\$detail['{$v->name}']?></el-descriptions-item>\n";
                }
}?>
        </el-descriptions>
    </el-main>
    <el-main class="content-wrapper transits bg-white" v-else>
        <el-empty v-if="isError" :description="errorMsg" class="mt-200"></el-empty>
    </el-main>
</el-container>
<?php
// Asset::addCss($this, "{$generator->getPageCssPath('detail')}");
Asset::addScript($this, "{$generator->getPageJsPath('detail')}");

$this->registerJs('instance = new app();');
?>
