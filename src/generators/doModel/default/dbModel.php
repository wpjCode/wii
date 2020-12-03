<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2020-07-21
 * Time: 16:05
 */

use \yii\helpers\StringHelper;

/* @var $generator wpjCode\wii\generators\doModel\Generator */

/* @var $model \yii\db\ActiveRecord */
$model = new $generator->baseModelClass();

$modelClass = $generator->getRenderFilePath();
$modelPath = pathinfo($modelClass);

$baseModelClass = str_replace('\\', '/', $generator->baseModelClass);
$baseModelPath = pathinfo($baseModelClass);

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
use yii\helpers\ArrayHelper;
use yii\db\Exception;

/**
 * {$generator->expName}
 * User: Administrator
 * Date: $createDate
 * Time: $createTime
 */
class {$modelPath['filename']} extends {$baseModelPath['filename']}
{

EOT;
if ($model->hasAttribute('status')) {
echo <<<EOT
    
    /**
     * 状态 列表
     * @var array
     */
    private static \$statusList = [
        'disabled' => -1,
        'normal' => 0,
        'open' => 1
    ];
    /**
     * 状态文本 列表
     * @var array
     */
    private static \$statusTextList = [
        -1 => '禁用',
        0 => '审核',
        1 => '开启'
    ];
    
EOT;
} if ($model->hasAttribute('type')) {
    echo <<<EOT
    
 
    /**
     * 类型 列表
     * @var array
     */
    private static \$typeList = [
        'type1' => 1,
        'type2' => 2
    ];
    /**
     * 类型文本 列表
     * @var array
     */
    private static \$typeTextList = [
        1 => '类型一(请自行完善)',
        2 => '类型二(请自行完善)'
    ];
    
EOT;
} if ($model->hasAttribute('sort') || $model->hasAttribute('list_order')) {
        echo <<<EOT
    
    
    /**
     * 排序最大值
     * @var int
     */
    protected static \$sortMax = 999999;
    /**
     * 排序最小值
     * @var int
     */
    protected static \$sortMin = -999999;
    
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

        \$parent = parent::rules();
EOT;

// ******** 有[状态]渲染状态列表 开始 ********
if ($model->hasAttribute('status')) {
    echo <<<EOT
    
        // 状态
        \$statusList = array_values(self::getStatList());
EOT;
}
// ******** 有[状态]渲染状态列表 结束 ********

// ******** 有[类型]渲染状态列表 开始 ********
if ($model->hasAttribute('type')) {
    echo <<<EOT
    
        // 类型
        \$typeList = array_values(self::getTypeList());
EOT;
}
// ******** 有[类型]渲染状态列表 结束 ********
    echo <<<EOT
        

        return ArrayHelper::merge(\$parent, [
EOT;

// ******** 有[状态]渲染[rules] 开始 ********
if ($model->hasAttribute('status')) {
    echo <<<EOT
    
            ['status', 'in', 'range' => \$statusList, 'message' => '状态不合法'],
EOT;
}
// ******** 有[状态]渲染规则 结束 ********

// ******** 有[类型]渲染[rules] 开始 ********
if ($model->hasAttribute('type')) {
    echo <<<EOT
    
            ['type', 'in', 'range' => \$typeList, 'message' => '类型不合法'],
EOT;
}
// ******** 有[类型]渲染规则 结束 ********

// ******** 有[排序]渲染规则 开始 ********
if ($model->hasAttribute('sort') || $model->hasAttribute('list_order')) {
        echo <<<EOT
    
            ['sort', 'integer', 'max' => self::getSortMax(), 'min' => self::getSortMin(),
                'message' => '排序不得超过999999，不得小于-999999'],
EOT;
}
// ******** 有[排序]渲染规则 结束 ********

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
// ******** 判断某些字段是否存在预设一些字段的label ********
    if ($model->hasAttribute('id')) {
        echo <<<EOT
    
            'id' => '编号',
EOT;
} if ($model->hasAttribute('title')) {
        echo <<<EOT
    
            'title' => '标题',
EOT;
} if ($model->hasAttribute('name')) {
        echo <<<EOT
    
            'name' => '名称',
EOT;
} if ($model->hasAttribute('role')) {
        echo <<<EOT
    
            'role' => '角色',
EOT;
} if ($model->hasAttribute('content')) {
        echo <<<EOT
    
            'content' => '内容',
EOT;
} if ($model->hasAttribute('add_time')) {
        echo <<<EOT
    
            'add_time' => '添加时间',
EOT;
} if ($model->hasAttribute('update_time')) {
        echo <<<EOT
    
            'update_time' => '更新时间',
EOT;
} if ($model->hasAttribute('action_uid')) {
        echo <<<EOT
    
            'action_uid' => '操作者编号',
EOT;
} if ($model->hasAttribute('sort')) {
        echo <<<EOT
    
            'sort' => '排序',
EOT;
} if ($model->hasAttribute('status')) {
    echo <<<EOT
    
            'status' => '状态',
EOT;
} if ($model->hasAttribute('type')) {
    echo <<<EOT
    
            'type' => '类型',
EOT;
}
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
     * 获取全部列表
     * @param \$page
     * @param \$limit
     * @param null \$filed
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getList(\$page, \$limit, \$filed = null)
    {

        // 条件
        \$where = \$this->where;

        // 当前页面计算
        \$page = ((\$page - 1) < 0 ? 0 : (\$page - 1));

        // 查找的 字段空的 就默认给列表
        if (!\$filed) \$filed = '*';


        // 基础 where加载完毕
        \$this->sqlBase = \$this::find()
            ->select(\$filed)
            ->where(\$where);

        // 数据的获取 分页等
        \$list = \$this->sqlBase->offset(\$page * \$limit)
            ->limit(\$limit)
EOT;
if ($model->hasAttribute('sort') && $model->hasAttribute('update_time')) {
    echo <<<EOT
    
            ->orderBy('sort desc, update_time desc')
EOT;
} else if ($model->hasAttribute('sort') && $model->hasAttribute('id')) {
        echo <<<EOT
    
            ->orderBy('sort desc, id desc')
EOT;
} else if (!$model->hasAttribute('sort') && !$model->hasAttribute('id')) {
        echo <<<EOT
    
            ->orderBy('id desc')
EOT;
} echo <<<EOT

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
} if ($model->hasAttribute('status')) {
    echo <<<EOT
    
    
            // 状态 文本
            if (isset(\$v['status'])) {
                \$v['status_text'] = self::getStatusText(\$v['status']);
            }
EOT;
} if ($model->hasAttribute('type')) {
    echo <<<EOT
    
    
            // 类型 文本
            if (isset(\$v['type'])) {
                \$v['type_text'] = self::getTypeText(\$v['type']);
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
} echo <<<EOT

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
        if (empty(\$this->sqlBase->where)) {
            \$this->sqlBase->where(\$this->where);
        }
        // 基础 where加载完毕
        \$count = \$this->sqlBase->count();

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
        if (empty(\$where)) {

            return \$this;
        }

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
            if (is_array(\$v) && count(\$v) > 0 && \$this->hasAttribute(\$k)) {

                \$stagingWhere[] = ['IN', \$k, array_values(\$v)];
                continue;
            }

            // 字符串 - 首先值是有的，不能是空
            if (strlen(\$v) > 0 && \$this->hasAttribute(\$k)) {   

                \$stagingWhere[] = ['=', \$k, \$v];
                continue;
            }
        }

        // 条件最终赋值
        \$this->where = \$stagingWhere;

        return \$this;
    }

    /**
     * 添加|保存
     * @return bool
     */
    public function saveData()
    {

        \$nowTime = time();
        // 添加的话要赋值一些初始数据
        if (empty(\$this->id)) {

            // 可以是走[mongoId]
            \$this->id = CommonModel::newMongoId();
EOT;
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
    
        // 内容取出图片域名
        \$this->content = CommonModel::removeHtmlImgHost(\$this->content);
        // 内容加密下
        \$this->content = htmlspecialchars(\$this->content);
EOT;
    }
    echo <<<EOT

        if (\$this->hasErrors() || !\$this->validate() ||  !\$this->save()) {

            // 记录下错误日志
            \Yii::error([

                "`````````````````````````````````````````````````````````",
                "``                      数据库错误                       ``",
                "`` 错误详情: [$generator->expName]保存数据失败             ``",
                "`` 错误信息和参数详情:                                     ``",
                "`````````````````````````````````````````````````````````",
                \$this->getErrors()
            ], 'normal');
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
            ], 'normal');

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
        try {

            // 还行写入多条
            \$addResult = \$db->batchInsert(self::tableName(),
                [
EOT;
    foreach ($model->attributes as $k => $v) {
        echo <<<EOT

                     '$k',
EOT;
    }
    echo <<<EOT

                ], \$createData
            )->execute();

            return true;
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
            ], 'normal');

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
            ], 'normal');

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
    public static function getSortMax()
    {
        return self::\$sortMax;
    }
    /**
     * 返回排序最小值
     * @return int
     */
    public static function getSortMin()
    {
        return self::\$sortMin;
    }
    
