<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace wpjCode\wii\generators\crud;

use ReflectionClass;
use wpjCode\wii\CodeFile;
use Yii;
use yii\base\Exception;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;
use yii\db\Schema;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * Generates CRUD
 * @property array $columnNames Model column names. This property is read-only.
 * @property string $controllerID The controller ID (without the module ID prefix). This property is
 * read-only.
 * @property string $nameAttribute This property is read-only.
 * @property array $searchAttributes Searchable attributes. This property is read-only.
 * @property bool|\yii\db\TableSchema $tableSchema This property is read-only.
 * @property string $viewPath The controller view path. This property is read-only.
 * @property string $expName The controller do name for note.
 * @property string $jsPath The js path for all page.
 * @property string $cssPath The css path for all page.
 * @property string $baseModelClass base model
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Generator extends \wpjCode\wii\Generator
{

    /**
     * @var string the name of the code template that the user has selected.
     * The value of this property is internally managed by this class.
     */
    public $template = 'elementUi';

    /**
     * [JavaScript]脚本路径
     * @var
     */
    public $jsPath;
    /**
     * 此次操作的注释名称
     * @var
     */
    public $expName;
    /**
     * [CSS]样式路径
     * @var
     */
    public $cssPath;
    /**
     * 模板路径
     * @var
     */
    public $viewPath;
    /**
     * 基础类名称
     * @var
     */
    public $baseModelClass;
    /**
     * 控制器名称
     * @var
     */
    public $controllerClass;
    /**
     * 控制器继承的基础控制器
     * @var string
     */
    public $baseControllerClass = 'app\controllers\BaseController';
    /**
     * @var bool whether to use strict inflection for controller IDs (insert a separator between two consecutive uppercase chars)
     * @since 2.1.0
     */
    public $strictInflector = true;

    protected static $setting = [
        'baseViewPath'          => '', // 根[模板]路径
        'baseViewAlias'         => '@app/views/', // 根[模板]别名
        'baseControllerPath'    => 'app/controllers', // 根[控制器]路径
        'modulesControllerPath' => 'app/modules/{name}/controllers', // 模块[控制器]路径
        'modulesViewPath'       => '@app/modules/{name}/views', // 模块[模板]路径
        'modulesViewFilePath'   => '@app/modules/{name}/views/{fileName}' // 模块[模板文件]路径
    ];

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->expName = $this->langString('ex name value');

        // 重写模板列表
        $this->templates = [];
        if (!isset($this->templates['elementUi'])) {
            $this->templates['elementUi'] = $this->defaultTemplate();
        }
        foreach ($this->templates as $i => $template) {
            $this->templates[$i] = Yii::getAlias($template);
        }
    }

    /**
     * 操作名称
     * @param bool $returnEn 返回英文
     * @return string
     */
    public function getName($returnEn = false)
    {

        $title = 'CRUD Generator';
        if ($returnEn) return $title;
        return $this->langString($title);
    }

    /**
     * 操作描述
     * @param bool $returnEn 返回英文
     * @return string
     */
    public function getDescription($returnEn = false)
    {

        $title = 'CRUD Description';
        if ($returnEn) return $title;
        return $this->langString('CRUD Description');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['controllerClass', 'baseModelClass', 'expName', 'viewPath', 'jsPath', 'cssPath'], 'filter', 'filter' => 'trim'],
            [['baseModelClass', 'controllerClass', 'expName', 'viewPath', 'jsPath', 'cssPath'], 'required'],
            [['baseModelClass', 'controllerClass'], 'match', 'pattern' => '/^[\w\\\\]*$/', 'message' => $this->langString('just characters and backslashes')],
            [['baseModelClass'], 'validateClass', 'params' => ['extends' => BaseActiveRecord::className()]],
            // [['baseControllerClass'], 'validateClass', 'params' => ['extends' => Controller::className()]],
            [['controllerClass'], 'match', 'pattern' => '/Controller$/', 'message' => $this->langString('controller suffixed error')],
            [['controllerClass'], 'match', 'pattern' => '/(^|\\\\)[A-Z][^\\\\]+Controller$/', 'message' => $this->langString('controller name error')],
            [['controllerClass'], 'validateNewClass'],
