<?php

/* @var $this yii\web\View */
/* @var $generator wpjCode\wii\generators\crud\Generator */
/* @var $model \yii\db\ActiveRecord */
$model = new $generator->baseModelClass();
echo <<<EOT
<?php
    
use \app\assets\BackendAsset as Asset;
use \app\models\\tableModel\AdminRoleModel;

/* @var \$this yii\web\View */
?>
EOT;
?>

<el-container class="index-wrapper">
    <el-header class="top-wrapper bg-white" :class="getTopClass" height="auto">
        <el-row :inline="true" class="button-container">
            <el-col :xs="12" :sm="12" :md="12" :lg="5">
                <el-breadcrumb separator="/">
                    <el-breadcrumb-item>
                        <a @click="goToIndex"><i class="el-icon-location-outline"></i>&nbsp;首页</a>
                    </el-breadcrumb-item>
                    <el-breadcrumb-item @dblclick.native="window.open(window.location.href)">
                        <?= $generator->expName ?>

                    </el-breadcrumb-item>
                </el-breadcrumb>
            </el-col>
            <el-col :xs="12" :sm="12" :md="12" :lg="19" class="text-right">
                <?= '<?php if (AdminRoleModel::checkAuth(\'create\')) { ?>'. "\n" ?>
                <el-button class="" size="mini" type="success"
                           @click.native="goToCreate()">
                    新建
                </el-button>
                <?= '<?php } ?>' . "\n" ?>
                <el-dropdown size="mini">
                    <el-button type="primary" size="mini">
                        更多操作
                        <i class="el-icon-arrow-down el-icon--right"></i>
                    </el-button>
                    <el-dropdown-menu slot="dropdown">
                        <?= '<?php if (AdminRoleModel::checkAuth(\'open\')) { ?>' . "\n" ?>
                        <el-dropdown-item size="mini" @click.native="openItem(null)">
                            批量恢复
                        </el-dropdown-item>
                        <?= '<?php } ?>' . "\n" ?>
                        <?= '<?php if (AdminRoleModel::checkAuth(\'disabled\')) { ?>' . "\n" ?>
                        <el-dropdown-item size="mini" @click.native="disabledItem(null)" divided>
                            批量禁用
                        </el-dropdown-item>
                        <?= '<?php } ?>' . "\n" ?>
                    </el-dropdown-menu>
                </el-dropdown>
            </el-col>
        </el-row>
    </el-header>
    <el-main class="content-wrapper transits bg-white">
        <div class="p-10" style="display: none;">
            <!-- 提醒 START -->
            <div class="tip">
                <el-collapse v-model="setting.activeNotice" accordion>
                    <el-collapse-item>
                        <template slot="title">
                            <i class="el-icon el-icon-info"></i>
                            &nbsp;&nbsp;
                            <span class="title">
                                温馨提示
                            </span>
                        </template>
                        <div class="content">
                            <p>
                                1、一些小提示: 我是提示；
                                一些小代码：<code>我是小代码</code>
                            </p>
                        </div>
                    </el-collapse-item>
                </el-collapse>
            </div>
            <!-- 提醒 END -->
        </div>
        <!-- 查询 START -->
        <?= '<?=' ?>$this->render('../common/search.php', [
            'groupFields'      => 'searchForm.group',
            'groupOtherFields' => 'searchForm.groupOther',
            'baseFields'       => 'searchForm.base',
            'moreFields'       => 'searchForm.more',
            'export'           => 'searchForm.value',
            'submit'           => 'handleCurrentChange'
        ]); <?= '?>' ?>
        <!-- 查询 START -->
        <!-- 主列表 表格 START -->
        <el-table :data="dataList" style="width: 100%" class="" @selection-change="handleSelectionChange"
                  @sort-change="handleSortChange">

            <el-table-column type="selection" width="55"></el-table-column>

            <el-table-column fixed prop="id" label="编号" width="100" sortable="custom">
                <template slot-scope="scope">
                    <el-tooltip class="item" effect="light" :content="scope.row.id"
                                placement="top-start">
                        <div class="text-more-ellipsis">
                            <span v-text="scope.row.id" class="pointer"></span>
                        </div>
                    </el-tooltip>
                </template>
            </el-table-column>
        <?php if ($model->hasAttribute('name')) { ?>

            <el-table-column prop="name" label="名称">
                <template slot-scope="scope">
                    <el-tooltip class="item" effect="light"
                                :content="scope.row.name" placement="top-start">
                        <div class="text-more-ellipsis">
                            <span v-text="scope.row.name"
                                  class="pointer text-over-flow"></span>
                        </div>
                    </el-tooltip>
                </template>
            </el-table-column>
        <?php } ?>
        <?php if ($model->hasAttribute('title')) { ?>

            <el-table-column prop="title" label="标题">
                <template slot-scope="scope">
                    <el-tooltip class="item" effect="light"
                                :content="scope.row.title" placement="top-start">
                        <div class="text-more-ellipsis">
                            <span v-text="scope.row.title"
                                  class="pointer text-over-flow"></span>
                        </div>
                    </el-tooltip>
                </template>
            </el-table-column>
        <?php } ?>
        <?php if ($model->hasAttribute('update_time')) { ?>

            <el-table-column prop="update_time" label="修改时间" width="120" sortable="custom">
                <template slot-scope="scope">
                    <el-tooltip class="item" effect="light"
                                :content="scope.row.update_time_text"
                                placement="top-start">
                        <div class="text-more-ellipsis">
                            <span v-text="scope.row.update_time_text_s"
                                  class="pointer"></span>
                        </div>
                    </el-tooltip>
                </template>
            </el-table-column>
        <?php } ?>
        <?php if ($model->hasAttribute('status')) { ?>

            <el-table-column prop="status_text" label="状态" width="80">
                <template slot-scope="scope">
                    <div class="text-more-ellipsis" v-if="setting.status_list">
                        <span v-if="scope.row.status == setting.status_list.disabled.value"
                              v-text="'已' + scope.row.status_text"
                              class="pointer text-danger"></span>
                        <span v-else-if="scope.row.status == setting.status_list.open.value"
                              v-text="'已' + scope.row.status_text"
                              class="pointer text-success"></span>
                        <span v-else v-text="'已' + scope.row.status_text"
                              class="pointer"></span>
                    </div>
                </template>
            </el-table-column>
        <?php } ?>
        <?php if ($model->hasAttribute('sort') || $model->hasAttribute('list_order')) { ?>

            <el-table-column prop="sort" label="排序" width="85" title="双击修改排序" sortable="custom">
                <template slot-scope="scope">
                    <?= '<?php if (AdminRoleModel::checkAuth(\'sort\')) { ?>' . "\n" ?>
                    <div class="column-border-dashed pointer" title="双击修改排序"
                         @dblclick="showEditSort(scope.row)">
                        <el-popover placement="top" width="160"
                                    v-model="scope.row.sortEdit">
                            <el-container>
                                <el-header height="20">请输入新的排序</el-header>
                                <el-main height="40">
                                    <el-input placeholder="请输入新排序"
                                              v-model="scope.row.newSort"
                                              size="mini"></el-input>
                                </el-main>
                                <el-footer height="40">
                                    <el-row>
                                        <el-col :span="8" offset="5">
                                            <el-button type="text" class="text-danger"
                                                       size="mini"
                                                       @click="scope.row.sortEdit = false">
                                                取消
                                            </el-button>
                                        </el-col>
                                        <el-col :span="8">
                                            <el-button type="text" size="mini"
                                                       @click="sort(scope.row)">
                                                确定
                                            </el-button>
                                        </el-col>
                                    </el-row>
                                </el-footer>
                            </el-container>
                            <span v-text="scope.row.sort" title="双击编辑"
                                  class="pointer text-more-ellipsis"
                                  slot="reference">
                            </span>
                        </el-popover>
                    </div>
                    <?= '<?php } else { ?>' . "\n" ?>
                    <span v-text="scope.row.sort"></span>
                    <?= '<?php } ?>' . "\n" ?>
                </template>
            </el-table-column>
        <?php } ?>

            <el-table-column fixed="right" label="操作" width="180">
                <template slot-scope="scope">
                    <?= '<?php if (AdminRoleModel::checkAuth(\'edit\')) { ?>' . "\n" ?>
                    <el-button type="text" size="small"
                               @click.native="goToUpdate(scope.row.id)">编辑
                    </el-button>
                    <?= '<?php } ?>' . "\n" ?>
