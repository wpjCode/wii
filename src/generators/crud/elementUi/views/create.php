<?php

/* @var $this yii\web\View */
/* @var $generator wpjCode\wii\generators\crud\Generator */
?>
<?php echo <<<EOT
<?php

/* @var \$apiModule string */
/* @var \$apiController string */

?>
EOT;
?>
<el-container class="wp-form-container">
    <el-header class="top-wrapper no-pb" height="auto">
        <el-row :inline="true" class="button-container">
            <el-col>
                <el-breadcrumb separator="/">
                    <el-breadcrumb-item>
                        <a @click="goToIndex"><i class="el-icon-location-outline"></i>&nbsp;首页</a>
                    </el-breadcrumb-item>
                    <el-breadcrumb-item>
                        <a @click="cancel">&nbsp;<?= $generator->expName; ?></a>
                    </el-breadcrumb-item>
                    <el-breadcrumb-item>添加</el-breadcrumb-item>
                </el-breadcrumb>
            </el-col>
        </el-row>
    </el-header>
    <?= "<?= " ?>$this->render('_form', ['apiModule' => $apiModule, 'apiController' => $apiController]) ?>
</el-container>