//            [['controllerShowLayout'], 'validateFile']
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'expName'             => $this->langString('Exp Name'),
            'baseModelClass'      => $this->langString('Base Model Class'),
            'controllerClass'     => $this->langString('Controller Do Class'),
            'viewPath'            => $this->langString('View Path'),
            'jsPath'              => $this->langString('Js Path'),
            'cssPath'             => $this->langString('Css Path'),
            'baseControllerClass' => $this->langString('Base Controller Class')
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function hints()
    {
        return array_merge(parent::hints(), [
            'expName'             => $this->langString('Exp Name Hint'),
            'baseModelClass'      => $this->langString('Base Model Class Hint'),
            'controllerClass'     => $this->langString('Controller Do Class Hint'),
            'viewPath'            => $this->langString('View Path Hint'),
            'jsPath'              => $this->langString('Js Path Hint'),
            'cssPath'             => $this->langString('Css Path Hint'),
            'baseControllerClass' => $this->langString('Base Controller Class Hint')
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function requiredTemplates()
    {
        return [
            'controller.php',
            'views/_form.php',
            'views/create.php',
            'views/index.php',
            'views/update.php',
            'views/detail.php',
            'statics/css/form.php',
            'statics/css/index.php',
            'statics/js/form.php',
            'statics/js/index.php',
            'statics/js/detail.php'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function stickyAttributes()
    {
        return array_merge(parent::stickyAttributes(), $this->attributes());
    }

    /**
     * Returns the root path to the default code template files.
     * The default implementation will return the "templates" subdirectory of the
     * directory containing the generator class file.
     * @return string the root path to the default code template files.
     */
    public function defaultTemplate()
    {
        $class = new ReflectionClass($this);

        return dirname($class->getFileName()) . '/elementUi';
    }

    /**
     * {@inheritdoc}
     */
    public function generate()
    {

        // 模板基础文件夹
        $tempBasePath = $this->getTemplatePath();
        // 模板视图文件夹
        $tempViewPath = $tempBasePath . '/views';
        $files        = [];

        // ****** 1、页面展示控制器渲染 ******
        $controllerClass = Yii::getAlias('@' . str_replace('\\', '/', ltrim($this->controllerClass, '\\')) . '.php');
        $files[]         = new CodeFile($controllerClass, $this->render('controller.php'));

        // ****** 2、各页面[CSS]渲染 ******
        // [CSS]即将渲染到的目录
        $cssPath = $this->getCssPath();
        $files   = array_merge($files, [
            // 列表页[css]
            new CodeFile("{$cssPath}-index.css",
                $this->render(Yii::getAlias("/statics/css/index.php"))),
            // 列表页[form]
            new CodeFile("{$cssPath}-form.css",
                $this->render(Yii::getAlias("/statics/css/form.php"))),
        ]);

        // ****** 3、各页面[JS]渲染 ******
        $jsPath = $this->getJsPath();
        $files  = array_merge($files, [
            // 列表页[js]
            new CodeFile("{$jsPath}-index.js",
                $this->render(Yii::getAlias("/statics/js/index.php"))),
            // 列表页[form]
            new CodeFile("{$jsPath}-form.js",
                $this->render(Yii::getAlias("/statics/js/form.php")),
                [

                ]
            ),// 列表页[详情]
            new CodeFile("{$jsPath}-detail.js",
                $this->render(Yii::getAlias("/statics/js/detail.php")),
                [

                ]
            ),
        ]);

        // 模板即将渲染到的目录
        $viewPath = $this->getViewPath();

        foreach (scandir($tempViewPath) as $file) {
            if (is_file($tempViewPath . '/' . $file) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $files[] = new CodeFile("{$viewPath}/{$file}",
                    $this->render("views/{$file}"));
            }
        }

        // 再次弹出层告诉用户应该添加的配置
//        echo <<<EOT
//            '<div></div>'
//EOT;

        return $files;
    }

    /**
     * 获取[HTML]控制器名称
     * @param int $type 类型[0 - do-name; 1 - doName]
     * @return string the show controller ID (without the module ID prefix)
     */
    public function getControllerID($type = 0)
    {
        $pos   = strrpos($this->controllerClass, '\\');
        $class = substr(substr($this->controllerClass, $pos + 1), 0, -10);

        if ($type == 1) {
            return Inflector::variablize($class);
        }

        return Inflector::camel2id($class, '-', $this->strictInflector);
    }

    /**
     * @return string the controller view path
     */
    public function getViewPath()
    {

        if (empty($this->viewPath)) {
            return Yii::getAlias(self::$setting['baseViewAlias'] . $this->getControllerID());
        }

        return Yii::getAlias(str_replace('\\', '/', $this->viewPath));
    }

    /**
     * 获取控制器路径
     * @return string
     */
    public function getControllerPath()
    {

        return Yii::getAlias(str_replace('\\', '/', $this->controllerClass));
    }

    /**
     * 获取即将渲染的[CSS]绝对基础路径
     * @return bool|string
     */
    public function getCssPath()
    {

        if (empty($this->cssPath)) {
            return Yii::getAlias(self::$setting['baseViewAlias'] . $this->getControllerID());
        }

        ### 别名
        $path = Yii::getAlias(str_replace('\\', '/', $this->cssPath));
        // 包含了直接返回
        if (strstr($path, '/' . $this->getControllerID())) return $path;
        // 不包含结尾添加控制器前缀
        return Yii::getAlias(str_replace('\\', '/',
                $path . '/' . $this->getControllerID())
        );
    }

    /**
     * 获取即将渲染的[JS]绝对基础路径
     * @return bool|string
     */
    public function getJsPath()
    {

        if (empty($this->jsPath)) {
            return Yii::getAlias(self::$setting['baseViewAlias'] . $this->getControllerID());
        }

        ### 别名
        $path = Yii::getAlias(str_replace('\\', '/', $this->jsPath));
        // 包含了直接返回
        if (strstr($path, '/' . $this->getControllerID())) return $path;
        // 不包含结尾添加控制器前缀
        return Yii::getAlias(str_replace('\\', '/',
            $path . '/' . $this->getControllerID())
        );
    }

    /**
     * 获取页面上[CSS]路径
     * @param string $page 页面
     * @return bool|string
     */
    public function getPageCssPath($page = '')
    {

        // 页面不存在直接返回错误信息
        if (empty($page)) {
            try {
                throw new Exception('获取页面上[CSS]路径出错', 500);
            } catch (\Exception $error) {
                \Yii::error([
                    '````````````````````````````````````````````````````````',
                    '``                    WPJ WII 错误                     ``',
                    '`` 错误详情:   获取页面上[CSS]路径出错                     ``',
                    '`` 错误信息和参数详情:                                    ``',
                    '````````````````````````````````````````````````````````',
                    $error->getTraceAsString()
                ]);
            }
            $this->addError('cssPath', '页面样式名称不能为空, 详情查看日志' . $page);
            return '';
        }

        // 将css渲染到路径
        $basePath = Yii::getAlias($this->cssPath);
        // 将css路径中的web前全去除
        $webPath = Yii::getAlias('@app');
        $replace = [
            '@web'            => '',
            $webPath . '/web' => '',
            $webPath          => '',
        ];

        foreach ($replace as $k => $v) {

            $basePath = str_replace($k, $v, $basePath);
        }

        // 路径规则合法化
        $basePath = str_replace('\\', '/', $basePath);

        // 最终无css后缀叠加
        $cssPathInfo = pathinfo($page);
        if (!empty($page) && (empty($cssPathInfo['extension']) || $cssPathInfo['extension'] != 'css')) {
            $page = '-' . $page;
            $page .= '.css';
        }

        $basePath = $basePath . $page;

        return $basePath;
    }

    /**
     * 获取页面上[JS]路径
     * @param string $page 页面
     * @return bool|string
     */
    public function getPageJsPath($page = '')
    {

        // 页面不存在直接返回错误信息
        if (empty($page)) {
            try {
                throw new Exception('获取页面上[JS]路径出错', 500);
            } catch (\Exception $error) {
                \Yii::error([
                    '````````````````````````````````````````````````````````',
                    '``                    WPJ WII 错误                     ``',
                    '`` 错误详情:   获取页面上[JS]路径出错                     ``',
                    '`` 错误信息和参数详情:                                    ``',
                    '````````````````````````````````````````````````````````',
                    $error->getTraceAsString()
                ]);
            }
            $this->addError('cssPath', '页面JS脚本名称不能为空, 详情查看日志' . $page);
            return '';
        }

        // 将[js]渲染到路径
        $basePath = Yii::getAlias($this->jsPath);
        // 将[js]路径中的web前全去除
        $webPath = Yii::getAlias('@app');
        $replace = [
            '@web'            => '',
            $webPath . '/web' => '',
            $webPath          => '',
        ];

        foreach ($replace as $k => $v) {
            $basePath = str_replace($k, $v, $basePath);
        }

        // 路径规则合法化
        $basePath = str_replace('\\', '/', $basePath);

        // 最终无[js]后缀叠加
        $pathInfo = pathinfo($page);
        if (!empty($page) && (empty($pathInfo['extension']) || $pathInfo['extension'] != 'js')) {
            $page = '-' . $page;
            $page .= '.js';
        }

        $basePath = $basePath . $page;

        return $basePath;
    }

    /**
     * 获取最终[render]函数所需的模板文件路径
     * @param $path
     * @return String of the controller render path
     */
    public function getRenderViewPath($path)
    {

        // 控制器 路径
        $controllerPath = $this->getControllerPath();
        // 控制器 文件夹
        $controllerDir = StringHelper::dirname($controllerPath);

        // ****** 控制器为根控制器 - 默认返回 ******
        $basePath = self::$setting['baseControllerPath'];
        if ($controllerDir == $basePath && $this->isControlViewPath($controllerPath)) {
            return $path;
        }

        // ****** 走模块控制器 - 默认返回 ******
        $modulesConList = [];
        foreach (Yii::$app->modules as $k => $v) {
            $modulesConList[] = str_replace('{name}', $k,
                self::$setting['modulesControllerPath']
            );
        }

        if (in_array($controllerDir, $modulesConList) && $this->isControlViewPath($controllerPath)) {
            return $path;
        }
        $rootPath  = dirname(Yii::getAlias('@webroot'));
        $inputPath = $this->getViewPath() . '/' . $path;
        $endPath   = str_replace($rootPath, '', $inputPath);
        return '@app' . $endPath;
    }

    /**
     * 是否为[controller]下直属[view]路径
     * @param string $controlPath 控制器路径|如：'app/modules/controllers/DoController'
     * @return bool
     */
    private function isControlViewPath($controlPath = '')
    {

        // 路径信息
        $pathInfo = pathinfo($controlPath);
        // 控制器操作名 - 默认名
        $conDoName = str_replace('Controller', '', $pathInfo['filename']);
        // 字符全部小写
        $conDoName = Inflector::camel2id($conDoName, '-', $this->strictInflector);

        // ***** 根目录控制器，根目录木板路径 *****
        if ($this->viewPath == self::$setting['baseViewAlias'] . $conDoName) {
            return true;
        }

        // 走模块控制器 - 默认返回
        foreach (Yii::$app->modules as $k => $v) {
            $modulesConPath  = str_replace('{name}', $k, self::$setting['modulesControllerPath']);
            $modulesViewPath = str_replace('{name}', $k, self::$setting['modulesViewFilePath']);
            $modulesViewPath = str_replace('{fileName}', $conDoName, $modulesViewPath);
            $modulesViewList = [
                $modulesViewPath,
                ltrim($modulesViewPath, '\\'),
                $modulesViewPath . '/'
            ];
            // 控制器路径是modules中模板文件也是当前modules中的模板文件夹下的控制器操作文件夹
            if ($modulesConPath == $pathInfo['dirname'] && in_array($this->viewPath, $modulesViewList)) {

                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function getNameAttribute()
    {
        foreach ($this->getColumnNames() as $name) {
            if (!strcasecmp($name, 'name') || !strcasecmp($name, 'title')) {
                return $name;
            }
        }
        /* @var $class \yii\db\ActiveRecord */
        $class = $this->baseModelClass;
        $pk    = $class::primaryKey();

        return $pk[0];
    }

    /**
     * Generates code for active field
     * @param string $attribute
     * @return string
     */
    public function generateActiveField($attribute)
    {

        $tableSchema = $this->getTableSchema();

        if ($tableSchema === false || !isset($tableSchema->columns[$attribute])) {
            return $this->generateInput($attribute);
        }

        $column = $tableSchema->columns[$attribute];
        if ($column->type === 'text' || $column->size > 50) {
            return $this->generateTextArea($attribute, $column);
        } else if ($column->type === 'tinyint') {
            return $this->generateRadio($attribute, $column);
        }

        return $this->generateInput($attribute, $column);
    }

    /**
     * 渲染[input]
     * @param $attribute
     * @param null|yii\db\mysql\ColumnSchema $column
     * @return string
     */
    public function generateInput($attribute, $column = null)
    {

        $label = $this->getColumnLabel($attribute);

        $type = 'string';
        $modelType = '';
        if (property_exists($column, 'phpType') && $column->phpType == 'integer') {
            $type = 'number';
            $modelType = '.number';
        }

        return <<<EOT
            <el-form-item label="{$label}" prop="{$attribute}" class="form-item" :inline-message="true"
                    :error="customErrMsg.{$attribute}" ref="formItem_{$attribute}">
                  
                <el-input v-model{$modelType}="form.{$attribute}" size="small" class="form-element"
                        placeholder="请输入{$label}" type="{$type}">
                </el-input>
                
                <div class="form-element-append-inline ml-20" style="display: none;">
                    <el-tooltip class="item" effect="light" placement="top-start">
                        <div slot="content">
                            行内说明内容
                        </div>
                        <div>
                            <i class="el-icon-question font-second pointer"></i>
                            <span class="font-third pointer">
                                什么是行内说明？
                            </span>
                        </div>
                    </el-tooltip>
                </div>
                <div class="form-element-append lh-normal mt-5" style="display: none;">
                    <i class="el-icon-question font-third"></i>
                    <span class="font-third">
                        非行内说明文本
                    </span>
                </div>
                
            </el-form-item>
EOT;
    }

    /**
     * 渲染[input]
     * @param $attribute
     * @param null|yii\db\mysql\ColumnSchema $column
     * @return string
     */
    public function generateRadio($attribute, $column = null)
    {

        $label = $this->getColumnLabel($attribute);

        return <<<EOT
            <el-form-item label="{$label}" prop="{$attribute}" class="form-item" :inline-message="true"
                    :error="customErrMsg.{$attribute}" ref="formItem_{$attribute}">
                  
                <el-radio-group v-model="form.{$attribute}" size="mini" class="form-element">
            
                    <el-radio v-for="item in setting.{$attribute}_list" :label="item.value">
                        {{item.text}}
                    </el-radio>
            
                </el-radio-group>
                
                <div class="form-element-append-inline ml-20" style="display: none;">
                    <el-tooltip class="item" effect="light" placement="top-start">
                        <div slot="content">
                            行内说明内容
                        </div>
                        <div>
                            <i class="el-icon-question font-second pointer"></i>
                            <span class="font-third pointer">
                                什么是行内说明？
                            </span>
                        </div>
                    </el-tooltip>
                </div>
                <div class="form-element-append lh-normal mt-5" style="display: none;">
                    <i class="el-icon-question font-third"></i>
                    <span class="font-third">
                        非行内说明文本
                    </span>
                </div>
                
            </el-form-item>
EOT;
    }

    /**
     * 渲染[textArea]
     * @param $attribute
     * @param null|yii\db\mysql\ColumnSchema $column
     * @return string
     */
    public function generateTextArea($attribute, $column = null)
    {
        $label = $this->getColumnLabel($attribute);

        $type = 'string';
        if (property_exists($column, 'phpType') && $column->phpType == 'integer') {
            $type = 'number';
        }

        return <<<EOT
            <el-form-item label="{$label}" prop="{$attribute}" class="form-item" :inline-message="true"
                    :error="customErrMsg.{$attribute}" ref="formItem_{$attribute}">
                
                <el-input type="textarea" placeholder="请输入{$label}" v-model="form.{$attribute}"
                    class="form-element" maxlength="{$column->size}" show-word-limit type="{$type}"
                    :autosize="{ minRows: 6}">
                </el-input>
            
                <div class="form-element-append-inline ml-20" style="display: none;">
                    <el-tooltip class="item" effect="light" placement="top-start">
                        <div slot="content">
                            行内说明内容
                        </div>
                        <div>
                            <i class="el-icon-question font-second pointer"></i>
                            <span class="font-third pointer">
                                什么是行内说明？
                            </span>
                        </div>
                    </el-tooltip>
                </div>
                <div class="form-element-append lh-normal mt-5" style="display: none;">
                    <i class="el-icon-question font-third"></i>
                    <span class="font-third">
                        非行内说明文本
                    </span>
                </div>
                
            </el-form-item>
EOT;
    }

    /**
     * Generates code for active search field
     * @param string $attribute
     * @return string
     */
    public function generateActiveSearchField($attribute)
    {
        $tableSchema = $this->getTableSchema();
        if ($tableSchema === false) {
            return "\$form->field(\$model, '$attribute')";
        }

        $column = $tableSchema->columns[$attribute];
        if ($column->phpType === 'boolean') {
            return "\$form->field(\$model, '$attribute')->checkbox()";
        }

        return "\$form->field(\$model, '$attribute')";
    }

    /**
     * Generates column format
     * @param \yii\db\ColumnSchema $column
     * @return string
     */
    public function generateColumnFormat($column)
    {
        if ($column->phpType === 'boolean') {
            return 'boolean';
        }

        if ($column->type === 'text') {
            return 'ntext';
        }

        if (stripos($column->name, 'time') !== false && $column->phpType === 'integer') {
            return 'datetime';
        }

        if (stripos($column->name, 'email') !== false) {
            return 'email';
        }

        if (preg_match('/(\b|[_-])url(\b|[_-])/i', $column->name)) {
            return 'url';
        }

        return 'text';
    }

    /**
     * Generates validation rules for the search model.
     * @return array the generated validation rules
     */
    public function generateSearchRules()
    {
        if (($table = $this->getTableSchema()) === false) {
            return ["[['" . implode("', '", $this->getColumnNames()) . "'], 'safe']"];
        }
        $types = [];
        foreach ($table->columns as $column) {
            switch ($column->type) {
                case Schema::TYPE_TINYINT:
                case Schema::TYPE_SMALLINT:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
                    $types['integer'][] = $column->name;
                    break;
                case Schema::TYPE_BOOLEAN:
                    $types['boolean'][] = $column->name;
                    break;
                case Schema::TYPE_FLOAT:
                case Schema::TYPE_DOUBLE:
                case Schema::TYPE_DECIMAL:
                case Schema::TYPE_MONEY:
                    $types['number'][] = $column->name;
                    break;
                case Schema::TYPE_DATE:
                case Schema::TYPE_TIME:
                case Schema::TYPE_DATETIME:
                case Schema::TYPE_TIMESTAMP:
                default:
                    $types['safe'][] = $column->name;
                    break;
            }
        }

        $rules = [];
        foreach ($types as $type => $columns) {
            $rules[] = "[['" . implode("', '", $columns) . "'], '$type']";
        }

        return $rules;
    }

    /**
     * @return array searchable attributes
     */
    public function getSearchAttributes()
    {
        return $this->getColumnNames();
    }

    /**
     * Generates URL parameters
     * @return string
     */
    public function generateUrlParams()
    {
        /* @var $class ActiveRecord */
        $class = $this->baseModelClass;
        $pks   = $class::primaryKey();
        if (count($pks) === 1) {
            if (is_subclass_of($class, 'yii\mongodb\ActiveRecord')) {
                return "'id' => (string)\$model->{$pks[0]}";
            }

            return "'id' => \$model->{$pks[0]}";
        }

        $params = [];
        foreach ($pks as $pk) {
            if (is_subclass_of($class, 'yii\mongodb\ActiveRecord')) {
                $params[] = "'$pk' => (string)\$model->$pk";
            } else {
                $params[] = "'$pk' => \$model->$pk";
            }
        }

        return implode(', ', $params);
    }

    /**
     * Generates action parameters
     * @return string
     */
    public function generateActionParams()
    {
        /* @var $class ActiveRecord */
        $class = $this->baseModelClass;
        $pks   = $class::primaryKey();
        if (count($pks) === 1) {
            return '$id';
        }

        return '$' . implode(', $', $pks);
    }

    /**
     * Generates parameter tags for phpdoc
     * @return array parameter tags for phpdoc
     */
    public function generateActionParamComments()
    {
        /* @var $class ActiveRecord */
        $class = $this->baseModelClass;
        $pks   = $class::primaryKey();
        if (($table = $this->getTableSchema()) === false) {
            $params = [];
            foreach ($pks as $pk) {
                $params[] = '@param ' . (strtolower(substr($pk, -2)) === 'id' ? 'integer' : 'string') . ' $' . $pk;
            }

            return $params;
        }
        if (count($pks) === 1) {
            return ['@param ' . $table->columns[$pks[0]]->phpType . ' $id'];
        }

        $params = [];
        foreach ($pks as $pk) {
            $params[] = '@param ' . $table->columns[$pk]->phpType . ' $' . $pk;
        }

        return $params;
    }

    /**
     * Returns table schema for current model class or false if it is not an active record
     * @return bool|\yii\db\TableSchema
     */
    public function getTableSchema()
    {
        /* @var $class ActiveRecord */
        $class = $this->baseModelClass;
        if (is_subclass_of($class, 'yii\db\ActiveRecord')) {
            return $class::getTableSchema();
        }

        return false;
    }

    /**
     * @return array model column names
     */
    public function getColumnNames()
    {
        /* @var $class ActiveRecord */
        $class = $this->baseModelClass;
        if (is_subclass_of($class, 'yii\db\ActiveRecord')) {
            return $class::getTableSchema()->getColumnNames();
        }

        /* @var $model \yii\base\Model */
        $model = new $class();

        return $model->attributes();
    }

    /**
     * 返回[attribute]的文本
     * @param $attribute
     * @return string
     */
    public function getColumnLabel($attribute)
    {
        /* @var $model \yii\db\ActiveRecord */
        $class = $this->baseModelClass;
        $model = new $class();

        $attrName = $model->getAttributeLabel($attribute);

        return $this->langString($attrName);
    }

    /**
     * 保存
     * @param CodeFile[] $files
     * @param array $answers
     * @param string $results
     * @return bool
     */
    public function save($files, $answers, &$results)
    {

        $result = parent::save($files, $answers, $results);

        // ☆--后台页面展示连接--☆
        $baseName = StringHelper::basename($this->baseModelClass);

        // 基础类名去关键词
        $baseName    = str_replace(['Model'], [''], $baseName);
        $controlFile = Yii::getAlias('@' . str_replace('\\', '/',
                ltrim($this->controllerClass, '\\')) . '.php');
        $files       = new CodeFile($controlFile, '');
        $route       = $files->getRoute(false);

        $row = [
            '下面是【后台】页面展示连接：',
            '{space}// [' . $this->expName . ']列表 页面',
            "{space}\$urlPre . '" . lcfirst($baseName) . ".html' => \$routePre . '" .
            $route . "/index-page',",
            '{space}// [' . $this->expName . ']添加 页面',
            "{space}\$urlPre . '" . lcfirst($baseName) . "Create.html' => \$routePre . '" .
            $route . "/create-page',",
            '{space}// [' . $this->expName . ']修改 页面',
            "{space}\$urlPre . '" . lcfirst($baseName) . "Update.html' => \$routePre . '" .
            $route . "/update-page',",
            '{space}// [' . $this->expName . ']详情 页面',
            "{space}\$urlPre . '" . lcfirst($baseName) . "Detail.html' => \$routePre . '" .
            $route . "/detail-page',",
            '下面是【前台】页面展示连接：',
            '{space}// ' . $this->expName,
            '{space}' . lcfirst($baseName) . ': {',
            "{space}{space}_prefix:  '" . lcfirst($baseName) . "',",
            "{space}{space}index: '.html',{tab}{tab}{space}// [列表]页面",
            "{space}{space}create: 'Create.html',{tab}// [添加]页面",
            "{space}{space}update: 'Update.html',{tab}// [修改]页面",
            "{space}{space}detail: 'Detail.html',{tab}// [详情]页面",
            '{space}}'
        ];

        $row = array_merge($row, [
            '下面是【后台】API操作连接：',
            '{space}// [' . $this->expName . ']获取设置 API',
            "{space}\$urlPre . '" . lcfirst($baseName) . "Setting.api' => \$routePre . '" .
            $route . "/setting',",
            '{space}// [' . $this->expName . ']获取列表 API',
            "{space}\$urlPre . '" . lcfirst($baseName) . "List.api' => \$routePre . '" .
            $route . "/list',",
            '{space}// [' . $this->expName . ']提交添加 API',
            "{space}\$urlPre . '" . lcfirst($baseName) . "Create.api' => \$routePre . '" .
            $route . "/create',",
            '{space}// [' . $this->expName . ']获取详情 API',
            "{space}\$urlPre . '" . lcfirst($baseName) . "Detail.api' => \$routePre . '" .
            $route . "/detail',",
            '{space}// [' . $this->expName . ']提交更新 API',
            "{space}\$urlPre . '" . lcfirst($baseName) . "Update.api' => \$routePre . '" .
            $route . "/update',",
            '{space}// [' . $this->expName . ']提交导出 API',
            "{space}\$urlPre . '" . lcfirst($baseName) . "Export.api' => \$routePre . '" .
            $route . "/export',",
            '{space}// [' . $this->expName . ']提交导入 API',
            "{space}\$urlPre . '" . lcfirst($baseName) . "Import.api' => \$routePre . '" .
            $route . "/import',"
        ]);

        // 有[状态]增加[状态]接口
        if (in_array('status', $this->getColumnNames())) {
            $row[] = '{space}// [' . $this->expName . ']禁用 API';
            $row[] = "{space}\$urlPre . '" . lcfirst($baseName) .
                "Disabled.api' => \$routePre . '" . $route . "/disabled',";
            $row[] = '{space}// [' . $this->expName . ']开启 API';
            $row[] = "{space}\$urlPre . '" . lcfirst($baseName) .
                "Open.api' => \$routePre . '" . $route . "/open',";
        }
        // 有排序增加排序接口
        if (
            in_array('sort', $this->getColumnNames()) ||
            in_array('list_order', $this->getColumnNames())
        ) {
            $row[] = '{space}// [' . $this->expName . ']修改排序 API';
            $row[] = "{space}\$urlPre . '" . lcfirst($baseName) .
                "Sort.api' => \$routePre . '" . $route . "/sort',";
        }

        // ☆--前台API操作连接--☆
        $row = array_merge($row, [
            '下面是【前台】API操作连接：',
            '{space}// ' . $this->expName,
            '{space}' . lcfirst($baseName) . ': {',
            "{space}{space}_prefix: '" . lcfirst($baseName) . "',",
            "{space}{space}setting: 'Setting.api',{space}{space}// [获取设置]API",
            "{space}{space}list: 'List.api',{tab}{tab}{tab}// [获取列表]API",
            "{space}{space}create: 'Create.api',{tab}{tab}// [提交创建]API",
            "{space}{space}update: 'Update.api',{tab}{tab}// [提交更新]API",
            "{space}{space}detail: 'Detail.api',{tab}{tab}// [获取详情]API",
            "{space}{space}export: 'Export.api',{tab}{tab}// [导出详情]API",
            "{space}{space}import: 'Import.api',{tab}{tab}// [导入详情]API",
        ]);
        // 有[状态]增加[状态]接口
        if (in_array('status', $this->getColumnNames())) {
            $row[] = "{space}{space}disabled: 'Disabled.api',{tab}// [禁用]API";
            $row[] = "{space}{space}open: 'Open.api',{tab}{tab}{tab}// [开启]API";
        }
        // 有[排序]增加[排序]接口
        if (
            in_array('sort', $this->getColumnNames()) ||
            in_array('list_order', $this->getColumnNames())
        ) {
            $row[] = "{space}{space}sort: 'Sort.api',{tab}{tab}{tab}// [修改排序]API";
        }
        $row[] = "{space}}";

        $results .= implode("\n", $row);
        $results = str_replace('{space}', '&nbsp;&nbsp;&nbsp;', $results);
        $results = str_replace('{tab}', "&nbsp;&nbsp;&nbsp;&nbsp;", $results);

        return $result;
    }

    /**
     * @return string|null driver name of baseModelClass db connection.
     * In case db is not instance of \yii\db\Connection null will be returned.
     * @since 2.0.6
     */
    protected function getClassDbDriverName()
    {
        /* @var $class ActiveRecord */
        $class = $this->baseModelClass;
        $db    = $class::getDb();
        return $db instanceof \yii\db\Connection ? $db->driverName : null;
    }
}
