<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2020-07-21
 * Time: 16:05
 */

use yii\helpers\StringHelper;

/* @var $generator wpjCode\wii\generators\doModel\Generator */
$schema = $generator->getTableSchema();
// 主键
$pk = empty($schema->primaryKey[0]) ? null : $schema->primaryKey[0];
/* @var $model \yii\db\ActiveRecord */
$model = new $generator->baseModelClass();

$modelClass = $generator->getRenderFilePath();
$modelPath = pathinfo($modelClass);

$baseModelClass = str_replace('\\', '/', $generator->baseModelClass);
$baseModelPath = pathinfo($baseModelClass);

// 最大排序
$maxSort = 999999;
// 最小排序
$minSort = -999999;

echo '<?php';

echo "\n\nnamespace " .
    StringHelper::dirname(ltrim($generator->nameSpace, '\\')) .
    ';';

if ($modelPath['dirname'] != $baseModelPath['dirname']) {
    echo "\n\nuse " . $generator->baseModelClass . ';';
}

$times = time();
$createDate = date('Y/m/d', $times);
$createTime = date('H:i:s', $times);
echo <<<EOT

use app\service\ToolsService;
use yii\db\Expression;
use yii\db\ExpressionInterface;
use yii\helpers\ArrayHelper;
use yii\db\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReadExcel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriteExcel;

/**
 * {$generator->expName}
 * 作者: Editor Name
 * 日期: {$createDate}
 * 时间: {$createTime}
 */
class {$modelPath['filename']} extends {$baseModelPath['filename']}
{

EOT;
if (property_exists($schema, 'columns')) {
    foreach ($schema->columns as $k => $v) {
        // 数据库类型不存在下一循
        if (!property_exists($v, 'dbType')) continue;

        // 如果是枚举数字类型则进行渲染 枚举列表
        if (strstr($v->dbType, 'tinyint')) {

            # 保证说明
            $comment = property_exists($v, 'comment') ? $v->comment : '--';

            # [ucwords]将每个单词的首字母大写
            # [str_replace]字符串替换
            $capFirstName = ucwords(str_replace('_', ' ', $v->name));
            # [ucfirst]将所有的字符串首字母大写；
            $capFirstName = str_replace(' ', '', ucfirst($capFirstName));
            # 首字母小写
            $lowFirstName = lcfirst($capFirstName);
            echo <<<EOT

    /**
     * $comment 列表
     * @var array
    */
    private static \${$lowFirstName}List = [
EOT;
            switch ($v->name) {
                // 状态默认值
                case 'status':
                    echo <<<EOT

        [
            'key' => 'disabled',
            'value' => -1,
            'text' => '禁用'
        ],
        [
            'key' => 'normal',
            'value' => 0,
            'text' => '审核'
        ],
        [
            'key' => 'open',
            'value' => 1,
            'text' => '开启'
        ],\r
    
EOT;
                    break;
            }
            echo <<<EOT
];
    
EOT;

        }
    }
}
if ($model->hasAttribute('sort') || $model->hasAttribute('list_order')) {
    echo <<<EOT
    
    
    /**
     * 排序最大值
     * @var int
     */
    protected static \$maxSort = {$maxSort};
    /**
     * 排序最小值
     * @var int
     */
    protected static \$minSort = {$minSort};
    
EOT;
}
echo <<<EOT

    /**
     * 导出的配置
     * @var array
     */
    private static \$exportConfig = [
        'field' => [ // 字段列表
EOT;
foreach ($generator->getTableSchema()->columns as $kL => $vL) {
echo "\n            '" . $vL->name . "',";
}
echo <<<EOT

        ]
    ];
    
    /**
     * 导入的配置
     * @var array
     */
    private static \$importConfig = [
        'field' => [ // 字段列表
EOT;
foreach ($generator->getTableSchema()->columns as $kL => $vL) {
echo "\n            '" . $vL->name . "' => '" . $vL->comment . "',";
}
echo <<<EOT

        ]
    ];
    
    /**
     * 基础[SQL]
     * @var \yii\db\ActiveQuery
     */
    private \$sqlBase;
    /**
     * 条件
     * @var array
     */
    private \$where = [];
    /**
     * 排序
     * @var array
     */
    private \$orderBy = [];
    /**
     * 静态错误暂存
     * @var
     */
    private static \$error_;

