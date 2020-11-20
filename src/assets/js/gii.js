yii.gii = (function ($) {
    var selInputVal = '',
        addInput,
        addSticky,
        useAlias = 0,
        cssName = '',
        jsName = '';
    var $clipboardContainer = $("#clipboard-container"),
        valueToCopy = '',
        ajaxRequest = null,
        doFileName = '',
        onKeydown = function (e) {
            var $target;
            $target = $(e.target);

            if ($target.is("input:visible, textarea:visible")) {
                return;
            }

            if (typeof window.getSelection === "function" && window.getSelection().toString()) {
                return;
            }

            if (document.selection != null && document.selection.createRange().text) {
                return;
            }

            $clipboardContainer.empty().show();
            return $("<textarea id='clipboard'></textarea>").val(valueToCopy).appendTo($clipboardContainer).focus().select();
        },
        onKeyup = function (e) {
            if ($(e.target).is("#clipboard")) {
                $("#clipboard-container").empty().hide();
            }
            return true;
        };

    var initStickyInputs = function () {
        $('.sticky:not(.error)').find('input[type="text"],select,textarea').each(function () {
            var value,
                element = document.createElement('div');
            if (this.tagName === 'SELECT') {
                value = this.options[this.selectedIndex].text;
            } else if (this.tagName === 'TEXTAREA') {
                value = $(this).html();
            } else {
                value = $(this).val();
            }
            if (value === '') {
                value = '[empty]';
            }
            element.classList.add('sticky-value');
            element.title = value;
            element.innerHTML = value;
            new Tooltip(element, {placement: 'right'});
            $(this).before(element).hide();
        });
        $('.sticky-value').on('click', function () {
            $(this).hide();
            $(this).next().show().get(0).focus();
        });
    };

    var fillModal = function ($link, data) {
        var $modal = $('#preview-modal'),
            $modalBody = $modal.find('.modal-body');
        if (!$link.hasClass('modal-refresh')) {
            var filesSelector = 'a.' + $modal.data('action') + ':visible';
            var $files = $(filesSelector);
            var index = $files.filter('[href="' + $link.attr('href') + '"]').index(filesSelector);
            var $prev = $files.eq(index - 1);
            var $next = $files.eq((index + 1 == $files.length ? 0 : index + 1));
            $modal.data('current', $files.eq(index));
            $modal.find('.modal-previous').attr('href', $prev.attr('href')).data('title', $prev.data('title'));
            $modal.find('.modal-next').attr('href', $next.attr('href')).data('title', $next.data('title'));
        }
        // 只有查看代码初始化行号
        if ($link.hasClass('preview-code')) {
            data = '<pre class="content">' + '<code>' + data + '</code>' + '</div>';
        }
        $modalBody.html(data);
        valueToCopy = $("<div/>").html(data.replace(/(<(br[^>]*)>)/ig, '\n').replace(/&nbsp;/ig, ' ')).text().trim() + '\n';
        // 只有查看代码才有特殊样式
        if ($link.hasClass('preview-code')) {
            $modal.find('.content').css({
                maxHeight: ($(window).height() - 200) + 'px',
                overflow: 'scroll'
            });
        } else {
            $modal.find('.content').css({
                maxHeight: ($(window).height() - 200) + 'px',
            });
        }

        // 最终调用下语法高亮
        document.querySelectorAll('pre code').forEach((block) => {
            hljs.highlightBlock(block);
            hljs.initLineNumbersOnLoad();
        });
    };

    var initPreviewDiffLinks = function () {
        $('.preview-code, .diff-code, .modal-refresh, .modal-previous, .modal-next').on('click', function () {
            if (ajaxRequest !== null) {
                if ($.isFunction(ajaxRequest.abort)) {
                    ajaxRequest.abort();
                }
            }
            var that = this;
            var $modal = $('#preview-modal');
            var $link = $(this);
            if ($link.hasClass('preview-code')) {
                // 复制下当前操作文件名
                doFileName = $link.attr('data-title');
            } else if ($link.hasClass('diff-code')) {
                // 复制下当前操作文件名
                doFileName = $link.attr('data-title');
            }
            $modal.find('.modal-refresh').attr('href', $link.attr('href'));
            if ($link.hasClass('preview-code') || $link.hasClass('diff-code')) {
                $modal.data('action', ($link.hasClass('preview-code') ? 'preview-code' : 'diff-code'))
            }
            $modal.find('.modal-title').text($link.data('title'));
            $modal.find('.modal-body').html('Loading ...');

            var modalInitJs = new Modal($modal[0]);
            modalInitJs.show();

            var checkbox = $('a.' + $modal.data('action') + '[href="' + $link.attr('href') + '"]').closest('tr').find('input').get(0);
            var checked = false;
            if (checkbox) {
                checked = checkbox.checked;
                $modal.find('.modal-checkbox').removeClass('disabled');
            } else {
                $modal.find('.modal-checkbox').addClass('disabled');
            }
            $modal.find('.modal-checkbox').toggleClass('checked', checked).toggleClass('unchecked', !checked);

            ajaxRequest = $.ajax({
                type: 'POST',
                cache: false,
                url: $link.prop('href'),
                data: $('.default-view form').serializeArray(),
                success: function (data) {
                    fillModal($(that), data);
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    $modal.find('.modal-body').html('<div class="error">' + XMLHttpRequest.responseText + '</div>');
                }
            });
            return false;
        });

        $('#preview-modal').on('keydown', function (e) {
            if (e.keyCode === 37) {
                $('.modal-previous').trigger('click');
            } else if (e.keyCode === 39) {
                $('.modal-next').trigger('click');
            } else if (e.keyCode === 82) {
                $('.modal-refresh').trigger('click');
            } else if (e.keyCode === 32) {
                $('.modal-checkbox').trigger('click');
            }
        });

        $('.modal-checkbox').on('click', checkFileToggle);
    };

    var checkFileToggle = function () {
        var $modal = $('#preview-modal');
        var $checkbox = $modal.data('current').closest('tr').find('input');
        var checked = !$checkbox.prop('checked');
        $checkbox.trigger('click');
        $modal.find('.modal-checkbox').toggleClass('checked', checked).toggleClass('unchecked', !checked);
        return false;
    };

    var checkAllToggle = function () {
        $('#check-all').prop('checked', !$('.default-view-files table .check input:enabled:not(:checked)').length);
    };

    var initConfirmationCheckboxes = function () {
        var $checkAll = $('#check-all');
        $checkAll.click(function () {
            $('.default-view-files table .check input:enabled').prop('checked', this.checked);
        });
        $('.default-view-files table .check input').click(function () {
            checkAllToggle();
        });
        checkAllToggle();
    };

    var initToggleActions = function () {
        $('#action-toggle').find(':input').change(function () {
            $(this).parent('label').toggleClass('active', this.checked);
            var $rows = $('.' + this.value, '.default-view-files table').toggleClass('action-hidden', !this.checked);
            if (this.checked) {
                $rows.not('.filter-hidden').show();
            } else {
                $rows.hide();
            }
            $rows.find('.check input').attr('disabled', !this.checked);
            checkAllToggle();
        });
    };

    var initFilterRows = function () {
        $('#filter-input').on('input', function () {
            var that = this,
                $rows = $('#files-body').find('tr');

            $rows.hide().toggleClass('filter-hidden', true).filter(function () {
                return $(this).text().toUpperCase().indexOf(that.value.toUpperCase()) > -1;
            }).toggleClass('filter-hidden', false).not('.action-hidden').show();

            $rows.find('input').each(function () {
                $(this).prop('disabled', $(this).is(':hidden'));
            });
        });
    };

    /**
     * 初始化某些东西
     * @param $dom
     */
    var initFunc = function ($dom) {
        addInput = $($dom['target']).parent().siblings('.form-control');
        addSticky = $($dom['target']).parent().siblings('.sticky-value');
        useAlias = $($dom['target']).attr('data-use-alias');
        useAlias = parseInt(useAlias) === 0 ? false : true;
    };

    /**
     * 过滤、格式化异步节点返回数据
     * @param treeId
     * @param parentNode
     * @param $response
     * @returns {Array}
     */
    var nodeFilter = function (treeId, parentNode, $response) {
        var html = [];
        var htmlItem = [];
        for (var i in $response.data) {
            if (!$response.data.hasOwnProperty(i)) continue;
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
                htmlItem['children'] = nodeFilter(false, false, {
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
    };

    /**
     * 用于在节点上固定显示用户自定义控件
     */
    var addDiyDom = function ($treeId, $treeNode) {
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
    };

    /**
     * 重新新加载当前[NODE]下文件列表
     * @param $dom
     */
    var reloadNode = function ($dom) {

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
    };

    /**
     * 检测[node]点击
     * @param event
     * @param treeId
     * @param treeNode
     */
    var onExpand = function (event, treeId, treeNode) {
        // var treeObj = $.fn.zTree.getZTreeObj(treeId);
        // var newNodes = [{name:"newNode1"}, {name:"newNode2"}, {name:"newNode3"}];
        // treeObj.addNodes(treeNode, newNodes);
    };

    /**
     * 检测[node]创建完毕
     */
    var nodeCreated = function (event, treeId, treeNode) {

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
    };

    /**
     * 检测[node]点击
     * @param event
     * @param treeId
     * @param treeNode
     */
    var nodeClick = function (event, treeId, treeNode) {
        // 需要赋值别名的
        if (useAlias === true || useAlias === 'true' || parseInt(useAlias) === 1) {
            selInputVal = treeNode['nameAlias'];
        } else { // 其他走命名空间
            selInputVal = treeNode['nameSpace']
        }
    };

    /**
     * 根据基础类获取CSS、JS文件名
     * @returns {boolean}
     */
    var getJsCssPath = function () {
        var val = $('#generator-basemodelclass').val();
        if (!val || val.length < 1) {
            console.error('基础模型的名称无法获取：' + val);
            return layer.msg('请选择基础类文件');
        }
        val = val.split('\\');
        val = val.slice(-1)[0];
        val = val.match(/[A-Z][a-z]+|[0-9][0-9]+/g);
        if (val && val.slice(-1) && (val.slice(-1)[0] === 'Model')) {
            val.pop();
        }
        if (val && val.slice(-1) && (val.slice(-1)[0] === 'Controller')) {
            val.pop();
        }
        if (!val || val.length < 1) {
            return layer.msg('请选择基础类文件，必须是一个具体文件');
        }
        return (val.join('-')).toLowerCase();
    };

    /**
     * 确定选择文件夹
     * @returns {*}
     */
    var selectSureFolder = function () {

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
    };

    $(document).on("keydown", function (e) {
        if (valueToCopy && (e.ctrlKey || e.metaKey) && (e.which === 67)) {
            return onKeydown(e);
        }
    }).on("keyup", onKeyup);

    // 默认打开先走一下获取资源文件路径
    var copyList = $('.cpy-some');
    if (copyList && copyList.length > 1) {
        getJsCssPath();
    }

    return {
        init: function () {
            initStickyInputs();
            initPreviewDiffLinks();
            initConfirmationCheckboxes();
            initToggleActions();
            initFilterRows();

            // model generator: hide class name inputs and show psr class name checkbox
            // when table name input contains *
            $('#model-generator #generator-tablename').change(function () {
                var show = ($(this).val().indexOf('*') === -1);
                $('.field-generator-modelclass').toggle(show);
                if ($('#generator-generatequery').is(':checked')) {
                    $('.field-generator-queryclass').toggle(show);
                }
                $('.field-generator-caseinsensitive').toggle(!show);
            }).change();

            // model generator: translate table name to model class
            $('#model-generator #generator-tablename').on('blur', function () {
                var tableName = $(this).val();
                var tablePrefix = $(this).attr('table_prefix') || '';
                if (tablePrefix.length) {
                    // if starts with prefix
                    if (tableName.slice(0, tablePrefix.length) === tablePrefix) {
                        // remove prefix
                        tableName = tableName.slice(tablePrefix.length);
                    }
                }
                if ($('#generator-modelclass').val() === '' && tableName && tableName.indexOf('*') === -1) {
                    var modelClass = '';
                    $.each(tableName.split(/\.|\_/), function () {
                        if (this.length > 0)
                            modelClass += this.substring(0, 1).toUpperCase() + this.substring(1);
                    });
                    $('#generator-modelclass').val(modelClass).blur();
                }
            });

            // model generator: translate model class to query class
            $('#model-generator #generator-modelclass').on('blur', function () {
                var modelClass = $(this).val();
                if (modelClass !== '') {
                    var queryClass = $('#generator-queryclass').val();
                    if (queryClass === '') {
                        queryClass = modelClass + 'Query';
                        $('#generator-queryclass').val(queryClass);
                    }
                }
            });

            // model generator: synchronize query namespace with model namespace
            $('#model-generator #generator-ns').on('blur', function () {
                var stickyValue = $('#model-generator .field-generator-queryns .sticky-value');
                var input = $('#model-generator #generator-queryns');
                if (stickyValue.is(':visible') || !input.is(':visible')) {
                    var ns = $(this).val();
                    stickyValue.html(ns);
                    input.val(ns);
                }
            });

            // model generator: toggle query fields
            $('form #generator-generatequery').change(function () {
                $('form .field-generator-queryns').toggle($(this).is(':checked'));
                $('form .field-generator-queryclass').toggle($(this).is(':checked'));
                $('form .field-generator-querybaseclass').toggle($(this).is(':checked'));
                $('#generator-queryclass').prop('disabled', $(this).is(':not(:checked)'));
            }).change();

            // hide message category when I18N is disabled
            $('form #generator-enablei18n').change(function () {
                $('form .field-generator-messagecategory').toggle($(this).is(':checked'));
            }).change();

            // hide Generate button if any input is changed
            $('#form-fields').find('input,select,textarea').change(function () {
                $('.default-view-results,.default-view-files').hide();
                $('.default-view button[name="generate"]').hide();
            });

            $('.module-form #generator-moduleclass').change(function () {
                var value = $(this).val().match(/(\w+)\\\w+$/);
                var $idInput = $('#generator-moduleid');
                if (value && value[1] && $idInput.val() === '') {
                    $idInput.val(value[1]);
                }
            });

            // 选择文件夹点击
            $('.can-chose-folder').on('click', function ($dom) {

                initFunc($dom);
                $.post('/wii/default/see-folder', {
                    openPath: addInput.val()
                }, function ($response) {

                    var html = nodeFilter('', '', $response);
                    var dom = $("#zTree");
                    $.fn.zTree.init(dom, {
                        treeId: 'ztree',
                        treeObj: dom,
                        async: {
                            enable: true,
                            dataType: "JSON",
                            url: "/wii/default/see-folder",
                            autoParam: ["path=parentFolder"],
                            dataFilter: nodeFilter
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
                $('#choseSpace').on('click', selectSureFolder);
            });

            // 点击赋值文件名按钮
            $('.can-copy-name').on('click', function ($dom) {
                // 先获取下类JS\CSS基础名
                getJsCssPath();
                // 初始化
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
                // 最后是否需要增加/或\
                if (endName.charAt(endName.length - 1) !== '\\' &&
                    endName.charAt(endName.length - 1) !== '/'
                )
                    endName = endName + (useAlias ? '/' : '\\');

                // 获取结尾字符串
                var endExt = $($($dom['target'])).data('sync-ext');
                // 结尾字符串存在
                if (endExt)
                    endName = endName + modelName + endExt;
                else
                    endName = endName + getJsCssPath();
                var layerId = layer.confirm('自动生成的文件名为<code>' + endName + '</code>是否继续？', {
                    title: '确认操作',
                    btn: ['确定', '取消']
                }, function () {
                    $(addInput).val(endName).change();
                    $(addSticky).text(endName);
                    layer.close(layerId);
                });
            });

            // 返回上一级文件夹点击
            $('.reduce-folder').on('click', function ($target) {
                // 初始化
                initFunc($target);
                // 分割字符串
                var path = !useAlias ? addInput.val().split('\\') : addInput.val().split('/');
                // 删除掉最后一个元素
                path.pop();
                // '\'合并
                path = !useAlias ? path.join('\\') : path.join('/');
                if (!path) {
                    layer.msg('已经是初始文件夹');
                    return 'app';
                }
                // 赋值
                addSticky.text(path);
                addInput.val(path);
            });
        }
    };
})(jQuery);
