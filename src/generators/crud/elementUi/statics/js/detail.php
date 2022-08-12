<?php

/* @var $generator wpjCode\wii\generators\crud\Generator */
/* @var $model \yii\db\ActiveRecord */
$model = new $generator->baseModelClass();

?>
/**
 * [<?=$generator->expName?>]详情首页[JS]
 * @returns {*}
 */
var app = function () {

    return new Vue({
        el: '#vueContainer',
        data: {
            loadOver: false,
            settingOver: false,
            setting: {
                pageType: 'detail',     // 页面类型
                showAllSearch: false,   // 是否出现[展示全部查询]按钮
                smallScreenWidth: 998,  // 小屏幕临界点(px)
                isSmallScreen: false,   // 是否是小屏幕
                bodyWidth: document.documentElement.clientWidth, // body宽度
            },
            id: $w.getParams('id'), // 编号
            isError: false,         // 是否展示错误
            errorMsg: '',           // 错误内容
            dataList: [],           // 父级数据列表
            handleSelectList: [],   // 当前多选项
            showTopScroll: false,   // 是否已滚动，默认否
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
            // 如果没有[id]展示错误信息
            if (!this.id) {
                this.showErrorPage('条目未找到');
                return this.loadOver = true;
            }
            // 初始化
            this.init();
            // 初始化下设置
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
             * 显示错误页面
             * @param $msg
             */
            showErrorPage: function ($msg) {
                this.isError = true;
                this.errorMsg = $w.isEmpty($msg) ? '页面错误，请稍后尝试...' : $msg;
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
                    showErrorPage: true,
                    beforeCallback: function () {
                        that.$nextTick(function () {
                            // 设置加载完毕
                            that.settingOver = true;
                            // 隐藏正在加载
                            loadingInstance.close();
                            // 获取下列表
                            // that.getDetail();
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

                        // 监测屏幕大小变化
                        return $(window).resize(function () {
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
             * 获取详情
             */
            getDetail: function () {

                // 正在加载...
                var loadingInstance = ELEMENT.Loading.service({
                    fullscreen: false,
                    text: '加载中...'
                });
                var that = this;

                // 获取各模块的值
                $w.request({
                    url: $w.getApiUrl('<?=$generator->getControllerID(1)?>.detail'),
                    type: 'get',
                    data: {id: this.id},
                    dataType: "json",
                    showErrorPage: true,
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

                        // 总条目
                        that.detail = parseInt(event.data);
                    }
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
                }, 1000);
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