    /**
     * 导入总页数
     * @var string
     */
    public \$importTotalPage = 0;
    
    /**
     * 规则验证
     * @return array
     */
    public function rules()
    {

EOT;

// ******** [枚举数据库类型]渲染列表 开始 ********
if (property_exists($schema, 'columns')) {
    foreach ($schema->columns as $k => $v) {
        // 数据库类型不存在 下一循
        if (!property_exists($v, 'dbType')) continue;

        // 如果不是枚举数字类型 下一循
        if (!strstr($v->dbType, 'tinyint')) continue;

        # [ucwords]将每个单词的首字母大写
        # [str_replace]字符串替换
        $capFirstName = ucwords(str_replace('_', ' ', $v->name));
        # [ucfirst]将所有的字符串首字母大写；
        $capFirstName = str_replace(' ', '', ucfirst($capFirstName));
        # 首字母小写
        $lowFirstName = lcfirst($capFirstName);
        echo <<<EOT

        // [{$v->comment}]列表
        \${$lowFirstName}List = array_column(self::get{$capFirstName}List(), 'value');
EOT;

    }
}
// ******** [枚举数据库类型]渲染列表 结束 ********

echo <<<EOT
        \n
        \$parent = parent::rules();
        return ArrayHelper::merge(\$parent, [
EOT;

// ******** [枚举数据库类型]渲染[rules] 开始 ********
if (property_exists($schema, 'columns')) {
    foreach ($schema->columns as $k => $v) {
        // 数据库类型不存在 下一循
        if (!property_exists($v, 'dbType')) continue;

        // 如果不是枚举数字类型 下一循
        if (!strstr($v->dbType, 'tinyint')) continue;

        # ucwords将每个单词的首字母大写
        # str_replace 字符串替换
        $capFirstName = ucwords(str_replace('_', ' ', $v->name));
        # ucfirst 将所有的字符串首字母大写；
        $capFirstName = str_replace(' ', '', ucfirst($capFirstName));
        # 保证首字母小写
        $capFirstName = lcfirst($capFirstName);
        echo <<<EOT

            ['$v->name', 'in', 'range' => \${$capFirstName}List, 'message' => '{$v->comment}不合法'],
EOT;

    }
}
// ******** [枚举数据库类型]渲染规则 结束 ********

// ******** 特殊 - [排序]渲染[rules] 开始 ********
if ($model->hasAttribute('sort')) {
    echo <<<EOT
    
            ['sort', 'integer', 'max' => self::getMaxSort(), 'min' => self::getMinSort(),
                'message' => '排序不得超过{$maxSort}，不得小于{$minSort}'],
EOT;
} else if ($model->hasAttribute('list_order')) {
    echo <<<EOT
    
            ['list_order', 'integer', 'max' => self::getMaxSort(), 'min' => self::getMinSort(),
                'message' => '排序不得超过{$maxSort}，不得小于{$minSort}'],
EOT;
}
// ******** 特殊 - [排序]渲染[rules] 结束 ********

echo <<<EOT
            
            [['importTotalPage'], 'safe'],
        ]);
    }

    /**
     * 重写label的 文字
     */
    public function attributeLabels()
    {
        
        \$parent = parent::attributeLabels();
        return array_merge(\$parent, [
EOT;
// ******** 字段[label]添加 开始 ********
if (property_exists($schema, 'columns')) {
    foreach ($schema->columns as $k => $v) {

        $capFirstName = ucfirst($v->name);
        echo <<<EOT
        
            '$v->name' => '$v->comment',
EOT;

    }
}
// ******** 字段[label]添加 结束 ********
echo <<<EOT
            
            'importTotalPage' => '导入分页总页数'
        ]);
    }

    /**
     * 场景
     * @return array
     */
    public function scenarios()
    {

        \$scenarios = parent::scenarios();
        return ArrayHelper::merge(\$scenarios, [
            // 自定义场景 (无用请删除)
            'scUpdate' => [
                'someAttributes'
            ]
        ]);
    }
    
