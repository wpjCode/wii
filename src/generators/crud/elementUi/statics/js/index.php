<?php

/* @var $generator wpjCode\wii\generators\crud\Generator */
/* @var $model \yii\db\ActiveRecord */
$model = new $generator->baseModelClass();
?>
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
                pageType: 'index', // 页面类型
                bodyWidth: document.documentElement.clientWidth, // body宽度
                smallScreenWidth: 998, // 小屏幕临界点(px)
                isSmallScreen: false, // 是否是小屏幕
            },
            searchForm: {}, // 搜索字段
            searchTopType: 'id', // 顶部搜索类型
            searchTopValue: '', // 顶部搜索内容
            showAllSearch: false, // 是否出现[更多查询]按钮
            searchFormAll: {},    // [更多查询]搜索字段
            allSearchDid: false,  // 已经提交过[更多查询]
            searchOrderField: '', // 查询排序字段
            searchOrderType: '', // 查询排序类型
            dataList: [], // 数据列表
            handelSelectList: [], // 当前多选项
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
                this.pageDialog.isIframe = +params['is_iframe'] === 1;

                var that = this;
                // [嵌入]返回事件监听 直接走自己的返回
                if (this.pageDialog.isIframe) {
                    window.addEventListener("popstate", function($event) {
                        that.cancel();              // 返回上一页
                        window.history.forward(-1); // 清理此页历史记录
                    }, false);
                    window.history.pushState({
                        title: "title", url: "#"
                    }, "title", "#");
                    window.history.forward(1);
                }
            },
            /**
             * 顶部查询 - 初始化查询[FORM]
             * @returns {boolean}
             */
            initSearchForm: function () {

                // 默认搜索字段
                this.searchForm = {
                    id: '',
<?php if ($model->hasAttribute('title')) { ?>
                    title: '',
<?php } if ($model->hasAttribute('name')) { ?>
                    name: '',
<?php } if ($model->hasAttribute('status')) { ?>
                    status: ''
<?php } ?>
                };
                // 赋值默认值
                for (var i in this.setting) {
                    if (!this.setting.hasOwnProperty(i)) continue;
                    // 不存在指定字符串直接返回
                    if (i.indexOf('default_') === -1) continue;
                    if (this.searchForm[i.replace('default_', '')] === undefined) continue;
                    this.searchForm[i.replace('default_', '')] = this.setting[i];
                }
            },
            /**
             * 顶部[更多查询] - 初始化查询[FORM]
             * @returns {boolean}
             */
            initSearchFormAll: function () {

                // 默认搜索字段
                this.searchFormAll = {};
                // 赋值默认值
                for (var i in this.setting) {
                    if (!this.setting.hasOwnProperty(i)) continue;
                    // 不存在指定字符串直接返回
                    if (i.indexOf('default_') === -1) continue;
                    if (this.searchFormAll[i.replace('default_', '')] === undefined) continue;
                    this.searchFormAll[i.replace('default_', '')] = this.setting[i];
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
                        }

                        // 最终清空性初始化查询
                        that.initSearchForm();
                        // 最终清空性初始化更多查询
                        that.initSearchFormAll();

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
                        search: this.searchForm,
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
                this.handelSelectList = id_list;
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
            handelSortChange: function ($column) {
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
                if (!$id || $id.length <= 0) {$id = this.handelSelectList;}

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
                        data: {idList: $id},
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
                            that.handelSelectList = null;
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
                if (!$id || $id.length <= 0) {$id = this.handelSelectList;}

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
                        data: {idList: $id},
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
                            that.handelSelectList = null;

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
                        idList: $row['id'],
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
                    is_iframe: 1, // 隐藏尾部
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
                    is_iframe: 1, // 隐藏尾部
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
        },
        watch: {
            /**
             * 顶部查询 - 顶部查询类型变化
             * @param val
             */
            'searchTopType': function (val) {
                // 先初始化[FORM]
                this.initSearchForm();
                // 重新赋值下值 - 具体效果就是不清空现在查询框内容
                this.searchForm[val] = this.searchTopValue;
            },
            /**
             * 顶部查询类型变化
             * @param $val
             */
            'searchTopValue': function ($val) {
                this.searchForm[this.searchTopType] = $val;
            },
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
        }
    });
};
