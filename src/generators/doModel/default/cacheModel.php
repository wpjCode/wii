<?php
/**
 * This is the template for generating the model class of a specified table.
 */

use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator wpjCode\wii\generators\doModel\Generator */

// [要生成]类信息
$renderModel = str_replace('\\', '/', $generator->nameSpace);
$renderModelPath = pathinfo($renderModel);

// [继承的基础]类信息
$baseModel = str_replace('\\', '/', $generator->baseModelClass);
$baseModelPath = pathinfo($baseModel);

// [数据库操作]类信息
$doDbModel = str_replace('\\', '/', $generator->doDbModel);
$doDbModel = pathinfo($doDbModel);
if (strstr($doDbModel['filename'], 'Model')) {
    $doDbModel['filename'] = preg_replace("/(Model)/", 'DbModel',
        $doDbModel['filename']
    );
    $doDbModel['filename'] = preg_replace("/(model)/", 'DbModel',
        $doDbModel['filename']
    );
} else {
    $doDbModel['filename'] = $doDbModel['filename'] . 'Db';
}
$doDbAlias = $doDbModel['filename'];

$times = time();
$createDate = date('Y/m/d', $times);
$createTime = date('H:i:s', $times);

/* @var $model \yii\redis\ActiveRecord */
$model = new $generator->baseModelClass();
/* @var $dbModel \yii\db\ActiveRecord */
$getTableSchema = $generator->getTableSchema();
// 主键
$primaryKey = empty($getTableSchema->primaryKey[0]) ? 'id' : $getTableSchema->primaryKey[0];

echo "<?php";
echo "\n\nnamespace " .
    StringHelper::dirname(ltrim($generator->nameSpace, '\\')) .
    ';';

if ($renderModelPath['dirname'] != $baseModelPath['dirname']) {
    echo "\n\nuse " . $generator->baseModelClass . ';';
}
echo <<<EOT

use app\service\ToolsService;
use {$generator->doDbModel} as {$doDbAlias};
use yii\helpers\ArrayHelper;
use yii\db\ExpressionInterface;
use yii\db\Expression;

/**
 * {$generator->expName} 缓存[Model]
 * 作者: Editor Name
 * 日期: {$createDate}
 * 时间: {$createTime}
 */
class {$renderModelPath['filename']} extends {$baseModelPath['filename']}
{

    /**
     * 数据库实例
     * @var {$doDbAlias}
     */
    private \$dbInstance;
    /**
     * 基础[SQL]
     * @var \yii\\redis\ActiveQuery
    */
    private \$sqlBase;
    /**
     * 条件暂存
     * @var
    */
    private \$where;
    /**
     * 排序
     * @var array
    */
    private \$orderBy = [];
    /**
     * 错误信息暂存
     * @var
    */
    private static \$error_;

    public function __construct(array \$config = [])
    {

        parent::__construct(\$config);
        // 先初始化下类
        \$this->setDbInstance();
    }

    /**
     * 规则验证
     * @return array
     */
    public function rules()
    {

        // 走数据库的规则
        \$parent = parent::rules();

        return ArrayHelper::merge(\$parent, []);
    }

    /**
     * 字段属性
     * @return array
     */
    public function attributes()
    {

        return ArrayHelper::merge(parent::attributes(), [

        ]);
    }

    /**
     * 字段文本
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), []);
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
     * @return {$renderModelPath['filename']}
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
     * 初始化[数据库]实例
     *  ` 逻辑：先从缓存中获取条目，条目不存在，从数据库拿，并存入缓存，返回[self] | 可以强制必须获取数据库`
     * @param string \$id 数据编号|直接初始化
     * @param string \$scenario 场景|ps.缓存类场景
     * @param bool \$forceDb 是否强制数据库
     * @return {$renderModelPath['filename']}
     */
    public static function loadModelDB(\$id = null, \$forceDb = false, \$scenario = 'default')
    {
        
        ### 先查询缓存
        // 查询缓存是否存在条目
        \$model = self::loadModel(\$id, \$scenario);

        ### 强制数据库 || 缓存未找到
        \$dbModel = false;
        if (!\$model || \$forceDb) \$dbModel = {$doDbAlias}::loadModel(\$id);

        // 数据库查询的空 && 数据库缓存空 - 返回数据空
        if (!\$model && !\$dbModel) return null;

        ### 数据库 -> 缓存同步数据
        // 缓存为空 初始化缓存
        if (!\$model) \$model = self::loadModel(true, \$scenario);
        // 需要把数据库[attribute]赋值到缓存
        if (\$dbModel) {
            \$attribute = \$dbModel->getAttributes();
            \$model->setAttributes(\$attribute);
        }

        // 如果数据是新的 先保存下
        if (\$forceDb && \$model->isNewRecord) {
            \$model->saveData(false);
            \$model->setIsNewRecord(false); // 同步之后就不是新建了
        }


        // 赋值数据库实例
        \$model->setDbInstance(\$dbModel);

        return \$model;
    }


