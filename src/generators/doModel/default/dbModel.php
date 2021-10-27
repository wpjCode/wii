<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2020-07-21
 * Time: 16:05
 */

use \yii\helpers\StringHelper;

/* @var $generator wpjCode\wii\generators\doModel\Generator */
$schema = $generator->getTableSchema();

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

use app\models\CommonModel;
use yii\db\Expression;
use yii\db\ExpressionInterface;
use yii\helpers\ArrayHelper;
use yii\db\Exception;

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
            $comment = property_exists($v, 'comment')?$v->comment:'--';

            # [ucwords]将每个单词的首字母大写
            # [str_replace]字符串替换
            $capFirstName = ucwords(str_replace('_',' ',$v->name));
            # [ucfirst]将所有的字符串首字母大写；
            $capFirstName = str_replace(' ','',ucfirst($capFirstName));
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

        'disabled' => -1,
        'default' => 0,
        'open' => 1\r
    
EOT;
                    break;
            }
            echo <<<EOT
];
    /**
     * {$comment}文本 列表
     * @var array
    */
    private static \${$lowFirstName}TextList = [
EOT;
            switch ($v->name) {
                // 状态默认值
                case 'status':
                    echo <<<EOT

        -1 => '禁用',
        0 => '审核',
        1 => '开启'\r
    
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
        $capFirstName = ucwords(str_replace('_',' ',$v->name));
        # [ucfirst]将所有的字符串首字母大写；
        $capFirstName = str_replace(' ','',ucfirst($capFirstName));
        # 首字母小写
        $lowFirstName = lcfirst($capFirstName);
        echo <<<EOT

        // [{$v->comment}]列表
        \${$lowFirstName}List = array_values(self::get{$capFirstName}List());
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
        $capFirstName = ucwords(str_replace('_',' ',$v->name));
        # ucfirst 将所有的字符串首字母大写；
        $capFirstName = str_replace(' ','',ucfirst($capFirstName));
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
}else if ($model->hasAttribute('list_order')) {
    echo <<<EOT
    
            ['list_order', 'integer', 'max' => self::getMaxSort(), 'min' => self::getMinSort(),
                'message' => '排序不得超过{$maxSort}，不得小于{$minSort}'],
EOT;
}
// ******** 特殊 - [排序]渲染[rules] 结束 ********

echo <<<EOT

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
     * @param null \$id 编号
     * @param string \$scenario 场景
     * @return {$modelPath['filename']}
     *  ` PS:[\$id]空为何不返回[\$model::find()]: 因为可能准确想返回条目是否存在查询结果可能null,返回在find报错。
     */
    public static function loadModel(\$id = null, \$scenario = 'default')
    {

        // 实力化类
        \$model = new self();

        if (!empty(\$id) && \$id !== true) \$model = \$model::findOne(\$id);

        // 条目不存在或者需要[find]都返回 - 无法加载场景
        if (!\$model) return \$model;

        // 场景
        \$sceList = array_keys(\$model->scenarios());
        if (!empty(\$scenario) && in_array(\$scenario, \$sceList)) \$model->setScenario(\$scenario);

        return \$model;
    }

    /**
     * 初始化并返回当前基础[SQL]
     * @return \yii\db\ActiveQuery
     */
    protected function getSqlBase() {
        if (\$this->sqlBase) return \$this->sqlBase;
        \$this->sqlBase = \$this::find()->where(\$this->where);
        return \$this->sqlBase;
    }
    
    /**
     * 获取全部列表
     * @param integer \$page 当前页
     * @param integer \$limit 获取几条
     * @param null \$field 获取字段
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getList(\$page, \$limit, \$field = null)
    {

        // 当前页面计算
        \$page = ((\$page - 1) < 0 ? 0 : (\$page - 1));

        // 查找的 字段空的 就默认给列表
        if (!\$field) \$field = '*';

        // 基础 where加载完毕
        \$this->getSqlBase()->select(\$field);
            
        // 是否已经有自定义排序
        if (property_exists(\$this, 'orderBy') && !empty(\$this->orderBy)) {
            \$this->getSqlBase()->orderBy(\$this->orderBy);
        } else { // 无自定义排序
EOT;
if ($model->hasAttribute('sort') && $model->hasAttribute('update_time')) {
    echo <<<EOT
    
            \$this->getSqlBase()->orderBy('sort desc, update_time desc');
EOT;
} else if ($model->hasAttribute('sort') && $model->hasAttribute('id')) {
    echo <<<EOT
    
            \$this->getSqlBase()->orderBy('sort desc, id desc');
EOT;
} else if (!$model->hasAttribute('sort') && $model->hasAttribute('id')) {
    echo <<<EOT
    
            \$this->getSqlBase()->orderBy('id desc');
EOT;
} echo <<<EOT

        }
            
        // 数据的获取 分页等
        \$list = \$this->getSqlBase()->offset(\$page * \$limit)
            ->limit(\$limit)
            ->asArray()->all();

        // 格式化数据
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
} if ($model->hasAttribute('update_time')) {
    echo <<<EOT
    
    
            // 更新时间
            if (isset(\$v['update_time'])) {
                \$v['update_time_text'] = date('Y-m-d H:i:s', \$v['update_time']);
                \$v['update_time_text_s'] = date('Y-m-d', \$v['update_time']);
            }
EOT;
} if ($model->hasAttribute('content')) {
    echo <<<EOT
    
    
            // 内容转化下
            if (!empty(\$v['content'])) {
                \$v['content'] = htmlspecialchars_decode(\$v['content']);
                \$v['content'] = CommonModel::addHtmlImgHost(\$v['content']);
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
        $capFirstName = ucwords(str_replace('_',' ',$v->name));
        # ucfirst 将所有的字符串首字母大写；
        $capFirstName = str_replace(' ','',ucfirst($capFirstName));
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

        return \$list;
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
     * @param string|array \$sort 无需验证
     * @param bool \$noCheck 无需验证
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
        if (is_string(\$sort)) \$sort = str_replace(', ', ',', \$sort);
        // 如果排序是 字符
        if (is_string(\$sort)) \$sort = explode(',', \$sort);

        // 允许列表 - 无需验证
        \$toExpList = ['RAND()'];
        // 合法排序列表
        \$typeList = [SORT_DESC, SORT_ASC, 'DESC', 'ASC'];
        // 循环  条件是否有效
        \$stagingSort = [];
        foreach (\$sort as \$k => \$v) {

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
            // 值已经是 排序列表中的数据
            if (in_array(strtoupper(\$v), \$typeList) && \$this->hasAttribute(\$k)) {
                \$stagingSort[\$k] = strtoupper(\$v);
                continue;
            }

            // 字符串 - 分割空格号
            \$v = preg_split('/\s*/', strval(\$v));
            if (!empty(\$v[0]) && strlen(\$v[0]) > 0 && \$this->hasAttribute(\$v[0])) {

                \$stagingSort[\$v[0]] = strtoupper(\$v[1]);
                continue;
            }
        }

        // 条件最终赋值
        \$this->orderBy = \$stagingSort;

        return \$this;
    }

    /**
     * 添加|保存
     * @param bool \$dontSave 仅仅验证，不要提交保存
     * @return bool
     */
    public function saveData(\$dontSave = false)
    {

        \$nowTime = time();
        // 添加的话要赋值一些初始数据
        if (empty(\$this->id)) {
        
EOT;
if (property_exists($schema, 'columns') && $schema->columns['id']->type == 'string') {
    echo <<<EOT

            // 可以是走[mongoId]
            \$this->id = CommonModel::newMongoId();
EOT;
}

if ($model->hasAttribute('add_time')) {
    echo <<<EOT
    
            // 添加时间
            \$this->add_time = \$nowTime;
EOT;
}
echo <<<EOT

        }

EOT;
if ($model->hasAttribute('update_time')) {
    echo <<<EOT
    
        // 更新时间
        \$this->update_time = \$nowTime;
EOT;
}
if ($model->hasAttribute('action_uid')) {
    echo <<<EOT
    
        // 操作者
        \$this->action_uid = \Yii::\$app->getUser()->id;
EOT;
}
if ($model->hasAttribute('content')) {
    echo <<<EOT
    
    
        // 内容解密下 - 防止加密多次
        \$this->content = htmlspecialchars_decode(\$this->content);
        // 内容取出图片域名
        \$this->content = CommonModel::removeHtmlImgHost(\$this->content);
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
                \$this->getErrors()
            ], 'error');
            return false;
        }
        
        // 需要 && 执行保存
        if (!\$dontSave && !\$this->save()) {

            // 记录下错误日志
            \Yii::error([

                "`````````````````````````````````````````````````````````",
                "``                      数据库错误                       ``",
                "`` 错误详情: [$generator->expName]保存数据失败             ``",
                "`` 错误信息和参数详情:                                     ``",
                "`````````````````````````````````````````````````````````",
                \$this->getErrors()
            ], 'error');
            return false;
        }

        return true;
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

            if (!\$model->hasAttribute(\$k)) {

                unset(\$fieldVal[\$k]);
                continue;
            }
        }

        \$db = \Yii::\$app->db->createCommand();

        try {

            \$db->update(self::tableName(), \$fieldVal, \$condition)->execute();

            // 否则成功
            return true;
        } catch (Exception \$error) {

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
            ], 'error');

            self::\$error_ = empty(\$error->errorInfo) ?
                \$error->getMessage() :
                implode(' | ', \$error->errorInfo);

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

        foreach (\$createData as \$k => \$v) {

            \$model = self::loadModel();
            \$model->load(\$createData[\$k], '');
            if (!\$model->saveData(true)) {
                // 取出错误信息
                \$error = CommonModel::getModelError(\$model->errors);
                // 添加到静态方法上
                self::\$error_[\$error['column']] = \$error['msg'];
                return false;
            }

            \$createData[\$k] = \$model->getAttributes(array_keys(\$model->attributeLabels()));
        }

        try {

            // 还行写入多条
            \$addResult = \$db->batchInsert(
                self::tableName(), array_keys(self::loadModel()->attributeLabels()), \$createData
            )->execute();

            return \$addResult;
        } catch (Exception \$error) {

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
            ], 'error');

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
            ], 'error');

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
        return self::\$statusList['disabled'];
    }
    /**
     * 获取[状态][开启]值
     * @return mixed
     */
    public static function getStatusOpen()
    {
        return self::\$statusList['open'];
    }
EOT;

            }
            echo <<<EOT


    /**
     * 获取[{$comment}]文本
     * @param \$value
     * @return mixed|string
     */
    public static function get{$capFirstName}Text(\$value) {

        // 列表
        \$list = self::\${$lowFirstName}TextList;
        // 不合法 - 不存在
        if (empty(\$list[\$value])) return '--';

        // 最终正常返回
        return \$list[\$value];
    }
    /**
     * 获取[{$comment}]列表 值
     * @return mixed|string
     */
    public static function get{$capFirstName}List() {

        // 最终正常返回
        return self::\${$lowFirstName}List;
    }
    /**
     * 获取[{$comment}]文本列表 值
     * @return mixed|string
     */
    public static function get{$capFirstName}TextList() {

        // 最终正常返回
        return self::\${$lowFirstName}TextList;
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
