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
     * 操作数据库实例
     *  ` 通过[loadModelDB]此值会是[true]
     * @var SettingDbModel
     */
    private \$doDb = false;
    /**
     * 基础[SQL]
     * @var \yii\redis\ActiveQuery
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

        return ArrayHelper::merge(\$parent, [
            [['doDb'], 'boolean']
        ]);
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
     * @return {$renderModelPath['filename']}
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
     * 初始化[数据库]实例
     *  ` 逻辑：先从缓存中获取条目，条目不存在，从数据库拿，并缓存，返回[self]
     * @param bool \$id 数据编号|直接初始化
     * @param bool \$sync 是否以数据库为主同步数据
     * @param string \$scenario 场景|ps.缓存类场景
     * @return {$renderModelPath['filename']}
     */
    public static function loadModelDB(\$id = true, \$sync = true, \$scenario = 'default')
    {
    
        ### 缓存数据
        // 查询缓存是否存在条目
        \$model = self::loadModel(\$id, \$scenario);

        ### 数据库数据
        \$dbModel = {$doDbAlias}::loadModel(\$id);
        // 数据库查询的空
        if (!\$dbModel) return null;

        ### 最终操作
        // 缓存数据空以数据库为准赋值下
        if (!\$model) {
            \$model = new self();
            \$model->setAttributes(\$dbModel->getAttributes());
            // 需要同步下
            if (\$sync) \$model->saveData();
        }
        // 加载数据库需要以数据库为准|否则保存数据时以缓存为准会出现类型错误
        \$model->load(array_merge(\$dbModel->getAttributes(), ['doDb' => true]), '');
        // 赋值数据库实例
        \$model->setDbInstance(\$dbModel);

        return \$model;
    }

    /**
     *  赋值数据库实例
     * @param {$doDbAlias}|bool \$dbModel
     * @return \$this
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
     * 初始化并返回当前基础[SQL]
     * @return \yii\redis\ActiveQuery
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
     * @return array|\yii\redis\ActiveRecord[]
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
                \$v['content'] = ToolsService::addHtmlImgHost(\$v['content']);
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
     * @param \$sort
     * @return \$this
     */
    public function loadSort(\$sort)
    {

        // 条件不存在
        if (empty(\$sort)) return \$this;

        // 如果排序是 字符
        if (is_string(\$sort)) \$sort = explode(',', \$sort);

        // 合法额排序列表
        \$typeList = [SORT_DESC, SORT_ASC, 'DESC', 'ASC'];
        // 循环  条件是否有效
        \$stagingSort = [];
        foreach (\$sort as \$k => \$v) {

            // 数组 - 过滤
            if (is_array(\$v)) continue;

            // 值已经是 排序列表中的数据
            if (in_array(strtoupper(\$v), \$typeList) && strtoupper(\$v)) {
                \$stagingSort[\$k] = strtoupper(\$v) == 'DESC' ? SORT_DESC : SORT_ASC;
                continue;
            }

            // 字符串 - 分割空格号
            \$v = preg_split('/\s+/', strval(\$v));
            if (!empty(\$v[0]) && strlen(\$v[0]) > 0 && \$this->hasAttribute(\$v[0])) {

                \$stagingSort[\$v[0]] = strtoupper(\$v[1]) == 'DESC' ? SORT_DESC : SORT_ASC;
                continue;
            }
        }

        // 条件最终赋值
        \$this->orderBy = \$stagingSort;

        return \$this;
    }

    /**
     * 添加|保存
     * @return bool
     */
    public function saveData()
    {

        ### 按需操作数据库
        // 检测是否数据库类型
        if (\$this->doDb && !\$this->dbInstance instanceof SettingDbModel) {
            \$this->addError(500, '请使用初始化数据库方式声明设置模块');
            return false;
        }
        // 加载数据
        if (\$this->doDb) \$this->dbInstance->load(\$this->getAttributes(), '');
        // 保存数据库
        if (\$this->doDb && !\$this->dbInstance->saveData(\$this->doDb)) {
            \$error = ToolsService::getModelError(\$this->dbInstance->getErrors());
            \$this->addError(\$error['column'], \$error['msg']);
            return false;
        }
        // 保存了数据看就需要把本条数据完全赋值到缓存以备保存
        if (\$this->doDb) \$this->load(\$this->dbInstance->getAttributes(), '');
        
        ### 缓存保存前一些格式化
        // 批量操作
        foreach (\$this->getAttributes() as \$k => \$v) {
            // 数组需要转为JSON字符串
            if (is_array(\$v)) \$this->setAttribute(\$k, json_encode(\$v, JSON_UNESCAPED_UNICODE));
        }
        // 单个操作
        \$nowTime = time();
        // 添加的话要赋值一些初始数据
        if (empty(\$this->isNewRecord)) {

            // 可以是走[mongoId] - 缓存的话暂时编号不存在叫他报错吧
            // {$primaryKey} = ToolsService::newMongoId();
EOT;
if ($model->hasAttribute('add_time')):
    echo <<<EOT

            // 添加时间
            \$this->add_time = \$nowTime;
EOT;
endif;
echo <<<EOT
    
        }
EOT;
if ($model->hasAttribute('update_time')):
    echo <<<EOT

        // 更新时间
        \$this->update_time = \$nowTime;
EOT;
endif;
if ($model->hasAttribute('content')):
    echo <<<EOT

        // 内容不为空
        if (!empty(\$this->content)) {
            // 内容加密下
            \$this->content = htmlspecialchars(\$this->content);
            // 内容取出图片域名
            \$this->content = ToolsService::removeHtmlImgHost(\$this->content);
        }
EOT;
endif;
echo <<<EOT


        if (\$this->hasErrors() || !\$this->validate() || !\$this->save()) {

            // 记录下错误日志
            \Yii::error([

                "`````````````````````````````````````````````````````````",
                "``                    缓存保存错误                       ``",
                "`` 错误详情: [$generator->expName]验证数据失败             ``",
                "`` 错误信息和参数详情:                                     ``",
                "`````````````````````````````````````````````````````````",
                \$this->getAttributes(),
                \$this->getErrors()
            ], 'error');
            return false;
        }

        return true;
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

            ### 更新下数据库数据
            {$doDbAlias}::updateField(\$condition, \$fieldVal);

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
            if (!empty(\$interIdList)) self::updateAll(\$fieldVal, \$condition);

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
            ], 'error');
    
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
