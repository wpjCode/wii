<?php

/* @var $this yii\web\View */
/* @var $generator wpjCode\wii\generators\crud\Generator */
/* @var $model \yii\db\ActiveRecord */
$model = new $generator->baseModelClass();
echo <<<EOT
<?php
    
use \app\assets\BackendAsset;

/* @var \$this yii\web\View */

BackendAsset::addCss(\$this, '{$generator->getPageCssPath('index')}');
?>
EOT;
?>

<el-header class="top-wrapper" height="auto">
    <el-row :inline="true" class="button-container">
        <el-col :xs="9" :sm="7" :md="6" :lg="5">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item>
                    <a><i class="el-icon-location-outline"></i>&nbsp;首页</a>
                </el-breadcrumb-item>
                <el-breadcrumb-item><?= $generator->expName ?></el-breadcrumb-item>
            </el-breadcrumb>
        </el-col>
        <el-col :xs="15" :sm="17" :md="18" :lg="19" class="text-right">
            <el-button class="" size="mini" type="success"
                       @click.native="goToCreate()">
                新建
            </el-button>
            <el-dropdown size="mini">
                <el-button type="primary" size="mini">
                    更多操作
                    <i class="el-icon-arrow-down el-icon--right"></i>
                </el-button>
                <el-dropdown-menu slot="dropdown">
                    <el-dropdown-item size="mini"
                                      @click.native="openItem(null)">
                        批量恢复
                    </el-dropdown-item>
                    <el-dropdown-item size="mini"
                                      @click.native="disabledItem(null)"
                                      divided>
                        批量禁用
                    </el-dropdown-item>
                </el-dropdown-menu>
            </el-dropdown>
        </el-col>
    </el-row>
    <div class="padding-10" style="display: none;">
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
    <el-form :inline="true" :model="searchForm" @submit.native.prevent
             class="search-container">
<?php if ($model->hasAttribute('status')) { ?>
        <el-form-item label="" class="" class="padding-right-30" v-if="!setting.isSmallScreen">
            <el-radio-group size="" v-model="searchForm.status" @change="handleCurrentChange(1)">
                <el-radio-button label="">全部</el-radio-button>
                <el-radio-button :label="key" v-for="(item, key) in setting.statusTextList">
                    {{item}}列表
                </el-radio-button>
            </el-radio-group>
        </el-form-item>
<?php } ?>
        <el-form-item label="" :class="!setting.isSmallScreen?'float-right':''">
            <el-input placeholder="请输入内容" v-model="searchTopValue" size="small" type="text"
                      class="input-with-select vert-align-init">
                <el-select v-model="searchTopType" slot="prepend" size="small"
                           placeholder="请选择" style="width: 130px;">

                    <el-option label="编号" value="id"></el-option>
<?php if ($model->hasAttribute('title')) { ?>
                    <el-option label="标题" value="title"></el-option>
<?php } if ($model->hasAttribute('name')) { ?>
                    <el-option label="名称" value="name"></el-option>
<?php } ?>
                </el-select>
                <el-button slot="append" type="primary" icon="el-icon-search"
                           size="small" @click="handleCurrentChange(1)">
                    搜索
                </el-button>
            </el-input>
        </el-form-item>
        <el-form-item label="" class="el-form-item-more" v-show="setting.isSmallScreen">
            <el-button type="text" @click="moreSearchClick">
                <span v-if="!setting.showAllSearch" class="font-fourth">
                    更多&nbsp;<i class="el-icon-caret-bottom font-fourth"></i>
                </span>
                <span v-else>
                    隐藏&nbsp;<i class="el-icon-caret-top"></i>
                </span>
            </el-button>
        </el-form-item>
        <el-collapse-transition>
            <div v-show="setting.showAllSearch" id="searchAllAni"
                 class="more-search-container">
                <!-- 此处添加[el-form-item] -->
<?php if ($model->hasAttribute('status')) {?>
                <el-form-item class="padding-right-30">
                    <el-radio-group v-model="searchForm.status"
                                    @change="handleCurrentChange(1)">
                        <el-radio-button label="">全部</el-radio-button>
                        <el-radio-button :label="key"
                                         v-for="(item, key) in setting.statusTextList">
                            {{item}}列表
                        </el-radio-button>
                    </el-radio-group>
                </el-form-item>
<?php } ?>
            </div>
        </el-collapse-transition>
    </el-form>
</el-header>
<el-main class="content-wrapper transits">
    <!-- 主列表 表格 START -->
    <el-table :data="dataList" style="width: 100%" class=""
              @selection-change="handleSelectionChange">
        <el-table-column type="selection" width="55"></el-table-column>

        <el-table-column fixed prop="id" label="编号" width="100">
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

        <el-table-column prop="update_time" label="修改时间" width="120">
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
                <div class="text-more-ellipsis" v-if="setting.statusList">
                    <span v-if="scope.row.status == setting.statusList.disabled"
                          v-text="'已' + scope.row.status_text"
                          class="pointer text-danger"></span>
                    <span v-else-if="scope.row.status == setting.statusList.open"
                          v-text="'已' + scope.row.status_text"
                          class="pointer text-success"></span>
                    <span v-else v-text="'已' + scope.row.status_text"
                          class="pointer"></span>
                </div>
            </template>
        </el-table-column>
    <?php } ?>
    <?php if ($model->hasAttribute('sort') || $model->hasAttribute('list_order')) { ?>

        <el-table-column prop="sort" label="排序" width="85" title="双击修改排序">
            <template slot-scope="scope">
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
            </template>
        </el-table-column>
    <?php } ?>

        <el-table-column fixed="right" label="操作" width="180">
            <template slot-scope="scope">
                <el-button type="text" size="small"
                           @click.native="goToUpdate(scope.row.id)">编辑
                </el-button>
<?php if ($model->hasAttribute('status')) { ?>
                <el-button type="text text-danger" size="small"
                           v-if="scope.row.status != setting.statusList.disabled"
                           @click.native="disabledItem(scope.row.id)">
                    {{setting.statusTextList[setting.statusList.disabled]}}
                </el-button>
                <el-button type="text text-success" size="small"
                           v-else @click.native="openItem(scope.row.id)">
                    {{setting.statusTextList[setting.statusList.open]}}
                </el-button>
<?php } ?>
            </template>
        </el-table-column>
    </el-table>
    <!-- 主列表 表格 END -->

    <!-- 分页 START -->
    <div class="block pagination">
        <el-pagination
                @size-change="handleSizeChange"
                @current-change="handleCurrentChange"
                :current-page="page"
                :page-sizes="[20, 50, 100, 200]"
                :page-size="20"
                layout="total, sizes, prev, pager, next, jumper"
                :total="dataTotal">
        </el-pagination>
    </div>
    <!-- 分页 END -->
</el-main>

<?= <<<EOT
<?= BackendAsset::addScript(\$this, '{$generator->getPageJsPath('index')}'); ?>
<?= \$this->registerJs('
    new app();
'); ?>
EOT;
?>