    /**
     * 加载整体[Model]
     * @param bool \$id 编号
     * @param string \$scenario 场景
     * @return {$modelPath['filename']}
     *  ` PS:[\$id]空为何不返回[\$model::find()]: 因为可能准确想返回条目是否存在查询结果可能null,返回在find报错。
     */
    public static function loadModel(\$id = true, \$scenario = 'default')
    {

        // 实力化类
        \$model = new self();

        ### 验证 + 查询
        // 编号 不存在直接返回空
        if (!\$id) return null;
        // 编号为非特定值查询
        if (\$id !== true) \$model = \$model::findOne(\$id);
        // 条目不存在
        if (!\$model) return \$model;

        ### 场景
        \$sceList = array_keys(\$model->scenarios());
        if (!empty(\$scenario) && in_array(\$scenario, \$sceList)) \$model->setScenario(\$scenario);

        return \$model;
    }

    /**
     * 初始化并返回当前基础[SQL]
     * @return \yii\db\ActiveQuery
     */
    protected function getSqlBase()
    {
        
        ### 数据存在直接返回
        if (\$this->sqlBase) return \$this->sqlBase;
        
        ### 不存在初始化
        \$this->sqlBase = \$this::find()->where(\$this->where);
        
        ### 初始化排序
        // 是否已经有自定义排序
        if (property_exists(\$this, 'orderBy') && !empty(\$this->orderBy)) {
            \$this->sqlBase->orderBy(\$this->orderBy);
        } else { // 无自定义排序
EOT;
if ($model->hasAttribute('sort') && $pk) {
    echo <<<EOT
    
            \$this->sqlBase->orderBy('sort desc, {$pk} desc');
EOT;
} else if ($model->hasAttribute('list_order') && $pk) {
    echo <<<EOT
    
            \$this->sqlBase->orderBy('list_order desc, {$pk} desc');
EOT;
} else if (!$model->hasAttribute('sort') && !$model->hasAttribute('list_order') && $pk) {
    echo <<<EOT
    
            \$this->sqlBase->orderBy('{$pk} desc');
EOT;
}
echo <<<EOT

        }
        
        return \$this->sqlBase;
    }
    
    /**
     * 获取全部列表
     * @param integer \$page 当前页
     * @param integer \$limit 获取几条
     * @param null \$field 获取字段
     * @param null \$opt 其他设置
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getList(\$page, \$limit, \$field = null, \$opt = [])
    {

        // 当前页面计算
        \$page = ((\$page - 1) < 0 ? 0 : (\$page - 1));

        // 查找的 字段空的 就默认给列表
        if (!\$field) \$field = '*';

        // 基础 where加载完毕
        \$this->getSqlBase()->select(\$field);
            
        // 数据的获取 分页等
        \$list = \$this->getSqlBase()->offset(\$page * \$limit)
            ->limit(\$limit)
            ->asArray()->all();
            
        ### 某些参数初始化
        

        return \$this::formatData(\$list, \$opt);
    }
    
    /**
     * 格式化列表活详情数据
     * @param array \$list 列表
     * @param array \$opt 其他设置
     * @return mixed
     */
    public static function formatData(\$list, \$opt = []) {

        // 为空直接返回
        if (empty(\$list)) return \$list;
        // 需要返回第一组（可能不是二维数组）
        \$needFirst = false;
        if (!is_array(array_values(\$list)[0])) {
            \$needFirst = true;
            \$list      = [\$list];
        }

        ### 某些参数初始化


        ### 开始格式化
        foreach (\$list as \$k => &\$v) {
EOT;
if ($model->hasAttribute('add_time')) {
    echo <<<EOT
    
    
            // 更新时间
            if (isset(\$v['add_time'])) {
                \$v['add_time_text'] = date('Y-m-d H:i:s', \$v['add_time']);
                \$v['add_time_text_s'] = date('Y-m-d', \$v['add_time']);
            }
EOT;
}
if ($model->hasAttribute('create_time')) {
    echo <<<EOT
    
    
            // 更新时间
            if (isset(\$v['create_time'])) {
                \$v['create_time_text'] = date('Y-m-d H:i:s', \$v['create_time']);
                \$v['create_time_text_s'] = date('Y-m-d', \$v['create_time']);
            }
EOT;
}
if ($model->hasAttribute('update_time')) {
    echo <<<EOT
    
    
            // 更新时间
            if (isset(\$v['update_time'])) {
                \$v['update_time_text'] = date('Y-m-d H:i:s', \$v['update_time']);
                \$v['update_time_text_s'] = date('Y-m-d', \$v['update_time']);
            }
EOT;
}
if ($model->hasAttribute('content')) {
    echo <<<EOT
    
    
            // 内容转化下
            if (!empty(\$v['content'])) {
                \$v['content'] = htmlspecialchars_decode(\$v['content']);
                \$v['content'] = ToolsService::addHtmlImgDomain(\$v['content']);
            }
EOT;
}
// ******** 枚举字段文本 开始 ********
if (property_exists($schema, 'columns')) {
    foreach ($schema->columns as $k => $v) {
        // 数据库类型不存在 下一循
        if (!property_exists($v, 'dbType')) continue;
        // 如果不是枚举数字类型 下一循
        if (!strstr($v->dbType, 'tinyint')) continue;

        # ucwords将每个单词的首字母大写
        # str_replace 字符串替换
        $capFirstName = ucwords(str_replace('_', ' ', $v->name));
        # ucfirst 将所有的字符串首字母大写；
        $capFirstName = str_replace(' ', '', ucfirst($capFirstName));
        echo <<<EOT


            // {$v->comment} 文本
            if (isset(\$v['{$v->name}'])) {
                \$v['{$v->name}_text'] = self::get{$capFirstName}Text(\$v['{$v->name}']);
            }
EOT;

    }
}
// ******** 枚举字段文本 结束 ********
echo <<<EOT

        }

        reset(\$list);
        return \$needFirst ? current(\$list) : \$list;
    }

