<?php

/* @var $generator wpjCode\wii\generators\crud\Generator */
/* @var $model \yii\db\ActiveRecord */
$model = new $generator->baseModelClass();
$safeAttributes = $generator->getTableSchema()->columns;

?>
/**
 * [<?=$generator->expName?>]表单[JS]
 * @returns {*}
 */
var app = function () {

    return new Vue({
        el: '#vueContainer',
        data: {
            loadOver: false,
            detailOver: false,
            settingOver: false,
            setting: {
                pageType: 'form', // 页面类型
                isCreate: false, // 添加状态
            },
            formRules: {
<?php foreach ($safeAttributes as $k => $v) {
    // 键略过
    if ($v->isPrimaryKey) continue;
    // 允许空略过
    if ($v->allowNull) continue;
    echo <<<EOT
                {$v->name}: [
                    {required: true, message: '请完善{$model->getAttributeLabel($v->name)}', trigger: 'blur'}
                ]
EOT;
    if ($k < (count($safeAttributes) -1)) {
        echo ",\n";
    } else {
        echo "\n";
    }
}?>
            },
            form: {
<?php foreach ($safeAttributes as $k => $v) {
    // 键略过
    if ($v->isPrimaryKey) continue;
    $defaultVal = 'null';
    // 默认是数字
    if ($v->phpType == 'integer') $defaultVal = '0';
    echo <<<EOT
                {$v->name}: {$defaultVal}
EOT;
    if ($k < (count($safeAttributes) -1)) {
        echo ",\n";
    } else {
        echo "\n";
    }
}?>
            },
            customErrMsg: {} // 自定义错误列表
        },
        created: function () {

            // 初始化下设置
            this.getSetting();
            // 初始化、获取详情
            this.init();
            var that = this;
            this.$nextTick(function () {
                that.loadOver = true;
            });
        },
        methods: {
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
                        type: 'form' // 表单页
                    },
                    dataType: 'json',
                    success: function (event) {

                        that.$nextTick(function () {
                            // 设置加载完毕
                            that.settingOver = true;
                            // 隐藏正在加载
                            loadingInstance.close();
                        });

                        // 必须先登录
                        if (parseInt(event.no) === 401) {

                            that.$message({
                                type: 'warning',
                                showClose: true,
                                message: '登录超时，请重新登录'
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

                        // 挨个赋值[setting]中 && 默认值
                        for (var i in event.data) {
                            if (!event.data.hasOwnProperty(i)) continue;
                            that.$set(that.setting, i, event.data[i]);
                            // 不存在指定字符串直接返回
                            if (i.indexOf('default_') === -1 || !that.setting.isCreate) continue;
                            that.form[i.replace('default_', '')] = event.data[i];
                        }
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
             * 获取下详细信息
             */
            init: function () {

                // 正在加载。。
                var loadingInstance = ELEMENT.Loading.service({
                    fullscreen: false,
                    text: '获取中...'
                });
                var that = this;

                var params = $w.getParams();

                // id参数存在
                if (!params['id'] || params['id'] === undefined) {

                    this.detailOver = true; // 详情加载完毕
                    this.setting.isCreate = true; // 正在添加
                    return loadingInstance.close();
                }

                this.setting.isCreate = false; // 正在修改

                // 获取各模块的值
                $.ajax({
                    url: $w.getApiUrl('<?=$generator->getControllerDoID(1)?>.detail'),
                    type: 'get',
                    data: {id: params['id']},
                    dataType: "json",
                    success: function (event) {

                        that.$nextTick(function () {
                            // 详情加载完毕
                            that.detailOver = true;
                            // 隐藏正在加载
                            loadingInstance.close();
                        });

                        // 必须先登录
                        if (parseInt(event.no) === 401) {

                            that.$message({
                                type: 'warning',
                                showClose: true,
                                message: '登录超时，请重新登录'
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

                        that.form = event.data;
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
             * 取消 添加修改返回上一页
             */
            cancel: function () {
                // 正在加载。。
                var loadingInstance = ELEMENT.Loading.service({
                    fullscreen: false,
                    text: '返回中...'
                });
                window.history.back();
            },
            /**
             * 去首页
             */
            goToIndex: function () {
                // 父级
                var parent = window.parent.window;
                if (!parent) return false;

                // 父级[vue]对象
                var vueInstance = window.parent.window.menu;
                if (!parent || !(typeof vueInstance === 'object')) return false;

                // 键值
                var key = vueInstance.indexKey;
                // 操作点击
                $(window.parent.window.document).find('#tab-' + key).click();
            },
            /**
             * 添加
             */
            submitCreate: function () {

                // 强制关闭下全部弹出层
                this.$message.closeAll();
                // 清空错误信息
                this.$refs['ruleForm'].clearValidate();

                var that = this;
                // 清空错误信息
                this.$set(that, 'customErrMsg', {});
                this.$refs['ruleForm'].validate(function (valid, msg) {

                    // 验证不过 - 滚动到错误字段
                    if (!valid) {
                        var first = $w.array_first_key(msg);
                        return $w.scrollToFormItem(false, first);
                    }

<?php if ($model->hasAttribute('sort')) {?>
                    // 是否越出范围值 大于
                    if (parseInt(that.form.sort) > that.setting.maxSort) {

                        that.$message({
                            showClose: true,
                            type: 'error',
                            message: '排序最大不得超过 ' + that.setting.maxSort
                        });

                        return that.$set(that.customErrMsg, 'sort',
                            '排序最大不得超过 ' + that.setting.maxSort);
                    }

                    // 是否越出范围值 小于
                    if (parseInt(that.form.sort) < that.setting.minSort) {

                        that.$message({
                            showClose: true,
                            type: 'error',
                            message: '排序最小不得小于 ' + that.setting.minSort
                        });

                        return that.$set(that.customErrMsg, 'sort',
                            '排序最小不得小于 ' + that.setting.minSort);
                    }
<?php } ?>
                    // 正在加载。。
                    var loadingInstance = ELEMENT.Loading.service({
                        fullscreen: false,
                        text: '添加中...'
                    });

                    $.ajax({
                        url: $w.getApiUrl('<?=$generator->getControllerDoID(1)?>.create'),
                        type: 'POST',
                        data: that.form,
                        dataType: "json",
                        success: function (event) {

                            that.$nextTick(function () {

                                // 隐藏正在加载
                                loadingInstance.close();
                            });

                            // 必须先登录
                            if (parseInt(event.no) === 401) {

                                that.$message({
                                    type: 'warning',
                                    showClose: true,
                                    message: '登录超时，请重新登录'
                                });

                                // 几秒之后移除
                                return setTimeout(function () {
                                    window.parent.location.href = $w.getPageUrl('login');
                                }, 810);
                            }

                            // 操作失败显示错误信息
                            if (parseInt(event.no) !== 200) {

                                for (var i in event.data.column_error) {
                                    if (!event.data.column_error.hasOwnProperty(i))
                                        continue;
                                    that.$set(that.column_error, i, event.data.column_error[i]);
                                }
                                // 滚动到错误字段
                                $w.scrollToFormItem();
                                return that.$message({
                                    type: 'error',
                                    showClose: true,
                                    message: event.msg
                                });
                            }

                            that.cancel();
                        },
                        error: function (event) {

                            // 按钮正在加载
                            that.$nextTick(function () {

                                // 隐藏正在加载
                                loadingInstance.close();
                            });
                            return that.$message({
                                type: 'error',
                                showClose: true,
                                message: '操作频繁，请稍后尝试'
                            });
                        }
                    });
                });
            },
            /**
             * 修改操作
             */
            submitUpdate: function () {

                // 强制关闭下全部弹出层
                this.$message.closeAll();
                // 清空错误信息
                this.$refs['ruleForm'].clearValidate();

                var that = this;
                // 清空错误信息
                this.$set(that, 'customErrMsg', {});
                this.$refs['ruleForm'].validate(function (valid, msg) {

                    // 验证不过 - 滚动到错误字段
                    if (!valid) {
                        var first = $w.array_first_key(msg);
                        return $w.scrollToFormItem(false, first);
                    }

<?php if ($model->hasAttribute('sort')) {?>
                    // 是否越出范围值 大于
                    if (parseInt(that.form.sort) > that.setting.maxSort) {

                        that.$message({
                            showClose: true,
                            type: 'error',
                            message: '排序最大不得超过 ' + that.setting.maxSort
                        });

                        return that.$set(that.customErrMsg, 'sort',
                            '排序最大不得超过 ' + that.setting.maxSort);
                    }

                    // 是否越出范围值 小于
                    if (parseInt(that.form.sort) < that.setting.minSort) {

                        that.$message({
                            showClose: true,
                            type: 'error',
                            message: '排序最小不得小于 ' + that.setting.minSort
                        });

                        return that.$set(that.customErrMsg, 'sort',
                            '排序最小不得小于 ' + that.setting.minSort);
                    }
<?php } ?>

                    // 正在加载。。
                    var loadingInstance = ELEMENT.Loading.service({
                        fullscreen: false,
                        text: '更新中...'
                    });

                    $.ajax({
                        url: $w.getApiUrl('<?=$generator->getControllerDoID(1)?>.update'),
                        type: 'POST',
                        data: that.form,
                        dataType: "json",
                        success: function (event) {

                            that.$nextTick(function () {

                                // 隐藏正在加载
                                loadingInstance.close();
                            });

                            // 必须先登录
                            if (parseInt(event.no) === 401) {

                                that.$message({
                                    type: 'warning',
                                    showClose: true,
                                    message: '登录超时，请重新登录'
                                });

                                // 几秒之后移除
                                return setTimeout(function () {
                                    window.parent.location.href = $w.getPageUrl('login');
                                }, 810);
                            }

                            // 操作失败显示错误信息
                            if (parseInt(event.no) !== 200) {

                                for (var i in event.data.column_error) {
                                    if (!event.data.column_error.hasOwnProperty(i))
                                        continue;
                                    that.$set(that.customErrMsg, i, event.data.column_error[i]);
                                }
                                // 滚动到错误字段
                                $w.scrollToFormItem();
                                return that.$message({
                                    type: 'error',
                                    showClose: true,
                                    message: event.msg
                                });
                            }

                            that.cancel();
                        },
                        error: function (event) {

                            that.$nextTick(function () {
                                // 隐藏正在加载
                                loadingInstance.close();
                            });
                            return that.$message({
                                type: 'error',
                                showClose: true,
                                message: '操作频繁，请稍后尝试'
                            });
                        }
                    });
                });
            },
        }
    });
};
