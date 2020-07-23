<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2020-07-15
 * Time: 15:50
 */
return [
    'CRUD Generator' => '增删改查操作渲染器',
    'CRUD Description' => '此脚本用于生成常用的CRUD操作页面，如：主页列表、详情页、添加修改页面。',
    'Base Model Class' => '基础类路径',
    'Base Model Class Hint' => '基础类路径，此类作为此CRUD操作的基类，一般是最初继承<code>\yii\db\ActiveRecord</code>的类，生成的，由于会随时重新生成此类，所以二次开发等代码修改最好在<code>自定义操作类</code>上进行',
    'Model Class' => '自定义操作类',
    'Model Class Hint' => '这是与CRUD操作的自定义二次封装类，会走其相关方法，所有的二次开发都是在这个[model]类上。一个提供一个完整合格的[类名称路径], 如： <code>app\models\Post</code>。',
    'Controller Do Class' => '操作控制器',
    'Controller Do Class Hint' => '这是要生成操作控制器类，主要是提交[AJAX]数据和操作所用，所以你应该提供一个完全合格的控制器空间类 (如： <code>app\controllers\PostController</code>), 并且类名应该遵循【第一个字母大写】的驼峰命名法。 请确保此次生成【控制器】遵循YII2的控制器语法。',
    'View Path' => '[页面展示]模板路径',
    'View Path Hint' => '生成与[页面展示]控制器相对应CRUD的模板文件。支持别名(alias), 如：
                <code>/var/www/basic/controllers/views/post</code>, <code>@app/views/post</code>。如果值为空则默认 <code>@app/views/ControllerID</code>',
    'Controller Show Class' => '页面展示控制器',
    'Controller Show Class Hint' => '这是要生成展示控制器类，主要是展示页面，所以你应该提供一个完全合格的控制器空间类 (如： <code>app\controllers\PostController</code>), 并且类名应该遵循【第一个字母大写】的驼峰命名法。 请确保此次生成【控制器】遵循YII2的控制器语法。',
    'chose file/folder' => '请选择一个文件/夹',
    'ex name value' => 'CRUD操作',
    'Exp Name' => '操作注释名称',
    'Exp Name Hint' => '生成一个对于此次操作的注释的说明，可以填写任意字符。必填字段，如：输入<code>CRUD</code>生成的操作控制器注释为：<code>[CRUD]操作控制器</code>',
    'Base Controller Class' => '基础控制器',
    'Base Controller Class Hint' => '新创建的两个控制器将继承此控制器。所以你应该提供一个完全合格的控制器空间类, 如： <code>yii\web\Controller</code>。',
    'modelClassErr_1' => "模型必须有或者继承过来一个<code>主键</code>",
    'just characters and backslashes' => '只允许使用单词字符和反斜杠',
    'controller suffixed error' => '控制器类名称必须以“Controller”后缀；',
    'controller name error' => '控制器类名必须以大写字母开头的驼峰命名法。',
    'Js Path' => '[页面展示模板]Js脚本路径',
    'Js Path Hint' => '生成与[页面展示]控制器相对应CRUD的模板文件中JS文件路径。支持别名(alias), 如：
                <code>/var/www/web/js/post</code>, <code>@web/js/post</code>。如果值为空则默认 <code>@web/js/ControllerID</code>，最终生成文件路径为：<code>web/js/post-index.js、web/js/post-form.js</code>',
    'Css Path' => '[页面展示模板]Css样式路径',
    'Css Path Hint' => '生成与[页面展示]控制器相对应CRUD的模板文件中CSS文件路径。支持别名(alias), 如：
                <code>/var/www/web/css/post</code>, <code>@web/css/post</code>。如果值为空则默认 <code>@web/css/ControllerID</code>，最终生成文件路径为：<code>web/css/post-index.css、web/css/post-form.css</code>',
    'Code Template' => '生成代码模板',
    'Code Template Hint' => '请选择应该使用哪一组模板生成代码。',
    'Controller Show Layout' => '[页面展示控制器]布局文件',
    'Controller Show Layout Hint' => '[页面展示控制器]布局文件路径。。支持别名(alias), 如：
                <code>/var/www/web/views/layouts/main</code>, <code>@app/views/layouts/main</code>',
    'code gender success' => '代码生成成功',
    'Welcome to Wii ' => '欢迎使用WPJ的WII代码生成器',
    'Welcome to Wii Hint' => '一个可以为你编写代码的神奇工具(基于Yii2 Gii)',
    'Start enjoy' => '从以下操作开始代码生成乐趣',
    'Start Button Text' => '开始',
    'Class not exit' => '类未找到或者有语法错误',
    'Check file to gender' => '点击 <code>Generate</code> 按钮生成下面所选择文件：',
    'Code File' => '代码文件',
    'Action' => '是否改变',
    'Preview' => '查看',
    'Generate' => '渲染'
];