    /**
     * 获取记录总数量
     * @return int|string
     */
    public function getCount()
    {

        // 没有加载条件加载下
        if (empty(\$this->getSqlBase()->where)) {
            \$this->getSqlBase()->where(\$this->where);
        }
        // 基础 where加载完毕
        \$count = \$this->getSqlBase()->count();

        return intval(\$count);
    }
    
    /**
     * 加载条件
     * @param \$where
     * @return \$this
     */
    public function loadWhere(\$where)
    {

        // 条件不存在
        if (empty(\$where)) return \$this;

        // 如果[where][0]是'and' 直接赋值
        \$canRetList = ['and', 'or', 'AND', 'OR'];
        if (!empty(\$where[0]) && in_array(\$where[0], \$canRetList)) {

            \$this->where = \$where;
            return \$this;
        }
        
        // 不是数组 字符直接 判断
        if (!is_array(\$where)) {

            // 条件是 有效
            if (!empty(\$where) && \$this->hasAttribute(\$where))

                \$this->where = \$where;

            // 条件 无有效
            return \$this;
        }

        // 循环  条件是否有效
        \$stagingWhere = ['and'];
        foreach (\$where as \$k => \$v) {

            // 数组 - 首先值是有的，不能是空
            if (\$this->hasAttribute(\$k) && is_array(\$v) && count(\$v) > 0) {

                \$stagingWhere[] = ['IN', \$k, array_values(\$v)];
                continue;
            }

            // 字符串 - 首先值是有的，不能是空
            if (\$this->hasAttribute(\$k) && !is_array(\$v) && strlen(\$v) > 0) {   

                \$stagingWhere[] = ['=', \$k, \$v];
                continue;
            }
        }

        // 条件最终赋值
        \$this->where = \$stagingWhere;

        return \$this;
    }
    
