<?php

/* @var $generator wpjCode\wii\generators\crud\Generator */
/* @var $model \yii\db\ActiveRecord */
$model = new $generator->baseModelClass();
$safeAttributes = $generator->getTableSchema()->columns;

?>
/**
 * [<?=$generator->expName?>]表单[JS]
 * @param $isCreate 是否新建
 * @returns {*}
 */
var app = function ($isCreate) {

    return new Vue({
        el: '#vueContainer',
        data: {
            loadOver: false,    // 页面加载状态
            detailOver: false,  // 详情加载状态
            settingOver: false, // 设置加载状态
            setting: {
                pageType: 'form',    // 页面类型
                isCreate: $isCreate, // 添加状态
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
            customErrMsg: {}, // 自定义错误列表
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
            // 初始化、获取详情
            this.initDetail();
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
                        type: 'form' // 表单页
                    },
                    dataType: 'json',
                    beforeCallback: function () {
                        that.$nextTick(function () {
                            // 设置加载完毕
                            that.settingOver = true;
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

                        // 挨个赋值[setting]中 && 默认值
                        var filters = ['value_need_opt_list'];
                        for (var i in event.data) {
                            if (!event.data.hasOwnProperty(i)) continue;
                            that.$set(that.setting, i, event.data[i]);

                            // 在过滤列表中进行过滤
                            if ($w.in_array(i, filters)) continue;
                            // 改变键值
                            if (i.indexOf('_list') !== -1) {
                                that.setting[i] = $w.array_index(event.data[i], 'key');
                            }

                            // 默认值
                            if (i.indexOf('default_') !== -1 && that.setting.isCreate) {
                                that.form[i.replace('default_', '')] = that.setting[i];
                            }
                        }
                    }
                });
            },
            /**
             * 获取下详细信息
             */
            initDetail: function () {

                // 正在加载。。
                var loadingInstance = ELEMENT.Loading.service({
                    fullscreen: false,
                    text: '获取中...'
                });
                var that = this;

                // 是新建
                if ($isCreate) {

                    this.detailOver = true; // 详情加载完毕
                    return loadingInstance.close();
                }

                // 获取各模块的值
                $w.request({
                    url: $w.getApiUrl('<?=$generator->getControllerID(1)?>.detail'),
                    type: 'get',
                    data: {id: $w.getParams('id')},
                    dataType: "json",
                    beforeCallback: function () {
                        that.$nextTick(function () {
                            // 详情加载完毕
                            that.detailOver = true;
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

                        that.form = event.data;
                    }
                });
            },
            /**
             * 返回上一页
             * @param $reloadList 需要加载列表
             */
            cancel: function ($reloadList) {

                // 需要重新加载下列表
                if ($reloadList && this.pageDialog.isIframe) {
                    window.parent.instance.getList();
                }

                // 显示脚部 - 当前页面返回
                if (!this.pageDialog.isIframe) {
                    // 非嵌入页面暂不操作 - 可根据逻辑自行修改
                    // // 正在加载。。
                    // var loadingInstance = ELEMENT.Loading.service({
                    //     fullscreen: false,
                    //     text: '返回中...'
                    // });
                    // window.history.back();
                } else {
                    window.parent.instance.pageDialog.show = false;
                }
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

                    // 正在加载。。
                    var loadingInstance = ELEMENT.Loading.service({
                        fullscreen: false,
                        text: '添加中...'
                    });

                    $w.request({
                        url: $w.getApiUrl('<?=$generator->getControllerID(1)?>.create'),
                        type: 'POST',
                        data: that.form,
                        dataType: "json",
                        beforeCallback: function () {
                            that.$nextTick(function () {
                                // 隐藏正在加载
                                loadingInstance.close();
                            });
                        },
                        callback: function (event) {

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

                            // 返回上一页
                            that.cancel(true);
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

                    // 正在加载。。
                    var loadingInstance = ELEMENT.Loading.service({
                        fullscreen: false,
                        text: '更新中...'
                    });

                    $w.request({
                        url: $w.getApiUrl('<?=$generator->getControllerID(1)?>.update'),
                        type: 'POST',
                        data: that.form,
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
                                for (var i in event.data.column_error) {
                                    if (!event.data.column_error.hasOwnProperty(i))
                                        continue;
                                    that.$set(that.customErrMsg, i, event.data.column_error[i]);
                                }
                                // 滚动到错误字段
                                $w.scrollToFormItem();
                                return that.$message({
                                    showClose: true,
                                    type: 'error',
                                    message: event.msg ? event.msg : '操作失败，请稍后尝试'
                                });
                            }

                            // 返回上一页
                            that.cancel(true);
                        }
                    });
                });
            },
        },
        computed: {
            /**
             * 表单验证规则
             */
            formRules: function () {

                if (!this.settingOver || !this.detailOver) return {};
                return {
<?php foreach ($safeAttributes as $k => $v) {
    // 键略过
    if ($v->isPrimaryKey) continue;
    // 头
    echo <<<EOT
                    {$v->name}: [
EOT;
    // 允许空略过
    if (!$v->allowNull) {
        echo <<<EOT
        
                        {required: true, message: '请完善排序', trigger: 'blur'},
EOT;
    }
    // 排序字段
if ($v->name == 'sort' || $v->name == 'list_order') {
    echo <<<EOT
    
                        {
                            validator: \$w.validateNumRange, message: false, trigger: 'blur',
                            max: this.setting.max_sort, min: this.setting.min_sort
                        },
EOT;
}
if (property_exists($v, 'phpType') && $v->phpType == 'integer' && $v->size > 2) {
        echo <<<EOT
        
                        {type: 'number', message: '{$model->getAttributeLabel($v->name)}必须为数字值', trigger: 'blur'}
EOT;
    }
    if (property_exists($v, 'phpType') && $v->phpType == 'string' && !strstr($v->name, 'time') && !strstr($v->name, 'image') && !strstr($v->name, 'avatar')) {
        echo <<<EOT
        
                        {max: $v->size, message: '{$model->getAttributeLabel($v->name)}长度最多 $v->size 个字', trigger: 'blur'},
EOT;
    }
    echo <<<EOT
    
                    ]
EOT;
    if ($k < (count($safeAttributes) -1)) {
        echo ",\n";
    } else {
        echo "\n";
    }
}?>
                };
            }
        }
    });
};
