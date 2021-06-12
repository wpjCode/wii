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
            setting: {
                showAllSearch: false, // 是否出现[展示全部查询]按钮
                smallScreenWidth: 998, // 小屏幕临界点(px)
                isSmallScreen: false, // 是否是小屏幕
            },
            searchForm: {}, // 搜索字段
            searchTopType: 'id', // 顶部搜索类型
            searchTopValue: '', // 顶部搜索内容
            dataList: [], // 父级数据列表
            handelSelectList: [], // 当前多选项
            page: 1,
            pageSize: 20,
            dataTotal: 0
        },
        created: function () {
            // 初始化下设置
            this.getSetting();
            var that = this;
            this.$nextTick(function () {
                that.loadOver = true;
            });
        },
        methods: {
            /**
             * 顶部查询 - 初始化查询[FORM]
             * @param $isClear
             * @returns {boolean}
             */
            initSearchForm: function ($isClear) {
                // 是否清空性质初始化
                if ($isClear) {
                    this.searchForm = {
                        id: '',
<?php if ($model->hasAttribute('title')) { ?>
                        title: '',
<?php } if ($model->hasAttribute('name')) { ?>
                        name: '',
<?php } if ($model->hasAttribute('status')) { ?>
                        status: this.setting.defaultStatus
<?php } ?>
                    };
                    return true;
                }
                // 选择性初始化
                this.searchForm['id'] = '';
<?php if ($model->hasAttribute('title')) { ?>
                this.searchForm['title'] = '';
<?php } if ($model->hasAttribute('name')) { ?>
                this.searchForm['name'] = '';
<?php } if ($model->hasAttribute('status')) { ?>
                this.searchForm['status'] = this.setting.defaultStatus
<?php } ?>
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
                $.ajax({
                    url: $w.getApiUrl('<?=$generator->getControllerDoID(1)?>.setting'),
                    type: 'get',
                    data: {
                        type: 'index' // 首页
                    },
                    dataType: 'json',
                    success: function (event) {

                        that.$nextTick(function () {
                            // 获取下列表
                            that.getList();
                            // 隐藏正在加载
                            loadingInstance.close();
                        });

                        // 必须先登录
                        if (parseInt(event.no) === 403) {

                            that.$message({
                                type: 'warning',
                                showClose: true,
                                message: '登陆超时，请重新登陆'
                            });

                            // 几秒之后移除
                            return setTimeout(function () {
                                window.parent.location.href = $w.getPageUrl('login');
                            }, 810);
                        }

                        // 操作失败显示错误信息
                        if (parseInt(event.no) !== 200) {

                            return that.$message({
                                type: 'error',
                                showClose: true,
                                message: event.msg
                            });
                        }

                        // 挨个赋值[setting]中
                        for (var i in event.data) {
                            if (!event.data.hasOwnProperty(i)) continue;
                            that.$set(that.setting, i, event.data[i]);
                        }

                        // 最终清空性初始化查询
                        that.initSearchForm(true);
                        // 监测屏幕大小变化
                        return $(window).resize(function() {
                            // 超过此宽度展示 更多筛选
                            var bodyDom = document.getElementsByTagName('body');
                            if (bodyDom[0] &&
                            bodyDom[0].clientWidth <= that.setting.smallScreenWidth)
                            {
                                return that.setting.isSmallScreen = true;
                            }
                            return that.setting.isSmallScreen = false;
                        }).resize();
                    },
                    error: function () {

                        // 按钮正在加载
                        loadingInstance.close();
                        return that.$message({
                            type: 'error',
                            showClose: true,
                            message: '操作频繁，请稍后尝试'
                        });
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
                $.ajax({
                    url: $w.getApiUrl('<?=$generator->getControllerDoID(1)?>.list'),
                    type: 'get',
                    data: {
                        page: this.page,
                        pageSize: this.pageSize,
                        search: this.searchForm
                    },
                    dataType: "json",
                    success: function (event) {

                        that.$nextTick(function () {

                            // 隐藏正在加载
                            loadingInstance.close();
                        });

                        // 必须先登录
                        if (parseInt(event.no) === 403) {

                            that.$message({
                                type: 'warning',
                                showClose: true,
                                message: '登陆超时，请重新登陆'
                            });

                            // 几秒之后移除
                            return setTimeout(function () {
                                window.parent.location.href = $w.getPageUrl('login');
                            }, 810);
                        }

                        // 操作失败显示错误信息
                        if (parseInt(event.no) !== 200) {

                            return that.$message({
                                type: 'error',
                                showClose: true,
                                message: event.msg
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
                    },
                    error: function () {

                        // 按钮正在加载
                        loadingInstance.close();
                        return that.$message({
                            type: 'error',
                            showClose: true,
                            message: '操作频繁，请稍后尝试'
                        });
                    }
                });
            },
            /**
            * [更多查询]按钮点击
            */
            moreSearchClick: function () {
                if(this.setting.showAllSearch) {
                    return this.setting.showAllSearch = false;
                }
                this.setting.showAllSearch = true;
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
             * 恢复记录
             */
            openItem: function ($id) {

                // 如果 $id 不传值 就 走列表
                if (!$id || $id.length <= 0) {$id = this.handelSelectList;}

                // 判断id是否为空
                if (!$id || $id.length <= 0) {
                    return this.$message({
                        showClose: true,
                        type: 'warning',
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
                    $.ajax({
                        url: $w.getApiUrl('<?=$generator->getControllerDoID(1)?>.open'),
                        type: 'POST',
                        data: {idList: $id},
                        dataType: "json",
                        success: function (event) {

                            that.$nextTick(function () {
                                // 隐藏正在加载
                                loadingInstance.close();
                            });

                            // 必须先登录
                            if (parseInt(event.no) === 403) {

                                that.$message({
                                    type: 'warning',
                                    showClose: true,
                                    message: '登陆超时，请重新登陆'
                                });

                                // 几秒之后移除
                                return setTimeout(function () {
                                    window.parent.location.href = $w.getPageUrl('login');
                                }, 810);
                            }

                            // 失败的返回|提示
                            if (parseInt(event.no) !== 200) {

                                return that.$message({
                                    showClose: true,
                                    type: 'error',
                                    message: event.msg
                                });
                            }

                            // 放空列表
                            that.handelSelectList = null;
                            // 成功 加载下列表
                            return that.$nextTick(function () {
                                // 隐藏正在加载
                                that.getList();
                            });
                        },
                        error: function () {

                            that.$nextTick(function () {
                                // 隐藏正在加载
                                loadingInstance.close();
                            });

                            return that.$message({
                                showClose: true,
                                type: 'error',
                                message: '请求用频繁稍后尝试'
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
                        type: 'warning',
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
                    $.ajax({
                        url: $w.getApiUrl('<?=$generator->getControllerDoID(1)?>.disabled'),
                        type: 'POST',
                        data: {idList: $id},
                        dataType: "json",
                        success: function (event) {

                            that.$nextTick(function () {
                                // 隐藏正在加载
                                loadingInstance.close();
                            });

                            // 必须先登录
                            if (parseInt(event.no) === 403) {

                                that.$message({
                                    type: 'warning',
                                    showClose: true,
                                    message: '登陆超时，请重新登陆'
                                });

                                // 几秒之后移除
                                return setTimeout(function () {
                                    window.parent.location.href = $w.getPageUrl('login');
                                }, 810);
                            }

                            // 失败的返回|提示
                            if (parseInt(event.no) !== 200) {

                                return that.$message({
                                    showClose: true,
                                    type: 'error',
                                    message: event.msg
                                });
                            }

                            // 放空列表
                            that.handelSelectList = null;


                            // 成功 加载下列表
                            return that.$nextTick(function () {
                                // 隐藏正在加载
                                that.getList();
                            });
                        },
                        error: function () {

                            that.$nextTick(function () {
                                // 隐藏正在加载
                                loadingInstance.close();
                            });

                            return that.$message({
                                showClose: true,
                                type: 'error',
                                message: '请求用频繁稍后尝试'
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
                if ($row['newSort'] > this.setting.maxSort) {

                    return this.$message({
                        showClose: true,
                        type: 'warning',
                        message: '排序最大不得超过 ' + this.setting.maxSort
                    });
                }

                //是否越出范围值 小于
                if ($row['newSort'] < this.setting.minSort) {

                    return this.$message({
                        showClose: true,
                        type: 'warning',
                        message: '排序最小不得超过 ' + this.setting.minSort
                    });
                }

                // 正在加载。。
                var loadingInstance = ELEMENT.Loading.service({
                    fullscreen: false,
                    text: '排序中...'
                });
                var that = this;

                // 获取各模块的值
                $.ajax({
                    url: $w.getApiUrl('<?=$generator->getControllerDoID(1)?>.sort'),
                    type: 'POST',
                    data: {
                        idList: $row['id'],
                        sort: parseInt($row['newSort'])
                    },
                    dataType: "json",
                    success: function (event) {

                        that.$nextTick(function () {

                            // 隐藏正在加载
                            loadingInstance.close();
                        });

                        // 必须先登录
                        if (parseInt(event.no) === 403) {

                            that.$message({
                                type: 'warning',
                                showClose: true,
                                message: '登陆超时，请重新登陆'
                            });

                            // 几秒之后移除
                            return setTimeout(function () {
                                window.parent.location.href = $w.getPageUrl('login');
                            }, 810);
                        }

                        // 失败的返回|提示
                        if (parseInt(event.no) !== 200) {

                            return that.$message({
                                showClose: true,
                                type: 'error',
                                message: event.msg
                            });
                        }

                        // 成功 加载下列表
                        return that.$nextTick(function () {
                            // 隐藏正在加载
                            that.getList();
                        });
                    },
                    error: function () {

                        that.$nextTick(function () {

                            // 隐藏正在加载
                            loadingInstance.close();
                        });

                        return that.$message({
                            showClose: true,
                            type: 'error',
                            message: '请求用频繁稍后尝试'
                        });
                    }
                });
            },
<?php } ?>
            /**
             * 跳转到添加
             */
            goToCreate: function () {
                // 正在加载。。
                var loadingInstance = ELEMENT.Loading.service({
                    fullscreen: false,
                    text: '跳转中...'
                });
                window.location.href = $w.getPageUrl('<?=$generator->getControllerShowID(1)?>.create');
            },
            /**
             * 跳转到编辑
             */
            goToUpdate: function ($id) {
                // 正在加载。。
                var loadingInstance = ELEMENT.Loading.service({
                    fullscreen: false,
                    text: '跳转中...'
                });
                window.location.href = $w.getPageUrl('<?=$generator->getControllerShowID(1)?>.update', {
                    id: $id
                });
            }
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
            }
        }
    });
};