    /**
     * 加载排序
     * @param string|array \$sort 排序规则
     *  ` 字符串格式为：field => sortType | id => desc
     * @param bool \$noCheck 无需验证
     * @return \$this
     * @return \$this
     */
    public function loadSort(\$sort, \$noCheck = false)
    {

        // 条件不存在
        if (empty(\$sort)) return \$this;

        // 无需验证
        if (\$noCheck || \$sort instanceof ExpressionInterface) {
            \$this->orderBy = \$sort;
            return \$this;
        }

        // 将[, ]转为[,]
        if (is_string(\$sort)) {
            // 字符串替换
            \$sort = str_replace(', ', ',', \$sort);
            // 字符串分割
            \$sort = explode(',', \$sort);
        }

        // 循环  条件是否有效
        \$stagingSort = [];
        // 合法排序列表
        \$typeList = [SORT_DESC, SORT_ASC, 'DESC', 'ASC'];
        // 允许列表 - 无需验证
        \$toExpList = ['RAND()'];
        foreach (\$sort as \$k => \$v) {

            ### 做一定的过滤
            // 数组 - 过滤
            if (is_array(\$v)) continue;
            // 类型是[表达式]
            if (\$v instanceof ExpressionInterface) {
                \$stagingSort[\$k] = \$v;
                continue;
            }
            // 无需验证列表
            if (in_array(strtoupper(\$v), \$toExpList)) {
                \$stagingSort[\$k] = new Expression(strtoupper(\$v));
                continue;
            }

            ### 字段验证
            // 数组模式：字段->排序类型
            if (\$this->hasAttribute(\$k) && in_array(\$v, \$typeList)) {
                \$stagingSort[\$k] = \$v;
                continue;
            }
            // 字符模式：'id DESC'
            \$expResult = preg_match('/^(.*?)\s+(asc|desc)$/i', \$v, \$matches);
            if (\$expResult && \$this->hasAttribute(\$matches[1])) {
                \$stagingSort[\$matches[1]] = strcasecmp(\$matches[2], 'desc') ? SORT_ASC : SORT_DESC;
            }
        }

        // 条件最终赋值
        \$this->orderBy = \$stagingSort;

        return \$this;
    }

    /**
     * 添加|保存
     * @param bool \$doSave 是否提交保存|false - 仅仅验证
     * @return bool
     */
    public function saveData(\$doSave = true)
    {

        ### 批量操作[缓存保存前一些格式化]
        foreach (\$this->getAttributes() as \$k => \$v) {
            // 字段类型为[JSON]类型需要转为数组 - 保存自动转为[JSON]
            if (is_string(\$v) && ToolsService::isJson(\$v)) {
                \$this->setAttribute(\$k, json_decode(\$v, true));
                continue;
            }
        }
EOT;
echo <<<EOT



        ### 单个操作[缓存保存前一些格式化]
        \$nowTime = time();
        \$this->setAttributes([
        
EOT;
if ($model->hasAttribute('update_time')) {
    echo <<<EOT
        
            'update_time' => \$nowTime, // 更新时间
EOT;
}

if ($model->hasAttribute('action_uid')) {
    echo <<<EOT
       
            'action_uid' => \Yii::\$app->getUser()->id, // 操作者
EOT;
}
echo <<<EOT
       
        ]);
EOT;
if (property_exists($schema, 'columns') && !empty($schema->columns[$pk]) && $schema->columns[$pk]->phpType == 'string') {
    echo <<<EOT
    
        // 编号
        if (\$this->getIsNewRecord()) \$this->setAttribute('id', ToolsService::newMongoId());
EOT;
}
if ($model->hasAttribute('add_time')) {
    echo <<<EOT
        
        // 添加时间
        if (\$this->getIsNewRecord()) \$this->setAttribute('add_time', \$nowTime);
EOT;
}
if ($model->hasAttribute('content')) {
    echo <<<EOT
    
        // 内容解密下 - 防止加密多次
        \$this->content = htmlspecialchars_decode(\$this->content);
        // 内容取出图片域名
        \$this->content = ToolsService::delHtmlImgDomain(\$this->content);
        // 内容加密下
        \$this->content = htmlspecialchars(\$this->content);
EOT;
}
echo <<<EOT

        
        // 检测
        if (\$this->hasErrors() || !\$this->validate()) {

            // 记录下错误日志
            \Yii::error([

                "`````````````````````````````````````````````````````````",
                "``                      数据库错误                       ``",
                "`` 错误详情: [$generator->expName]验证数据失败             ``",
                "`` 错误信息和参数详情:                                     ``",
                "`````````````````````````````````````````````````````````",
                \$this->getAttributes(),
                \$this->getErrors()
            ], 'db');
            return false;
        }
        
        // 需要 && 执行保存
        if (\$doSave && !\$this->save()) {

            // 记录下错误日志
            \Yii::error([

                "`````````````````````````````````````````````````````````",
                "``                      数据库错误                       ``",
                "`` 错误详情: [$generator->expName]保存数据失败             ``",
                "`` 错误信息和参数详情:                                     ``",
                "`````````````````````````````````````````````````````````",
                \$this->getAttributes(),
                \$this->getErrors()
            ], 'db');
            return false;
        }

        return true;
    }
    
