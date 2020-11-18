$(function () {
    var selInputVal = '', addInput, addSticky, useAlias = 0, cssName = '', jsName = '';
    // 默认打开先走一下获取资源文件路径
    getJsCssPath();
    // 点击 选择文件/文件夹
    $('.can-chose-folder').on('click', loadFolder);
    // 点击渲染页面展示按钮
    $('.can-copy-name').on('click', randNameModel);
    // 获取
    $('#generator-modelclass').on('input', getJsCssPath);

    /**
     * 初始化某些东西
     * @param $dom
     */
    function initFunc($dom) {
        addInput = $($dom['target']).parent().siblings('.form-control');
        addSticky = $($dom['target']).parent().siblings('.sticky-value');
        useAlias = $($dom['target']).attr('data-use-alias');
        useAlias = parseInt(useAlias) === 0 ? false : true;
    }

    /**
     * 加载文件夹列表
     */
    function loadFolder($dom) {

        // 初始化点
        initFunc($dom);
        $.post('/wii/default/see-folder', {
            openPath: addInput.val()
        }, function ($response) {

            var html = dataFilter('', '', $response);
            var dom = $("#zTree");
            $.fn.zTree.init(dom, {
                treeId: 'ztree',
                treeObj: dom,
                async: {
                    enable: true,
                    dataType: "JSON",
                    url: "/wii/default/see-folder",
                    autoParam: ["path=parentFolder"],
                    dataFilter: dataFilter
                },
                view: {
                    // addHoverDom: addHoverDom,
                    // removeHoverDom: removeHoverDom,
                    addDiyDom: addDiyDom
                },
                callback: {
                    /**
                     * 检测[node]展开
                     * @param event
                     * @param treeId
                     * @param treeNode
                     */
                    onExpand: onExpand,
                    /**
                     * 检测[node]点击
                     * @param event
                     * @param treeId
                     * @param treeNode
                     */
                    onClick: nodeClick,
                    /**
                     * 检测[node]创建完毕之后
                     * @param event
                     * @param treeId
                     * @param treeNode
                     */
                    onNodeCreated: nodeCreated
                }
            }, html);
        }, 'JSON');
        var doName = $($dom['target']).parent().siblings('label').text();
        selfTool.alert({
            Title: '请选择：【' + doName + '】',
            Message: '加载中...',
            width: '100%',
            okBtnId: 'choseSpace'
        });

        // 检测确定选择
        $('#choseSpace').on('click', function () {

            if (!selInputVal || selInputVal.length < 1) {
                return layer.msg('未选择或者选择的文件/夹为空');
            }
            // 正在操作的input是[js]
            if ($(addInput).attr('id').indexOf('js') !== -1) {
                // input值赋值
                addInput.val(selInputVal + '/' + jsName);
                // 展示框值赋值
                return addSticky.text(selInputVal + '/' + jsName);
            }
            // 正在操作的input是[css]
            if ((addInput).attr('id').indexOf('css') !== -1) {
                // input值赋值
                addInput.val(selInputVal + '/' + cssName);
                // 展示框值赋值
                return addSticky.text(selInputVal + '/' + cssName);
            }
            // input值赋值
            addInput.val(selInputVal);
            // 展示框值赋值
            addSticky.text(selInputVal);
        });
    }

    /**
     * 检测[node]点击
     * @param event
     * @param treeId
     * @param treeNode
     */
    function nodeClick(event, treeId, treeNode) {
        // 需要赋值别名的
        if (useAlias === true || useAlias === 'true' || parseInt(useAlias) === 1) {
            selInputVal = treeNode['nameAlias'];
        } else { // 其他走命名空间
            selInputVal = treeNode['nameSpace']
        }
    }

    /**
     * 检测[node]点击
     * @param event
     * @param treeId
     * @param treeNode
     */
    function onExpand(event, treeId, treeNode) {
        // var treeObj = $.fn.zTree.getZTreeObj(treeId);
        // var newNodes = [{name:"newNode1"}, {name:"newNode2"}, {name:"newNode3"}];
        // treeObj.addNodes(treeNode, newNodes);
    }

    /**
     * 检测[node]创建完毕
     */
    function nodeCreated(event, treeId, treeNode) {

        var treeObj = $.fn.zTree.getZTreeObj(treeId);
        var selVal = $(addInput).val();
        // 不适用[别名] 则默认点选根据[命名空间]
        if (!useAlias && treeNode['nameSpace'] === selVal) {
            treeObj.selectNode(treeNode);
            // 最后调用下点击
            nodeClick(event, treeId, treeNode);
        } else if (useAlias && treeNode['nameAlias'] === selVal) {
            // 适用[别名] 则默认点选根据[别名]
            treeObj.selectNode(treeNode);
            // 最后调用下点击
            nodeClick(event, treeId, treeNode);
        }
    }

    /**
     * 过滤、格式化异步节点返回数据
     * @param treeId
     * @param parentNode
     * @param $response
     * @returns {Array}
     */
    function dataFilter(treeId, parentNode, $response) {
        var html = [];
        var htmlItem = [];
        for (var i in $response.data) {
            htmlItem = {
                pid: $response.data[i]['name'],
                name: $response.data[i]['name'],
                isParent: true,
                isLoadedChildren: false,
                nameSpace: $response.data[i]['nameSpace'],
                path: $response.data[i]['path'],
                nameAlias: $response.data[i]['nameAlias']
            };

            // 非文件夹
            if (parseInt($response.data[i]['isFolder']) !== 1) {
                htmlItem.isParent = false;
            }

            // 子集有数据的话，仿造[response]数据返回打开
            if ($response.data[i]['children']) {
                htmlItem['children'] = dataFilter(false, false, {
                    data: $response.data[i]['children']
                });
                htmlItem['open'] = true;
            }

            // 不适用别名 命名空间相同 则默认点选
            if (!useAlias && htmlItem['nameSpace'] === selInputVal) {
                htmlItem['isHover'] = true;
            } else if (useAlias && htmlItem['nameAlias'] === selInputVal) {
                // 适用别名 别相同 则默认点选
                htmlItem['isHover'] = true;
            }

            html.push(htmlItem);

        }

        return html;
    }

    /**
     * 用于在节点上固定显示用户自定义控件
     */
    function addDiyDom($treeId, $treeNode) {
        if (!$treeNode.isParent) {
            return false;
        }
        var nodeIdStr = $treeNode.tId;
        var addIdStr = $treeNode.tId + '_a';
        var reloadIdStr = 'reloadBtn_' + $treeNode.tId;
        var editStr =
            "<span class='reload-icon' id='" + reloadIdStr + "' " +
            "data-node-id='" + nodeIdStr + "' " +
            "title='刷新文件'>" +
            "</span>";

        $("#" + addIdStr).after(editStr);
        $('#' + reloadIdStr).on('click', reloadNode);
    }

    /**
     * 重新新加载当前[NODE]下文件列表
     * @param $dom
     */
    function reloadNode($dom) {

        var nodeId = $($dom['target']).attr('data-node-id');
        var zTree = $.fn.zTree.getZTreeObj("zTree"),
            type = "refresh",
            silent = false;
        /*根据 zTree 的唯一标识 tId 快速获取节点 JSON 数据对象*/
        var parentNode = zTree.getNodeByTId(nodeId);

        if (!parentNode) {
            alert('未找到此节点');
            return false;
        }
        /*选中指定节点*/
        zTree.selectNode(parentNode);
        zTree.reAsyncChildNodes(parentNode, type, silent);
    }

    /**
     * 根据[基础类路径]生成名称
     */
    function randNameModel($dom) {

        // 初始化点
        initFunc($dom);
        // 先获取 基础模块 名称
        var modelName = $('#generator-basemodelclass').val();
        // 获取最后名称
        modelName = modelName.split('\\').pop();
        modelName = modelName.replace('Model', '');
        if (!modelName || modelName.length < 1) {
            layer.msg('请先填写[基础类路径]', {
                offset: document.body.clientHeight - 100 + 'px'
            });
            return false;
        }

        // 最终生成的
        var endName = $(addInput).val();
        // 点击的[input]的[id]
        var inputId = $(addInput).attr('id');
        if (inputId.indexOf('model') !== -1) {
            if (endName.charAt(endName.length - 1) !== '\\' &&
                endName.charAt(endName.length - 1) !== '/'
            )
                endName = endName + (useAlias ? '/' : '\\');
            endName = endName + modelName + 'Model';
        } else if (inputId.indexOf('controller') !== -1) {
            if (endName.charAt(endName.length - 1) !== '\\' &&
                endName.charAt(endName.length - 1) !== '/')
                endName = endName + (useAlias ? '/' : '\\');
            endName = endName + modelName + 'Controller';
        } else if (inputId.indexOf('js') !== -1 || inputId.indexOf('view') !== -1 || inputId.indexOf('css') !== -1) {
            if (endName.charAt(endName.length - 1) !== '\\' &&
                endName.charAt(endName.length - 1) !== '/')
                endName = endName + (useAlias ? '/' : '\\');
            endName = endName + getJsCssPath();
        }
        var layerId = layer.confirm('自动生成的文件名为<code>' + endName + '</code>是否继续？', {
            title: '确认操作',
            btn: ['确定', '取消']
        }, function() {
            $(addInput).val(endName).change();
            $(addInput).siblings('.sticky-value').text(endName);
            layer.close(layerId);
        });
    }
    /**
     * 根据基础类获取CSS、JS文件名
     * @returns {boolean}
     */
    function getJsCssPath() {
        var val = $('#generator-controllershowclass').val();
        if (!val || val.length < 1) {
            return console.error('基础模型的名称无法获取：' + val);
        }
        val = val.split('\\');
        val = val.slice(-1)[0];
        val = val.match(/[A-Z][a-z]+|[0-9][0-9]+/g);
        if (val.slice(-1) && (val.slice(-1)[0] === 'Controller')) {
            val.pop();
        }
        // cssName = (val.join('-')).toLowerCase();
        // jsName = (val.join('-')).toLowerCase();
        return (val.join('-')).toLowerCase();
    }
});
