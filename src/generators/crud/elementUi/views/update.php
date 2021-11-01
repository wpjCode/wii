<?php

/* @var $this yii\web\View */
/* @var $generator wpjCode\wii\generators\crud\Generator */
?>
<el-container class="wp-form-container">
    <el-header class="top-wrapper no-pb" height="auto">
        <el-row :inline="true" class="button-container">
            <el-col>
                <el-breadcrumb separator="/">
                    <el-breadcrumb-item>
                        <a><i class="el-icon-location-outline"></i>&nbsp;首页</a>
                    </el-breadcrumb-item>
                    <el-breadcrumb-item>&nbsp;<?= $generator->expName; ?></el-breadcrumb-item>
                    <el-breadcrumb-item>修改</el-breadcrumb-item>
                </el-breadcrumb>
            </el-col>
        </el-row>
    </el-header>
    <?= "<?= " ?>$this->render('_form') ?>
</el-container>