    /**
     *  赋值数据库实例
     * @param AdminRoleDBModel|bool \$dbModel
     * @return {$renderModelPath['filename']}
     */
    public function setDbInstance(\$dbModel = false)
    {

        if (!\$dbModel) {
            \$this->dbInstance = {$doDbAlias}::loadModel();
        } else {
            \$this->dbInstance = \$dbModel;
        }
        return \$this;
    }

    /**
     * 获取数据库实例
     * @return {$renderModelPath['filename']}
     */
    public function getDbInstance()
    {
        return \$this->dbInstance;
    }
    
    /**
     * 初始化并返回当前基础[SQL]
     * @return \yii\\redis\ActiveQuery
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
if ($model->hasAttribute('sort') && $model->hasAttribute('update_time')) {
    echo <<<EOT
    
            \$this->sqlBase->orderBy('sort desc');
EOT;
} else if ($model->hasAttribute('sort') && $primaryKey) {
    echo <<<EOT
    
            \$this->sqlBase->orderBy('sort desc');
EOT;
} else if (!$model->hasAttribute('sort') && $primaryKey) {
    echo <<<EOT
    
            \$this->sqlBase->orderBy('{$primaryKey} desc');
EOT;
}
echo <<<EOT

        }
        
        return \$this->sqlBase;
    }

    /**
     * 获取全部列表
     * @param int \$page 当前页
     * @param int \$limit 展示多少条
     * @param array \$opt 其他设置
     * @return array|\yii\\redis\ActiveRecord[]
     */
    public function getList(\$page, \$limit, \$opt = [])
    {

        // 当前页面计算
        \$page = ((\$page - 1) < 0 ? 0 : (\$page - 1));

        // 基础 where加载完毕
        \$this->getSqlBase();
            
        // 数据的获取 分页等
        \$list = \$this->getSqlBase()->offset(\$page * \$limit)
            ->limit(\$limit)
            ->asArray()->all();

        // 数据库实例
        \$dbInstance = \$this->dbInstance;

        // 格式化数据
        foreach (\$list as \$k => &\$v) {
EOT;
if ($model->hasAttribute('update_time')):
    echo <<<EOT


            // 更新时间
            if (!empty(\$v['update_time'])) {
                \$v['update_time_text'] = date('Y-m-d H:i:s', \$v['update_time']);
                \$v['update_time_text_s'] = date('Y-m-d', \$v['update_time']);
            }
EOT;
endif;
if ($model->hasAttribute('status')):
    echo <<<EOT


            // 状态文本
            if (isset(\$v['status'])) {
                \$v['status_text'] = \$dbInstance::getStatusText(\$v['status']);
            }
EOT;
endif;
if ($model->hasAttribute('content')):
    echo <<<EOT


            // 内容转化下
            if (!empty(\$v['content'])) {
                \$v['content'] = htmlspecialchars_decode(\$v['content']);
                \$v['content'] = ToolsService::addHtmlImgDomain(\$v['content']);
            }
EOT;
endif;
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
            if (is_string(\$v) && strlen(\$v) > 0 && \$this->hasAttribute(\$k)) {

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
     * 同步|新建数据库数据
     * @return bool
     */
    public function saveDbData() {

        \$db = null;
        // 主键
        \$pk = \$this->getAttribute('id');
        // 需要新建 - 初始化
        \$db = {$doDbAlias}::loadModel(!empty(\$pk) ? \$pk : true);
        // 保存
        \$db->setAttributes(\$this->getAttributes());
        if (!\$db->saveData()) {\$this->addErrors(\$db->getErrors());return false;}
        // 数据库数据原封不动赋值下 - 以数据库为主
        \$this->setAttributes(\$db->getAttributes());

        return true;
    }
    
    /**
     * 添加|保存
     * @param bool \$saveDb 是否保存数据库
     * @return bool
     */
    public function saveData(\$saveDb = true)
    {

        \$dbTran = \Yii::\$app->db->beginTransaction();
            
        try {

             ### 先保存数据库
            // 查询数据库记录
            if (\$saveDb === true && !\$this->saveDbData()) {
                \$dbTran->rollBack();
                return false;
            }

            ### 保存此条缓存记录
            if (\$this->hasErrors() || !\$this->validate() || !\$this->save()) {
                \$dbTran->rollBack();
                // 记录下错误日志
                \Yii::error([

                    "`````````````````````````````````````````````````````````",
                    "``                      缓存保存错误                      ``",
                    "`` 错误详情: [$generator->expName]验证数据失败              ``",
                    "`` 错误信息和参数详情:                                     ``",
                    "`````````````````````````````````````````````````````````",
                    \$this->getErrors()
                ], 'cache');
                return false;
            }

            \$dbTran->commit();
            return true;

        } catch (Exception \$error) {

            \$dbTran->rollBack();
            // 记录下错误日志
            \Yii::error([

                "`````````````````````````````````````````````````````````",
                "``                      缓存保存错误                      ``",
                "`` 错误详情: [{$generator->expName}]出现错误               ``",
                "`` 错误信息和参数详情:                                     ``",
                "`````````````````````````````````````````````````````````",
                \$this->getErrors()
            ], 'cache');
            return false;
        }
    }

    /**
     * 更新某些字段
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

            // 数字字段转[JSON]
            if (is_array(\$v)) \$fieldVal[\$k] = json_encode(\$fieldVal, JSON_UNESCAPED_UNICODE);
        }

        try {  

            ### 取出已更新的数据库条目
            // 取出数据库条目
            \$dbList = {$doDbAlias}::find()->where(\$condition)->asArray()->all();
            // 数据库为空的直接为更新成功
            if (!\$dbList) return true;
            // 取出数据库编号
            \$dbIdList = array_column(\$dbList, '{$primaryKey}');

            ### 取出已更新的缓存条目
            \$cacheList = self::find()->where(\$condition)->asArray()->all();
            // 缓存条目为空的直接 默认空数组
            if (empty(\$cacheList)) \$cacheList = [];
            // 取出缓存编号
            \$cacheIdList = array_column(\$cacheList, '{$primaryKey}');

            ### 取出差集添加；取出交集更新
            // 数据库为主取出[差集]
            \$diffIdList = array_diff(\$dbIdList, \$cacheIdList);
            // 数据库为主取出[交集]
            \$interIdList = array_intersect(\$dbIdList, \$cacheIdList);

            ### 数据提交
            // 更新数据
            if (!empty(\$interIdList)) {$doDbAlias}::updateField(\$condition, \$fieldVal);

            // 添加数据
            \$insertData = [];
            foreach (\$diffIdList as \$k => \$v) {
                if (empty(\$dbList[\$v])) continue;
                \$insertData[] = \$dbList[\$v];
            }
            if (!empty(\$insertData)) (new self())->insert(false, \$insertData);

            // 否则成功
            return true;
        } catch (\Exception \$error) {

            // 记录下错误日志
            \Yii::error([
    
                "``````````````````````````````````````````````````````````",
                "``                       缓存错误                          ``",
                "`` 错误详情: [{$generator->expName}]缓存中修改[指定字段]失败       ``",
                "`` {\$error->getMessage()}                                ``",
                "`` 错误信息和参数详情:                                      ``",
                "`````````````````````````````````````````````````````````",
                \$error->getTraceAsString()
            ], 'cache');
    
            self::\$error_ = empty(\$error->errorInfo) ?
                \$error->getMessage() :
                implode(' | ', \$error->errorInfo);
    
            return false;
        }
    }

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
