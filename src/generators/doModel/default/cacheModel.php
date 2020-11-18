<?php
/**
 * This is the template for generating the model class of a specified table.
 */

use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator wpjCode\wii\generators\doModel\Generator */
/* @var $tableName string full table name */
/* @var $className string class name */
/* @var $queryClassName string query class name */
/* @var $tableSchema yii\db\TableSchema */
/* @var $properties array list of properties (property => [type, name. comment]) */
/* @var $labels string[] list of attribute labels (name => label) */
/* @var $rules string[] list of validation rules */
/* @var $relations array list of relations (name => relation declaration) */

// [要生成]类信息
$renderModel = str_replace('\\', '/', $generator->nameSpace);
$renderModel = pathinfo($renderModel);

// [继承的基础]类信息
$baseModel = str_replace('\\', '/', $generator->baseModelClass);
$baseModel = pathinfo($baseModel);

// [数据库操作]类信息
$doDbModel = str_replace('\\', '/', $generator->doDbModel);
$doDbModel = pathinfo($doDbModel);
$doDbAlias = 'db' . $doDbModel['filename'];

$times = time();
$createDate = date('Y/m/d', $times);
$createTime = date('H:i:s', $times);

/* @var $model \yii\redis\ActiveRecord */
$model = new $generator->baseModelClass();

echo "<?php\n";

?>

namespace <?= StringHelper::dirname(ltrim($generator->nameSpace, '\\')) ?>;

use <?=$generator->baseModelClass?>;
use app\models\CommonModel;
use <?=$generator->doDbModel?> as <?=$doDbAlias?>;
use yii\helpers\ArrayHelper;

/**
 * <?=$generator->expName?> 缓存[Model]
 * User: jees
 * Date: <?=$createDate . "\n"?>
 * Time: <?=$createTime . "\n"?>
 */
class <?=$renderModel['filename']?> extends <?=$baseModel['filename'] . "\n"?>
{


    /**
     * 数据库实例
     * @var <?=$doDbAlias . "\n"?>
     */
    protected $dbInstance;
    /**
     * 条件暂存
     * @var
     */
    private $where;
    /**
     * 错误信息暂存
     * @var
     */
    private static $error_;

    public function __construct(array $config = [])
    {

        parent::__construct($config);

        // 先初始化下类
        $this->setDbInstance();
    }

