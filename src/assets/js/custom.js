$(function () {
    var selInputVal = '', addInput, addSticky, useAlias = 0, cssName = '', jsName = '';
    // 默认打开先走一下获取资源文件路径
    getJsCssPath();
    $('.can-chose-folder').on('click', function ($dom) {
        addInput = $($dom['target']).siblings('.form-control');
        addSticky = $($dom['target']).siblings('.sticky-value');
        useAlias = $($dom['target']).attr('data-use-alias');
        useAlias = parseInt(useAlias) === 0 ? false : true;
        loadFolder();
    });

    $('#generator-modelclass').on('input', getJsCssPath);

    /**
     * 加载文件夹列表
     */
    function loadFolder() {
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
        selfTool.alert({
            Title: '请选择一个文件',
            Message: '加载中...',
            width: '100%',
            okBtnId: 'choseSpace'
        });

        // 检测确定选择
        $('#choseSpace').on('click', function () {
            // input值赋值
            addInput.val(selInputVal);
            // 展示框值赋值
            addSticky.text(selInputVal);
            // 正在操作的input是[js]
            if ($(addInput).attr('id') === 'generator-jspath') {
                // input值赋值
                addInput.val(selInputVal + '/' + jsName);
                // 展示框值赋值
                addSticky.text(selInputVal + '/' + jsName);
            }
            // 正在操作的input是[css]
            if ($(addInput).attr('id') === 'generator-csspath') {
                // input值赋值
                addInput.val(selInputVal + '/' + cssName);
                // 展示框值赋值
                addSticky.text(selInputVal + '/' + cssName);
            }
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
        if (parseInt(useAlias) === 1) {
            selInputVal = treeNode['nameAlias'];
        } else { // 其他走命名空间
            selInputVal = treeNode['nameSpace']
        }
        console.log('选择了命名空间/别名：' + selInputVal);
    }

    /**
     * 检测[node]点击
     * @param event
     * @param treeId
     * @param treeNode
     */
    function onExpand(event, treeId, treeNode) {
        // var treeObj = $.fn.zTree.getZTreeObj(treeId);
        // console.log(treeObj);
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
        } else if (useAlias && treeNode['nameAlias'] === selVal) {
            // 适用[别名] 则默认点选根据[别名]
            treeObj.selectNode(treeNode);
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
     * 根据基础类获取CSS、JS文件名
     * @returns {boolean}
     */
    function getJsCssPath() {
        var val = $('#generator-controllershowclass').val();
        val = val.split('\\');
        if (!val || val.length < 1) {
            console.error('基础模型的名称无法获取：' + val);
            return false;
        }
        val = val.slice(-1)[0];
        val = val.match(/[A-Z][a-z]+|[0-9][0-9]+/g);
        if (val.slice(-1) && val.slice(-1)[0] === 'Controller') {
            val.pop();
        }
        cssName = (val.join('-')).toLowerCase();
        jsName = (val.join('-')).toLowerCase();
    }
});
