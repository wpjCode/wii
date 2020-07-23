<?php

/* @var $this yii\web\View */
/* @var $generator wpjCode\wii\generators\crud\Generator */
?>

<div class="app-container" id="formContainer" v-show="loadOver" style="display: none;">
    <div class="top-title">
        <el-breadcrumb separator="/">
            <el-breadcrumb-item>
                <a>
                    <i class="el-icon-location-outline"></i> 首页
                </a>
            </el-breadcrumb-item>
            <el-breadcrumb-item><?= $generator->expName ?></el-breadcrumb-item>
            <el-breadcrumb-item>添加</el-breadcrumb-item>
        </el-breadcrumb>
    </div>
    <?= "<?= " ?>$this->render('_form') ?>
</div>