<?php if ($model->hasAttribute('status')) { ?>
                    <?= '<?php if (AdminRoleModel::checkAuth(\'disabled\')) { ?>' . "\n" ?>
                    <el-button type="text text-danger" size="small"
                               v-if="scope.row.status != setting.status_list.disabled.value"
                               @click.native="disabledItem(scope.row.id)">
                        {{setting.status_list['disabled'].text}}
                    </el-button>
                    <?= '<?php } ?>' . "\n" ?>
                    <?= '<?php if (AdminRoleModel::checkAuth(\'open\')) { ?>' . "\n" ?>
                    <el-button type="text text-success" size="small"
                               v-else @click.native="openItem(scope.row.id)">
                        {{setting.status_list['open'].text}}
                    </el-button>
                    <?= '<?php } ?>' . "\n" ?>
<?php } ?>
                </template>
            </el-table-column>
        </el-table>
        <!-- 主列表 表格 END -->

        <!-- 分页 START -->
        <div class="block pagination" v-if="dataTotal >= pageSize">
            <el-pagination @size-change="handleSizeChange"
                           @current-change="handleCurrentChange"
                           :current-page="page"
                           :page-sizes="[20, 50, 100, 200]"
                           :page-size="pageSize"
                           layout="total, sizes, prev, pager, next, jumper"
                           :total="dataTotal">
            </el-pagination>
        </div>
        <!-- 分页 END -->

        <div class="clean-80px" v-else></div>
    </el-main>
</el-container>

<?= <<<EOT

<?= Asset::addCss(\$this, '{$generator->getPageCssPath('index')}'); ?>

<?= Asset::addScript(\$this, '{$generator->getPageJsPath('index')}'); ?>
<?= \$this->registerJs('instance = new app();'); ?>
EOT;
?>
