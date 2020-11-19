<?php

/* @var $generator wpjCode\wii\generators\crud\Generator */
/* @var $model \yii\db\ActiveRecord */
$model = new $generator->baseModelClass();
$safeAttributes = $model->safeAttributes();
if (empty($safeAttributes)) {
    $safeAttributes = $model->attributes();
}

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
            setting: {
                isAdd: false, // 添加状态
                status: { // 状态列表
                    disabled: -1,
                    normal: 1
                },
                statusTxt: {
                    '-1': '禁用',
                    0: '未审核',
                    1: '开启',
                },
                sortMax: 999999, // 排序允许最大
                sortMin: -999999, // 排序允许最小
            },
            formRules: {
<?php foreach ($safeAttributes as $k => $v) {
    echo <<<EOT
                {$v}: [
                    {required: true, message: '请填写{$model->getAttributeLabel($v)}', trigger: 'blur'}
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
    echo <<<EOT
                {$v}: null
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
                url: $w.getApiUrl('position.setting'),
                type: 'get',
                data: {
                    type: 'index' // 首页
                },
                dataType: 'json',
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

                    // 挨个赋值[setting]中
                    for (var i in event.data) {
                        if (!event.data.hasOwnProperty(i)) continue;
                        that.$set(that.setting, i, event.data[i]);
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
                // 1. 默认状态是 正常
                this.form.status = this.setting.status.normal;

                var params = $w.getParams();

                // id参数存在
                if (!params['id'] || params['id'] === undefined) {

                    loadingInstance.close();
                    this.setting.isAdd = true; // 正在添加
                    return this.loadOver = true;
                }

                this.setting.isAdd = false; // 正在修改

                // 获取各模块的值
                $.ajax({
                    url: $w.getApiUrl('<?=$generator->getControllerDoID(1)?>.detail'),
                    type: 'get',
                    data: {id: params['id']},
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
             * 添加
             */
            submitAdd: function () {

                var that = this;
                // 清空错误信息
                this.$set(that, 'customErrMsg', {});
                this.$refs['ruleForm'].validate(function (valid, msg) {

                    // 验证不过
                    if (!valid) {
                        return false;
                    }

                    // 是否越出范围值 大于
                    if (parseInt(that.form.sort) > that.setting.sortMax) {

                        that.$message({
                            showClose: true,
                            type: 'error',
                            message: '排序最大不得超过 ' + that.setting.sortMax
                        });

                        return that.$set(that.customErrMsg, 'sort',
                            '排序最大不得超过 ' + that.setting.sortMax);
                    }

                    // 是否越出范围值 小于
                    if (parseInt(that.form.sort) < that.setting.sortMin) {

                        that.$message({
                            showClose: true,
                            type: 'error',
                            message: '排序最小不得小于 ' + that.setting.sortMin
                        });

                        return that.$set(that.customErrMsg, 'sort',
                            '排序最小不得小于 ' + that.setting.sortMin);
                    }

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

                                for (var i in event.data.columnError) {
                                    if (!event.data.columnError.hasOwnProperty(i))
                                        continue;
                                    that.$set(that.customErrMsg, i, event.data.columnError[i]);
                                }
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

                var that = this;
                // 清空错误信息
                this.$set(that, 'customErrMsg', {});
                this.$refs['ruleForm'].validate(function (valid, msg) {

                    // 验证不过
                    if (!valid) {return false;}

                    // 是否越出范围值 大于
                    if (parseInt(that.form.sort) > that.setting.sortMax) {

                        that.$message({
                            showClose: true,
                            type: 'error',
                            message: '排序最大不得超过 ' + that.setting.sortMax
                        });

                        return that.$set(that.customErrMsg, 'sort',
                            '排序最大不得超过 ' + that.setting.sortMax);
                    }

                    // 是否越出范围值 小于
                    if (parseInt(that.form.sort) < that.setting.sortMin) {

                        that.$message({
                            showClose: true,
                            type: 'error',
                            message: '排序最小不得小于 ' + that.setting.sortMin
                        });

                        return that.$set(that.customErrMsg, 'sort',
                            '排序最小不得小于 ' + that.setting.sortMin);
                    }

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

                                for (var i in event.data.columnError) {
                                    if (!event.data.columnError.hasOwnProperty(i))
                                        continue;
                                    that.$set(that.customErrMsg, i, event.data.columnError[i]);
                                }
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