    /**
     * 规则验证
     * @return array
     */
    public function rules()
    {

        // 走数据库的规则
        $parent = parent::rules();

        return ArrayHelper::merge($parent, [

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

        $scenarios = parent::scenarios();
        return ArrayHelper::merge($scenarios, [
            // 自定义场景 (无用请删除)
            'scUpdate' => [
                'someAttributes'
            ]
        ]);
    }

    /**
     * 加载整体[Model]
     * @param null $id 编号
     * @param string $scenario 场景
     * @return <?=$renderModel['filename'] . "\n"?>
     */
    public static function loadModel($id = null, $scenario = 'default')
    {

        // 实力化类
        $model = new self();

        if (!empty($id) && $id !== true) $model = $model::findOne($id);

        // 条目不存在或者需要[find]都返回 - 无法加载场景
        if (!$model) return $model;

        // 场景
        $sceList = array_keys($model->scenarios());
        if (!empty($scenario) && in_array($scenario, $sceList)) $model->setScenario($scenario);

        return $model;
    }

    /**
     * 初始化[数据库]实例
     *  ` 逻辑：先从缓存中获取条目，条目不存在，从数据库拿，并存入缓存，返回[self] `
     * @param bool $id 数据编号|直接初始化
     * @param string $scenario 场景|ps.缓存类场景
     * @param bool $sync 是否以数据库为主同步数据
     * @return <?=$renderModel['filename']?>
     */
    public static function loadModelDB($id = true, $sync = true, $scenario = 'default')
    {

        // ********** 1、查询缓存数据 **********
        // 查询缓存是否存在条目
        $model = self::loadModel($id, $scenario);
        // 空的声明下新条目, 数据库走一下
        $dbModel = false;
        if (!$model) {
            $model = self::loadModel(true, $scenario);
            $dbModel = <?=$doDbAlias?>::loadModel($id);
        }

        // 数据库查询的空 || id true 返回
        if (!$model && !$dbModel) return null;

        // 需要把数据库[attribute]赋值到缓存
        if ($sync && $dbModel) {
            $attribute = $dbModel->getAttributes();
            $model->setAttributes($attribute);
        }

        // 如果数据是新的 先保存下
        if ($id && $model->isNewRecord) {
            $model->saveData();
        }

        // 赋值数据库实例
        $model->setDbInstance($dbModel);

        return $model;
    }

    /**
     *  赋值数据库实例
     * @param <?=$doDbAlias?>|bool $dbModel
     * @return $this
     */
    public function setDbInstance($dbModel = false)
    {

        if (!$dbModel) {
            $this->dbInstance = <?=$doDbAlias?>::loadModel();
        } else {
            $this->dbInstance = $dbModel;
        }
        return $this;
    }

    /**
     * 获取全部列表
     * @param $page
     * @param $limit
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getList($page, $limit)
    {

        // 条件
        $where = $this->where;

        // 当前页面计算
        $page = (($page - 1) < 0 ? 0 : ($page - 1));

        // 基础 where加载完毕
        $base = $this::find()
            ->where($where);

        // 数据的获取 分页等
        $list = $base->offset($page * $limit)
            ->limit($limit)
<?php if ($model->hasAttribute('sort') && $model->hasAttribute('update_time')): ?>
            ->orderBy('sort desc, update_time desc')
<?php elseif ($model->hasAttribute('sort') && $model->hasAttribute('id')): ?>
            ->orderBy('sort desc, id desc')
<?php elseif ($model->hasAttribute('sort') && $model->hasAttribute('id')): ?>
            ->orderBy('id desc')
<?php endif;?>
            ->asArray()->all();

        // 数据库实例
        $dbInstance = $this->dbInstance;

        // 格式化数据
        foreach ($list as $k => &$v) {
<?php if ($model->hasAttribute('update_time')):?>

            // 更新时间
            if (!empty($v['update_time'])) {
                $v['update_time_text'] = date('Y-m-d H:i:s', $v['update_time']);
                $v['update_time_text_s'] = date('Y-m-d', $v['update_time']);
            }
<?php endif;?>
<?php if ($model->hasAttribute('status')):?>

            // 状态文本
            if (isset($v['status'])) {
                $v['status_text'] = $dbInstance::getStatusText($v['status']);
            }
<?php endif;?>
<?php if ($model->hasAttribute('content')):?>

            // 内容转化下
            if (!empty($v['content'])) {
                $v['content'] = htmlspecialchars_decode($v['content']);
                $v['content'] = CommonModel::addHtmlImgHost($v['content']);
            }
<?php endif;?>
        }

        return $list;
    }

    /**
     * 加载条件
     * @param $where
     * @return $this
     */
    public function loadWhere($where)
    {

        // 条件不存在
        if (empty($where)) {

            return $this;
        }

        // 如果[where][0]是'and' 直接赋值
        $canRetList = ['and', 'or', 'AND', 'OR'];
        if (!empty($where[0]) && in_array($where[0], $canRetList)) {

            $this->where = $where;
            return $this;
        }

        // 不是数组 字符直接 判断
        if (!is_array($where)) {

            // 条件是 有效
            if (!empty($where) && $this->hasAttribute($where))

                $this->where = $where;

            // 条件 无有效
            return $this;
        }

        // 循环  条件是否有效
        $stagingWhere = ['and'];
        foreach ($where as $k => $v) {

            // 首先值是有的，不能是空
            if (strlen($v) > 0 && is_array($v) && count($v) > 0 && $this->hasAttribute($k)) {

                $stagingWhere[] = ['IN', $k, array_values($v)];
                continue;
            }

            // 首先值是有的，不能是空
            if (strlen($v) > 0 && $this->hasAttribute($k))

                $stagingWhere[] = ['=', $k, $v];
        }

        // 条件最终赋值
        $this->where = $stagingWhere;

        return $this;
    }

    /**
     * 获取记录总数量
     * @return int|string
     */
    public function getCount()
    {

        // 条件
        $where = $this->where;

        // 基础 where加载完毕
        $count = $this::find()->where($where)->count();

        return $count;
    }

    /**
     * 添加|保存
     * @return bool
     */
    public function saveData()
    {

        $nowTime = time();
        // 添加的话要赋值一些初始数据
        if (empty($this->id)) {

            // 可以是走[mongoId]
            $this->id = CommonModel::newMongoId();
<?php if ($model->hasAttribute('add_time')):?>

            // 添加时间
            $this->add_time = $nowTime;
<?php endif;?>
        }
<?php if ($model->hasAttribute('update_time')):?>

        // 添加时间
        $this->update_time = $nowTime;
<?php endif;?>
<?php if ($model->hasAttribute('content')):?>

        // 内容不为空
        if (!empty($this->content)) {
            // 内容加密下
            $this->content = htmlspecialchars($this->content);
            // 内容取出图片域名
            $this->content = CommonModel::removeHtmlImgHost($this->content);
        }
<?php endif;?>

        if ($this->hasErrors() || !$this->validate() || !$this->save()) {

            return false;
        }

        return true;
    }

    /**
     * 更新某些字段
     * @param $id
     * @param array $fieldVal
     * @return bool
     */
    public static function updateField($id, $fieldVal = [])
    {

        try {

            // 不是数组塑造下
            if (!is_array($id)) $id = explode(',', $id);

            // 循环每条记录的 编号
            foreach ($id as $k => $v) {

                $model = self::loadModel($v);

                // 找不到记录直接返回
                if (!$model) {
                    self::$error_ = ['id' => ['记录不存在']];
                    return false;
                }

                // 循环每一个赋值
                $model->setAttributes($fieldVal);

                if (!$model->save()) {
                    $error = CommonModel::getModelError($model->errors);
                    self::$error_ = $error;
                    return false;
                }
            }

            // 否则成功
            return true;
        } catch (\Exception $error) {

            // 记录下错误日志
            \Yii::error([

                "``````````````````````````````````````````````````````````",
                "``                       缓存错误                         ``",
                "`` 错误详情: [<?=$generator->expName?>]缓存中修改[指定字段]失败``",
                "`` {$error->getMessage()}                                 ``",
                "`` 错误信息和参数详情:                                     ``",
                "`````````````````````````````````````````````````````````",
                $error->getTraceAsString()
            ], 'normal');

            self::$error_ = empty($error->errorInfo) ?
                $error->getMessage() :
                implode(' | ', $error->errorInfo);

            return false;
        }
    }


    /**
     * 获取静态错误
     * @return mixed
     */
    public static function getStaticErrors()
    {
        return self::$error_;
    }
}