    /**
     * 导出到[excel]文件
     * @param \$filePath
     * @param \$records
     * @return bool
     */
    public function exportExcel(\$filePath, \$records)
    {

        ### 判断结果
        if (empty(\$records)) return true;

        try {

            ### 执行表格
            // 配置
            \$config = self::\$exportConfig['field'];
            // 实例化
            \$spreadsheet = new Spreadsheet();
            // 获取活动工作薄
            \$sheet = \$spreadsheet->getActiveSheet();
            // 当前此次添加开始sheet数
            if (file_exists(\$filePath)) {
                \$reader = new ReadExcel();
                \$index  = \$reader->load(\$filePath)->getActiveSheetIndex();
            } else {
                \$index = 1;
                ### 先添加标头
                \$i = 0;
                foreach (\$config as \$k => \$v) {
                    
                    // 最终数据
                    \$sheet->getCell(chr(65 + \$i) . '1')->setValue(
                        \$v
                    )->getStyle()->applyFromArray([
                        'font' => [
                            'bold' => true,
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_LEFT, // 水平居中
                        ],
                    ]);
                    \$i++;
                }
            }

            ### 添加数据
            // 循环表格
            \$i = 1;
            foreach (\$records as \$item) {
                \$ci = 0; // 字段增加
                foreach (\$config as \$k2 => \$v2) {
                    if (empty(\$item[\$k2])) continue;
                    
                    // 时间格式化
                    if (strstr(\$k2, 'time')) {
                        \$item[\$k2] = date('Y-m-d H:i:s', \$item[\$k2]);
                    }
                    
                    // 获取单元格
                    \$cellA = \$sheet->getCell(chr(65 + \$ci) . (\$index + \$i));
                    \$cellA->getStyle()->applyFromArray([
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_LEFT,
                        ],
                    ]);
                    // 给单元格赋值
                    \$cellA->setValue(\$item[\$k2]);
                    \$ci++;
                }
                \$i++;
            }

            // 先建立下文件夹
            ToolsService::mkdir(dirname(\$filePath));
            // Xlsx类 将电子表格保存到文件
            \$writer = new WriteExcel(\$spreadsheet);
            \$writer->save(\$filePath);

            return true;
        } catch (\Exception \$error) {

            \$this->addError(500, \$error->getMessage());
            return false;
        }
    }
    
    /**
     * 导入Excel
     * @param \$filePath string 文件路径
     * @param \$page int 第几页
     * @param \$pageSize int 分页大小
     * @return bool
     */
    public function importExcel(\$filePath, \$page, \$pageSize)
    {

        try {

            ### 执行表格
            // 配置
            \$config = self::\$importConfig['field'];
            // 当前此次添加开始sheet数
            \$activeSheet = IOFactory::load(\$filePath)->getActiveSheet();
            // 总行数
            \$total = \$activeSheet->getHighestRow();
            // 此文件总页数赋值
            \$importTotalPage = ToolsService::decimalUp(\$total / \$pageSize, 0);
            \$this->setAttributes(['importTotalPage' => \$importTotalPage]);
            // 此次操作最大行
            \$maxLine = (\$page * \$pageSize);
            // 此次操作的最小行
            \$miniLine = (\$page * \$pageSize - (\$pageSize - 2));
            if (\$miniLine > \$total) {
                \$this->addError(404, '没有此页数据');
                return false;
            }

            ### 添加数据
            // 数据塑造
            \$addData = [];
            for (\$i = \$miniLine; \$i <= \$maxLine; \$i++) {
                // 当前行数大于总数
                if (\$i > \$total) break;
                \$row     = [];
                \$configI = 0;
                foreach (\$config as \$k => \$v) {
                    \$row[\$k] = \$activeSheet->getCell(chr(65 + \$configI) . \$i)->getCalculatedValue();
                    \$configI = \$configI + 1;
                }
                \$addData[] = \$row;
            }
            // 执行添加 - 只有要添加数据不为空
            \$result = !empty(\$addData) ? self::createData(\$addData) : false;
            if (!\$result) {
                \$this->addError(500, '数据提交保存失败');
                return false;
            }

            return true;
        } catch (\Exception \$error) {

            \$this->addError(500, \$error->getMessage());
            return false;
        }
    }

    
    /**
     * [静态方法]批量快速更新某些字段|PS：无验证，请在调用此方法前做好各字段验证
     * @param \$condition
     * @param array \$fieldVal
     * @return bool
     */
    public static function updateField(\$condition, \$fieldVal = [])
    {

        \$model = new self();
        foreach (\$fieldVal as \$k => \$v) {

            // 没有字段删除
            if (!\$model->hasAttribute(\$k)) {unset(\$fieldVal[\$k]);continue;}

            // 字段转[JSON]
            if (is_array(\$v)) \$fieldVal[\$k] = json_encode(\$fieldVal, JSON_UNESCAPED_UNICODE);
        }

        \$db = \Yii::\$app->db->createCommand();

        try {

            \$db->update(self::tableName(), \$fieldVal, \$condition)->execute();

            // 否则成功
            return true;
        } catch (\Exception \$error) {

            // 记录下错误日志
            \Yii::error([

                "`````````````````````````````````````````````````````````",
                "``                      数据库错误                       ``",
                "`` 错误详情: [$generator->expName]批量修改[指定字段]失败，   ``",
                "``         {\$error->getMessage()}                       ``",
                "`` SQL语句: {\$db->getRawSql()}                         ``",
                "`` 错误信息和参数详情:                                     ``",
                "`````````````````````````````````````````````````````````",
                \$error->getTraceAsString()
            ], 'db');

            // 静态错误
            self::\$error_['db_error'] = empty(self::\$error_['db_error']) ?
                [\$error->getMessage()] : array_merge(self::\$error_['db_error'], [\$error->getMessage()]);

            return false;
        }
    }
    
    /**
     * 批量添加数据|ps.请事先做好字段数据校验
     * @param array \$createData
     * @return bool
     */
    public static function createData(\$createData = [])
    {

        \$db = \Yii::\$app->db->createCommand();

        try {
        
            ### 先数据格式化
            \$values = [];
            foreach (\$createData as \$k => \$v) {
    
                \$model = self::loadModel();
                \$model->load(\$createData[\$k], '');
                if (!\$model->saveData(false)) {
                    // 取出错误信息
                    \$error = ToolsService::getModelError(\$model->errors);
                    // 添加到静态方法上
                    self::\$error_[\$error['column']] = [\$error['msg']];
                    return false;
                }
        
                \$createData[\$k] = \$model->getAttributes(\$model::getTableSchema()->getColumnNames());
                
                // 循环一些数据
                foreach (\$createData[\$k] as \$kc => \$vc) {
                    // 字段类型为[JSON]类型需要转为[JSON]
                    if (is_array(\$vc)) {
                        \$createData[\$k][\$kc] = json_encode(\$vc, JSON_UNESCAPED_UNICODE);
                        continue;
                    }
                }
                
                // 值赋值
                \$values[] = array_values(\$createData[\$k]);
            }
            
            ### 取出此次操作的字段列表
            \$columns = !current(\$createData) ? [] : array_keys(current(\$createData));

            // 执行
            \$addResult = \$db->batchInsert(self::tableName(), \$columns, \$values)->execute();

            return \$addResult;
        } catch (\Exception \$error) {

            // 记录下错误日志
            \Yii::error([

                "`````````````````````````````````````````````````````````",
                "``                      数据库错误                       ``",
                "`` 错误详情: [$generator->expName]批量添加[数据]失败，      ``",
                "``         {\$error->getMessage()}                       ``",
                "`` SQL语句: {\$db->getRawSql()}                         ``",
                "`` 错误信息和参数详情:                                     ``",
                "`````````````````````````````````````````````````````````",
                \$error->getTraceAsString()
            ], 'db');
            
            // 静态错误
            self::\$error_['db_error'] = empty(self::\$error_['db_error']) ?
                [\$error->getMessage()] : array_merge(self::\$error_['db_error'], [\$error->getMessage()]);

            return false;
        }
    }
    
    /**
     * 更新某些字段自增|自减
     * @param \$condition
     * @param array \$fieldVal 增/减加的字段
     * @return bool
     */
    public static function updateCounter(\$condition, \$fieldVal = [])
    {

        \$model = new self();
        foreach (\$fieldVal as \$k => \$v) {

            if (!\$model->hasAttribute(\$k)) {

                unset(\$fieldVal[\$k]);
                continue;
            }
        }

        try {

            \$model->updateAllCounters(\$fieldVal, \$condition);

            // 否则成功
            return true;
        } catch (\Exception \$error) {

            // 记录下错误日志
            \Yii::error([
                "`````````````````````````````````````````````````````````",
                "``                      数据库错误                       ``",
                "`` 错误详情: [$generator->expName]批量增/减[指定字段]失败   ``",
                "``         {\$error->getMessage()}                       ``",
                "`` 错误信息和参数详情:                                     ``",
                "`````````````````````````````````````````````````````````",
                \$error->getTraceAsString()
            ], 'db');

            self::\$error_ = empty(\$error->errorInfo) ?
                \$error->getMessage() :
                implode(' | ', \$error->errorInfo);

            return false;
        }
    }
    