EOT;
    }
if ($model->hasAttribute('status')) {
    echo <<<EOT
    

    /**
     * 获取[正常]状态 值
     * @return mixed|string
     */
    public static function getStatNormal() {

        // 最终正常返回
        return self::\$statusList['normal'];
    }
    /**
     * 获取[开启]状态 值
     * @return mixed|string
     */
    public static function getStatOpen() {

        // 最终正常返回
        return self::\$statusList['open'];
    }
    /**
     * 获取[禁用]状态 值
     * @return mixed|string
     */
    public static function getStatDisabled() {

        // 最终正常返回
        return self::\$statusList['disabled'];
    }
    /**
     * 获取[状态]文本
     * @param \$value
     * @return mixed|string
     */
    public static function getStatusText(\$value) {

        // 列表
        \$list = self::\$statusTextList;
        // 不合法 - 不存在
        if (empty(\$list[\$value]))

            return '--';

        // 最终正常返回
        return \$list[\$value];
    }
    /**
     * 获取[状态]列表 值
     * @return mixed|string
     */
    public static function getStatList() {

        // 最终正常返回
        return self::\$statusList;
    }
    /**
     * 获取[状态]文本列表 值
     * @return mixed|string
     */
    public static function getStatusTextList() {

        // 最终正常返回
        return self::\$statusTextList;
    }
    
EOT;
} if ($model->hasAttribute('type')) {
    echo <<<EOT
    
    /**
     * 获取[类型]文本
     * @param \$value
     * @return mixed|string
     */
    public static function getTypeText(\$value) {
    
        // 列表
        \$list = self::\$typeTextList;
        // 不合法 - 不存在
        if (empty(\$list[\$value]))
    
            return '--';
    
        // 最终正常返回
        return \$list[\$value];
    }
    /**
     * 获取[类型]列表 值
     * @return mixed|string
     */
    public static function getTypeList() {
    
        // 最终正常返回
        return self::\$typeList;
    }
    /**
     * 获取[类型]文本列表 值
     * @return mixed|string
     */
    public static function getTypeTextList() {
    
        // 最终正常返回
        return self::\$typeTextList;
    }
    
EOT;
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

