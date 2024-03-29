<?php

/* @var $generator wpjCode\wii\generators\crud\Generator */
/* @var $model \yii\db\ActiveRecord */
$model = new $generator->baseModelClass();

use yii\helpers\Inflector;
use yii\helpers\StringHelper; ?>
/**
 * [<?=$generator->expName?>]列表首页[JS]
 * @returns {*}
 */
var app = function () {

    return new Vue({
        el: '#vueContainer',
        data: {
            loadOver: false,
            settingOver: false,
            setting: {
                scenario: '<?= Inflector::camel2id(StringHelper::basename($className), '_') ?>',
                pageType: 'index',     // 页面类型
                smallScreenWidth: 998, // 小屏幕临界点(px)
                isSmallScreen: false,  // 是否是小屏幕
                bodyWidth: document.documentElement.clientWidth, // body宽度
            },
            searchForm: {
                group: [ // 组合查询
                    {name: 'id', text: '编号'},
<?php if ($model->hasAttribute('title')) { ?>
                    {name: 'title', text: '标题'},
<?php } if ($model->hasAttribute('name')) { ?>
                    {name: 'name', text: '名称'},
<?php } ?>
                ],
                groupOther: [
<?php if ($model->hasAttribute('status')) { ?>
                    {name: 'status', type: 'radio', text: '状态'},
<?php } ?>
                ],  // 组合查询 右侧
                base: [],  // 基础
                more: [],  // 更多
                value: {}  // 值
            },                    // 搜索字段
            searchOrderField: '', // 查询排序字段
            searchOrderType: '',  // 查询排序类型
            dataList: [],         // 数据列表
            handleSelectList: [], // 当前多选项
            showTopScroll: false, // 是否已滚动，默认否
            page: 1,
            pageSize: 20,
            dataTotal: 0,
            pageDialog: {
                show: false,    // 页面 - 是否展示弹出层
                url: '',        // 页面 - 弹出层连接
                loading: false, // 页面 - 弹出层加载中
                isIframe: false // 页面 - 是否[iframe]嵌入
            },
        },
        created: function () {
            // 初始化
            this.init();
            // 初始化设置
            this.getSetting();
            var that = this;
            this.$nextTick(function () {
                that.loadOver = true;
            });
        },
        methods: {
            /**
             * 初始化的逻辑
             */
            init: function () {

                var params = $w.getParams();
                // 是否[iframe]嵌入
                this.pageDialog.isIframe = window.self !== window.top || +params['is_iframe'] === 1;

                var that = this;
                // [嵌入]返回事件监听 直接走自己的返回
                if (this.pageDialog.isIframe) {
                    window.addEventListener("popstate", function($event) {
                        if (that.cancel) that.cancel(); // 返回上一页
                        window.history.forward(-1);     // 清理此页历史记录
                    }, false);
                    window.history.pushState({
                        title: "title", url: "#"
                    }, "title", "#");
                    window.history.forward(1);
                }

                this.$nextTick(function () {
                    // 监听主滚动条
                    that.$refs['mainScroller'].wrap.addEventListener('scroll', function ($event) {
                        that.showTopScroll = $event.srcElement.scrollTop > 10; // 顶部以滚动
                    })
                });
            },
            /**
             * 顶部查询 - 初始化查询[FORM]
             * @returns {boolean}
             */
            initSearchForm: function () {

                // 暂存
                var stage;
                // 赋值默认值
                for (var i in this.setting) {
                    if (!this.setting.hasOwnProperty(i)) continue;
                    // 不存在指定字符串直接返回
                    if (i.indexOf('default_') === -1 && i.indexOf('_list') === -1) continue;

                    // 组合字段右侧 默认值, 值列表
                    for (var x in this.searchForm['groupOther']) {
                        if (!this.searchForm['groupOther'].hasOwnProperty(x)) continue;
                        if (!this.searchForm['groupOther'][x]) continue;
                        // 字段默认值 键值
                        stage = 'default_' + this.searchForm['groupOther'][x]['name'];
                        // 相同则需要赋值[默认值]
                        if (stage === i) {
                            this.$set(this.searchForm['groupOther'][x], 'default',
                                this.setting[i]
                            );
                            continue;
                        }

                        // 字段默列表 键值
                        stage = this.searchForm['groupOther'][x]['name'] + '_list';
                        // 相同则需要赋值[默认值]
                        if (stage === i) {
                            this.$set(this.searchForm['groupOther'][x], 'option',
                                this.setting[i]
                            );
                        }
                    }

                    // 基础字段 默认值, 值列表
                    for (var y in this.searchForm['base']) {
                        if (!this.searchForm['base'].hasOwnProperty(y)) continue;
                        if (!this.searchForm['base'][y]) continue;
                        // 字段默认值 键值
                        stage = 'default_' + this.searchForm['base'][y]['name'];
                        // 相同则需要赋值[默认值]
                        if (stage === i) {
                            this.$set(this.searchForm['base'][y], 'default',
                                this.setting[i]
                            );
                            continue;
                        }

                        // 字段默列表 键值
                        stage = this.searchForm['base'][y]['name'] + '_list';
                        // 相同则需要赋值[默认值]
                        if (stage === i) {
                            this.$set(this.searchForm['base'][y], 'option',
                                this.setting[i]
                            );
                        }
                    }

                    // 更多字段 默认值, 值列表
                    for (var z in this.searchForm['more']) {
                        if (!this.searchForm['more'].hasOwnProperty(z)) continue;
                        if (!this.searchForm['more'][z]) continue;
                        // 字段默认值 键值
                        stage = 'default_' + this.searchForm['more'][z]['name'];
                        // 相同则需要赋值[默认值]
                        if (stage === i) {
                            this.$set(this.searchForm['more'][z], 'default',
                                i, this.setting
                            );
                            continue;
                        }

                        // 字段默列表 键值
                        stage = this.searchForm['more'][z]['name'] + '_list';
                        // 相同则需要赋值[默认值]
                        if (stage === i) {
                            this.$set(this.searchForm['more'][z], 'option',
                                this.setting[i]
                            );
                        }
                    }
                }
            },
            /**
             * 获取设置
             * @returns {boolean}
             */
            getSetting: function () {
                // 正在加载...
                var loadingInstance = ELEMENT.Loading.service({
                    fullscreen: false,
                    text: '加载中...'
                });
                var that = this;

                // 获取各模块的值
                $w.request({
                    url: $w.getApiUrl('<?=$generator->getControllerID(1)?>.setting'),
                    type: 'get',
                    data: {
                        type: 'index' // 首页
                    },
                    dataType: 'json',
                    beforeCallback: function () {
                        that.$nextTick(function () {
                            // 设置加载完毕
                            that.settingOver = true;
                            // 隐藏正在加载
                            loadingInstance.close();
                            // 获取下列表
                            that.getList();
                        });
                    },
                    callback: function (event) {

                        // 失败的返回|提示
                        if (parseInt(event.no) !== 200) {

                            return that.$message({
                                showClose: true,
                                type: 'error',
                                message: event.msg ? event.msg : '操作失败，请稍后尝试'
                            });
                        }

                        // 挨个赋值[setting]中
                        for (var i in event.data) {
                            if (!event.data.hasOwnProperty(i)) continue;
                            that.$set(that.setting, i, event.data[i]);

                            // 字段是列表值 需要更改键
                            if (i.indexOf('_list') !== -1) {
                                that.setting[i] = $w.array_index(that.setting[i], 'key');
                            }
                        }

                        // 最终清空性初始化查询
                        that.initSearchForm();

                        // 监测屏幕大小变化
                        return $(window).resize(function() {
                            // 超过此宽度展示 更多筛选
                            var bodyDom = document.getElementsByTagName('body');
                            if (bodyDom[0] &&
                                bodyDom[0].clientWidth <= that.setting.smallScreenWidth
                            ) {
                                return that.setting.isSmallScreen = true;
                            }
                            return that.setting.isSmallScreen = false;
                        }).resize();
                    }
                });
            },
            /**
             * 获取下列表
             */
            getList: function () {

                // 正在加载...
                var loadingInstance = ELEMENT.Loading.service({
                    fullscreen: false,
                    text: '加载中...'
                });
                var that = this;

                // 获取各模块的值
                $w.request({
                    url: $w.getApiUrl('<?=$generator->getControllerID(1)?>.list'),
                    type: 'get',
                    data: {
                        page: this.page,
                        page_size: this.pageSize,
                        search: this.searchForm.value,
                        sort_field: this.searchOrderField,
                        sort_type: this.searchOrderType
                    },
                    dataType: "json",
                    beforeCallback: function () {
                        that.$nextTick(function () {
                            // 隐藏正在加载
                            loadingInstance.close();
                        });
                    },
                    callback: function (event) {

                        // 失败的返回|提示
                        if (parseInt(event.no) !== 200) {
                            return that.$message({
                                showClose: true,
                                type: 'error',
                                message: event.msg ? event.msg : '操作失败，请稍后尝试'
                            });
                        }

                        // 数据
                        that.dataList = event.data.list;
                        for (var i in that.dataList) {
                            if (!that.dataList.hasOwnProperty(i)) continue;
                            that.$set(that.dataList[i], 'newSort', 0);
                            that.$set(that.dataList[i], 'sortEdit', false);
                        }
                        // 总条目
                        that.dataTotal = parseInt(event.data.total);
                    }
                });
            },
            /**
             * [更多查询]按钮点击
             */
            moreSearchClick: function () {
                if(this.showAllSearch) {
                    return this.showAllSearch = false;
                }
                this.showAllSearch = true;
            },
            /**
            * [更多查询]提交
            */
            moreSearchSubmit: function () {

                // 先将更多查询合并入普通查询
                this.searchForm = $w.eachAdd(this.searchForm, this.searchFormAll);
                this.page = 1;  // 默认到 第一页
                this.getList(); // 查询列表

                var that = this;
                this.$nextTick(function () {
                    that.allSearchDid = true;   // 更多搜索- 已提交过
                    that.showAllSearch = false; // 更多搜索- 隐藏
                });
            },
            /**
            * [更多查询]取消
            */
            moreSearchCancel: function () {
                // 如果未提交过则 重新初始化
                if (!this.allSearchDid) {
                    // 还原初始化
                    this.initSearchFormAll();
                }
                // 以[普通查询]为主，[更多查询]中有的字段同步到[更多查询]
                else for (var i in this.searchForm) {
                    if (!this.searchForm.hasOwnProperty(i)) continue;
                    if (this.searchFormAll[i] === undefined) continue;
                    this.searchFormAll[i] = this.searchForm[i];
                }

                // 取消展示
                this.showAllSearch = false;
            },
            /**
            * [更多查询]重置
            */
            moreSearchReset: function () {

                // 重新初始化
                this.initSearchFormAll();

                // 先将更多查询合并入普通查询
                this.searchForm = $w.eachAdd(this.searchForm, this.searchFormAll);
                // 默认到 第一页
                this.page = 1;

                var that = this;
                this.$nextTick(function () {
                    that.getList();
                });
            },
            /**
             * 列表选择监测处理
             * @param $val
             */
            handleSelectionChange: function ($val) {

                // 选择暂存字段
                var selColumn = 'id';

                var id_list = [];
                $val.forEach(function (currentValue, index, arr) {
                    if (currentValue[selColumn]) {
                        id_list.push(currentValue[selColumn]);
                    }
                });
                // 赋值
                this.handleSelectList = id_list;
            },
            /**
             * 分页大小监测处理
             * @param $val
             */
            handleSizeChange: function ($val) {
                // 分页大小赋值
                this.pageSize = $val;
                // 默认到 第一页
                this.page = 1;
                this.getList();
            },
            /**
             * 分页跳转监测处理
             * @param $val
             */
            handleCurrentChange: function ($val) {
                $val = !parseInt($val) ? 1: $val;
                this.page = $val;
                this.getList();
            },
            /**
             * 排序检测处理
             * @param $column
             */
            handleSortChange: function ($column) {
                // 没有排序|字段 置空
                if (!$column.order || !$column.prop) {
                    $column.order = null;
                    $column.prop = null;
                }
                var type = '';
                switch ($column.order) {
                    case 'ascending':
                        type = 'asc';
                        break;
                    case 'descending':
                        type = 'desc';
                        break;
                }
                this.searchOrderField = $column.prop;
                this.searchOrderType = type;
                // 调用加载列表
                this.getList();
            },
            /**
             * 恢复记录
             */
            openItem: function ($id) {

                // 如果 $id 不传值 就 走列表
                if (!$id || $id.length <= 0) {$id = this.handleSelectList;}

                // 判断id是否为空
                if (!$id || $id.length <= 0) {
                    return this.$message({
                        showClose: true,
                        type: 'error',
                        message: '请至少选择一个条目'
                    });
                }

                var that = this;

                this.$confirm('此操作将[开启]数据, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(function () {

                    // 正在加载。。
                    var loadingInstance = ELEMENT.Loading.service({
                        fullscreen: false,
                        text: '开启中...'
                    });

                    // 获取各模块的值
                    $w.request({
                        url: $w.getApiUrl('<?=$generator->getControllerID(1)?>.open'),
                        type: 'POST',
                        data: {id_list: $id},
                        dataType: "json",
                        beforeCallback: function () {
                            that.$nextTick(function () {
                                // 隐藏正在加载
                                loadingInstance.close();
                            });
                        },
                        callback: function (event) {

                            // 失败的返回|提示
                            if (parseInt(event.no) !== 200) {

                                return that.$message({
                                    showClose: true,
                                    type: 'error',
                                    message: event.msg ? event.msg : '操作失败，请稍后尝试'
                                });
                            }

                            // 放空列表
                            that.handleSelectList = null;
                            // 成功 加载下列表
                            return that.$nextTick(function () {
                                // 隐藏正在加载
                                that.getList();
                            });
                        }
                    });
                });
            },
            /**
             * 禁用记录
             */
            disabledItem: function ($id) {

                // 如果 $id 不传值 就 走列表
                if (!$id || $id.length <= 0) {$id = this.handleSelectList;}

                // 判断id是否为空
                if (!$id || $id.length <= 0) {
                    return this.$message({
                        showClose: true,
                        type: 'error',
                        message: '请至少选择一个条目'
                    });
                }

                var that = this;

                this.$confirm('此操作将[禁用]数据, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(function () {

                    // 正在加载。。
                    var loadingInstance = ELEMENT.Loading.service({
                        fullscreen: false,
                        text: '禁用中...'
                    });

                    // 获取各模块的值
                    $w.request({
                        url: $w.getApiUrl('<?=$generator->getControllerID(1)?>.disabled'),
                        type: 'POST',
                        data: {id_list: $id},
                        dataType: "json",
                        beforeCallback: function () {
                            that.$nextTick(function () {
                                // 隐藏正在加载
                                loadingInstance.close();
                            });
                        },
                        callback: function (event) {

                            // 失败的返回|提示
                            if (parseInt(event.no) !== 200) {

                                return that.$message({
                                    showClose: true,
                                    type: 'error',
                                    message: event.msg ? event.msg : '操作失败，请稍后尝试'
                                });
                            }

                            // 放空列表
                            that.handleSelectList = null;

                            // 成功 加载下列表
                            return that.$nextTick(function () {
                                // 隐藏正在加载
                                that.getList();
                            });
                        }
                    });
                });
            },
            /**
             * 修改排序编辑框展示
             * @param $row
             */
            showEditSort: function ($row) {
                $row['sortEdit'] = true;
                this.$set($row, 'newSort', $row['sort']);
            },
<?php if ($model->hasAttribute('sort') || $model->hasAttribute('list_order')) { ?>
            /**
             * 修改排序提交
             * @param $row
             */
            sort: function ($row) {

                // 先来判断是否未改变还提交了
                if (parseInt($row['newSort']) === parseInt($row['sort'])) {

                    return this.$message({
                        showClose: true,
                        type: 'warning',
                        message: '请修改排序数值'
                    });
                }

                // 是否越出范围值 大于
                if ($row['newSort'] > this.setting.max_sort) {

                    return this.$message({
                        showClose: true,
                        type: 'warning',
                        message: '排序最大不得超过 ' + this.setting.max_sort
                    });
                }

                //是否越出范围值 小于
                if ($row['newSort'] < this.setting.min_sort) {

                    return this.$message({
                        showClose: true,
                        type: 'warning',
                        message: '排序最小不得超过 ' + this.setting.min_sort
                    });
                }

                // 正在加载。。
                var loadingInstance = ELEMENT.Loading.service({
                    fullscreen: false,
                    text: '排序中...'
                });
                var that = this;

                // 获取各模块的值
                $w.request({
                    url: $w.getApiUrl('<?=$generator->getControllerID(1)?>.sort'),
                    type: 'POST',
                    data: {
                        id_list: $row['id'],
                        sort: parseInt($row['newSort'])
                    },
                    dataType: "json",
                    beforeCallback: function () {
                        that.$nextTick(function () {
                            // 隐藏正在加载
                            loadingInstance.close();
                        });
                    },
                    callback: function (event) {

                        // 失败的返回|提示
                        if (parseInt(event.no) !== 200) {
                            return that.$message({
                                showClose: true,
                                type: 'error',
                                message: event.msg ? event.msg : '操作失败，请稍后尝试'
                            });
                        }

                        // 成功 加载下列表
                        return that.$nextTick(function () {
                            // 隐藏正在加载
                            that.getList();
                        });
                    }
                });
            },
<?php } ?>

            /**
            * 跳转到添加
            */
            goToCreate: function () {

                this.pageDialog.url = $w.getPageUrl('<?=$generator->getControllerID(1)?>.create', {
                    is_iframe: 1,
                    rand_str: Math.random()
                });
                this.pageDialog.loading = true; // 页面弹出层加载中
                this.pageDialog.show = true;    // 展示页面弹出层
                // [IFRAME]加载完毕
                var that = this;
                $("#pageIframe").load(function () {
                    that.pageDialog.loading = false; // 页面加载中 否
                });
            },
            /**
            * 跳转到编辑
            */
            goToUpdate: function ($id) {

                this.pageDialog.url = $w.getPageUrl('<?=$generator->getControllerID(1)?>.update', {
                    id: $id,
                    is_iframe: 1,
                    rand_str: Math.random()
                });
                this.pageDialog.loading = true; // 页面弹出层加载中
                this.pageDialog.show = true;    // 展示页面弹出层
                // [IFRAME]加载完毕
                var that = this;
                this.$nextTick(function () {
                    $("#pageIframe").load(function () {
                        that.pageDialog.loading = false; // 页面加载中 否
                    });
                });
            },
            /**
             * 跳转到详情
             */
            goToDetail: function ($id) {

                this.pageDialog.url = $w.getPageUrl('<?=$generator->getControllerID(1)?>.detail', {
                    id: $id,
                    is_iframe: 1,
                    rand_str: Math.random()
                });
                this.pageDialog.loading = true; // 页面弹出层加载中
                this.pageDialog.show = true;    // 展示页面弹出层
                // [IFRAME]加载完毕
                var that = this;
                this.$nextTick(function () {
                    $("#pageIframe").load(function () {
                        that.pageDialog.loading = false; // 页面加载中 否
                    });
                });
            },
            /**
             * 去首页
             */
            goToIndex: function () {
                // 父级
                var parent = window.parent.top;
                if (!parent) return false;

                // 父级[vue]对象
                var vueInstance = parent.instance;
                if (!parent || !(typeof vueInstance === 'object')) return false;

                // 键值
                var key = vueInstance.indexKey;
                // 操作点击
                $(parent.document).find('#tab-' + key).click();
            },
            /**
             * 导出询问
             */
            exportConfirm: function () {
                var that = this;
                this.$confirm('此操作将[导出]全部查询结果为Excel文件, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(function () {
                    // 导出
                    that.exportItem(1);
                });
            },
            /**
             * 导出执行
             * @param $page
             * @param $filePath
             */
            exportItem: function ($page, $filePath) {

                $page = $page || 1;
                $filePath = $filePath || null;

                var that = this;

                // 正在加载。。
                var loadingInstance = ELEMENT.Loading.service({
                    fullscreen: false,
                    text: '导出第' + $page + '页...'
                });

                // 获取各模块的值
                $w.request({
                    url: $w.getApiUrl('<?=$generator->getControllerID(1)?>.export'),
                    type: 'POST',
                    data: {
                        page: $page,
                        page_size: this.pageSize,
                        search: this.searchForm.value,
                        file_path: $filePath
                    },
                    dataType: "json",
                    beforeCallback: function () {},
                    callback: function (event) {

                        // 失败的返回|提示
                        if (parseInt(event.no) !== 200) {

                            // 隐藏正在加载
                            loadingInstance.close();
                            return that.$message({
                                showClose: true,
                                type: 'error',
                                message: event.msg ? event.msg : '操作失败，请稍后尝试'
                            });
                        }

                        // 如果下一页没有了 直接打卡下载
                        if (parseInt(event.data.next_have) !== 1) {

                            // 隐藏正在加载
                            loadingInstance.close();
                            return window.open(event.data.path);
                        }

                        // 成功 加载下一页列表
                        return setTimeout(function () {
                            // 隐藏正在加载
                            loadingInstance.close();
                            that.exportItem($page + 1, event.data.path);
                        }, 1000);
                    }
                });
            }
        },
        watch: {
            /**
             * 检测[页面弹出层加载中状态]
             */
            'pageDialog.loading': function ($value) {
                // 未加载中||已经有计时器 不操作
                if (!$value || this.timer) return false;
                var that = this;
                this.timer = setTimeout(function () {
                    that.pageDialog.loading = false; // 加载完毕
                    clearTimeout(that.timer);        // 清除计时器
                    that.timer = null;               // 清空变量存储
                }, 1000)
            }
        },
        computed: {
            /**
             * 获得顶部样式
             */
            getTopClass: function () {
                var className = [];
                // 如果已经滚动
                if (this.showTopScroll) className.push('is-scroll');
                return className.join(' ');
            }
        },
    });
};