EOT;

if ($model->hasAttribute('sort') || $model->hasAttribute('list_order')) {
    echo <<<EOT
    
    
    /**
     * 返回排序最大值
     * @return int
     */
    public static function getMaxSort()
    {
        return self::\$maxSort;
    }
    /**
     * 返回排序最小值
     * @return int
     */
    public static function getMinSort()
    {
        return self::\$minSort;
    }
    
EOT;
}
if (property_exists($schema, 'columns')) {
    foreach ($schema->columns as $k => $v) {
        // 数据库类型不存在下一循
        if (!property_exists($v, 'dbType')) continue;

        // 如果是枚举数字类型则进行渲染 枚举列表
        if (strstr($v->dbType, 'tinyint')) {

            # 保证说明
            $comment = property_exists($v, 'comment') ? $v->comment : '--';

            # [ucwords]将每个单词的首字母大写
            # [str_replace]字符串替换
            $capFirstName = ucwords(str_replace('_', ' ', $v->name));
            # [ucfirst]将所有的字符串首字母大写；
            $capFirstName = str_replace(' ', '', ucfirst($capFirstName));
            # 首字母小写
            $lowFirstName = lcfirst($capFirstName);
            if ($v->name == 'status') {
                echo <<<EOT


    /**
     * 获取[状态][关闭]值
     * @return mixed
     */
    public static function getStatusDisabled()
    {
    
        \$list = array_column(self::\$statusList, null, 'key');
        return \$list['disabled']['value'];
    }
    /**
     * 获取[状态][开启]值
     * @return mixed
     */
    public static function getStatusOpen()
    {
        
        \$list = array_column(self::\$statusList, null, 'key');
        return \$list['open']['value'];
    }
EOT;

            }
            echo <<<EOT

    /**
     * 获取[{$comment}]文本
     * @param \$value
     * @return mixed|string
     */
    public static function get{$capFirstName}Text(\$value)
    {

        // 列表
        \$list = array_column(self::\${$lowFirstName}List, null, 'value');
        // 不合法 - 不存在
        if (empty(\$list[\$value]['text'])) return '--';

        // 最终正常返回
        return \$list[\$value]['text'];
    }
    /**
     * 获取[{$comment}]列表 值
     * @return mixed|string
     */
    public static function get{$capFirstName}List()
    {

        // 最终正常返回
        return self::\${$lowFirstName}List;
    }
    
EOT;
        }
    }
}
echo <<<EOT

    /**
     * 获取静态错误
     * @return mixed
     */
    public static function getStaticErrors()
    {
        return self::\$error_;
    }
}
EOT;
