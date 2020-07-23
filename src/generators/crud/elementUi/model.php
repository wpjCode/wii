<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2020-07-21
 * Time: 16:05
 */

/* @var $generator wpjCode\wii\generators\crud\Generator */

/* @var $model \yii\db\ActiveRecord */
$model = new $generator->baseModelClass();
echo <<<EOT
<?php

namespace app\models;

use app\models\CommonModel;
use yii\helpers\ArrayHelper;

/**
 * {$generator->expName}[Model]
 * User: Administrator
 * Date: 1995/12/22
 * Time: 17:59
 */
class NoticeModel extends Notice
{

    /**
     * 状态 列表
     * @var array
     */
    private static \$statusList = [
        'delete' => -1,
        'normal' => 0,
        'open' => 1
    ];
    /**
     * 状态文本 列表
     * @var array
     */
    private static \$statusListText = [
        -1 => '已禁用',
        0 => '未审核',
        1 => '已开启'
    ];
EOT;
if ($model->hasAttribute('status')) {
    echo <<<EOT
    
    /**
     * 排序最大值
     * @var int
     */
    protected static \$sortMax = 9999999;
    /**
     * 排序最小值
     * @var int
     */
    protected static \$sortMin = -999999;
EOT;
}
echo <<<EOT


    public \$where = [];

    /**
     * 静态错误暂存
     * @var
     */
    public static \$error_;


    /**
     * 规则验证
     * @return array
     */
    public function rules()
    {

        \$parent = parent::rules();
EOT;

// ******** 有状态渲染状态列表 开始 ********
if ($model->hasAttribute('status')) {
echo <<<EOT
        // 状态
        \$statusList = array_values(self::getStatList());
EOT;
}
// ******** 有[状态]渲染状态列表 结束 ********
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
// ******** 有[排序]渲染规则 开始 ********
if ($model->hasAttribute('sort')) {
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
        return [
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
}
echo <<<EOT

        ];
    }

    /**
     * 场景
     * @return array
     */
    public function scenarios()
    {

        \$scenarios = parent::scenarios();
        return ArrayHelper::merge(\$scenarios, [
            [
                // 自定义场景 (无用请删除)
                'scUpdate' => [
                    'someAttributes'
                ]
            ]
        ]);
    }
    
    /**
     * 加载整体[Model]
     * @param null \$id 编号
     * @param string \$scenario 场景
     * @return NoticeModel|\yii\db\ActiveQuery|null
     */
    public static function loadModel(\$id = null, \$scenario = 'default')
    {

        // 实力化类
        \$model = new self();

        // [id]是空的 仅仅返回[model]
        if (\$id == null) return \$model;

        // [id]是[true]返回
        if (\$id === true)

            \$model = \$model::find();
        else if (!empty(\$id)) // 有[id]去查询

            \$model = \$model::findOne(\$id);


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
        \$base = \$this::find()
            ->select(\$filed)
            ->where(\$where);

        // 数据的获取 分页等
        \$list = \$base->offset(\$page * \$limit)
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
} else if ($model->hasAttribute('sort') && $model->hasAttribute('id')) {
    echo <<<EOT
    
            ->orderBy('id desc')
EOT;
}
echo <<<EOT

            ->asArray()->all();

        // 格式化数据
        foreach (\$list as \$k => &\$v) {
EOT;
if ($model->hasAttribute('update_time')) {
    echo <<<EOT
    
    
            // 更新时间
            if (!empty(\$v['update_time'])) {
                \$v['update_time_text'] = date('Y-m-d H:i:s', \$v['update_time']);
                \$v['update_time_text_s'] = date('Y-m-d', \$v['update_time']);
            }
EOT;
}
if ($model->hasAttribute('status')) {
    echo <<<EOT
    
    
            // 状态文本
            if (isset(\$v['status'])) {
                \$v['status_text'] = self::getStatusText(\$v['status']);
            }
EOT;
}
if ($model->hasAttribute('content')) {
    echo <<<EOT
    
    
            // 内容转化下
            if (!empty(\$v['content'])) {
                \$v['content'] = htmlspecialchars_decode(\$v['content']);
                \$v['content'] = CommonModel::addHtmlImgHost(\$v['content']);
            }
EOT;
}
echo <<<EOT

        }

        return \$list;
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

            // 首先值是有的，不能是空
            if (\$v && is_array(\$v) && count(\$v) > 0 && \$this->hasAttribute(\$k)) {

                \$stagingWhere[] = ['IN', \$k, array_values(\$v)];
                continue;
            }

            // 首先值是有的，不能是空
            if (\$v && \$this->hasAttribute(\$k))

                \$stagingWhere[] = ['=', \$k, \$v];
        }

        // 条件最终赋值
        \$this->where = \$stagingWhere;

        return \$this;
    }

    /**
     * 获取记录总数量
     * @return int|string
     */
    public function getCount()
    {

        // 条件
        \$where = \$this->where;

        // 基础 where加载完毕
        \$count = \$this::find()->where(\$where)->count();

        return \$count;
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

            // 可以是走mongoId
            // \$this->id = CommonModel::newMongoId();
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
    
        // 添加时间
        \$this->update_time = \$nowTime;
EOT;
}
if ($model->hasAttribute('update_time')) {
    echo <<<EOT
    
        // 操作者
        \$this->action_uid = \Yii::\$app->getUser()->id;
EOT;
}
if ($model->hasAttribute('update_time')) {
    echo <<<EOT
    
        // 内容加密下
        \$this->content = htmlspecialchars(\$this->content);
        // 内容取出图片域名
        \$this->content = CommonModel::removeHtmlImgHost(\$this->content);
EOT;
}
echo <<<EOT


        if (\$this->hasErrors() || !\$this->validate() ||  !\$this->save()) {

            return false;
        }

        return true;
    }

EOT;
if ($model->hasAttribute('status')) {
    echo <<<EOT
    
    /**
     * 更新[STATUS]状态操作
     * @param \$id
     * @param \$status
     * @return bool
     */
    public static function updateStatus(\$id, \$status)
    {

        \$db = \Yii::\$app->db->createCommand();

        try {

            // 不是数组塑造下
            if (!is_array(\$id))
                \$id = explode(',', \$id);

            \$db->update(self::tableName(), [
                'status' => \$status
            ], [
                'and',
                ['in', 'id', \$id]
            ])->execute();

            // 否则成功
            return true;
        } catch (\Exception \$err) {

            // 记录下错误日志
            \Yii::error([
                "````````````````````````````````````````````````````````",
                "``                      数据库错误                      ``",
                "`` 错误详情: 状态改为{\$status}失败，{\$err->getMessage()}  ``",
                "`` SQL语句: {\$db->getRawSql()}                         ``",
                "`` 错误信息和参数详情:                                    ``",
                "````````````````````````````````````````````````````````",
                \$err->getTraceAsString()
            ], 'normal');

            self::\$error_ = CommonModel::renderModelError('status', \$err->getMessage());
            return false;
        }
    }
        
EOT;
}
if ($model->hasAttribute('sort')) {
    echo <<<EOT
    
    /**
     * 更新[SORT]排序操作
     * @param \$id
     * @param \$sort
     * @return bool
     */
    public static function updateSort(\$id, \$sort)
    {

        // 排序不得超过 7 位数字
        if (\$sort > self::\$sortMax || \$sort < self::\$sortMin) {


            self::\$error_ = [
                '400' => '排序非法'
            ];
            return false;
        }


        \$db = \Yii::\$app->db->createCommand();

        try {

            // 不是数组塑造下
            if (!is_array(\$id))
                \$id = explode(',', \$id);

            \$db->update(self::tableName(), [
                'sort' => intval(\$sort)
            ], [
                'and',
                ['in', 'id', \$id]
            ])->execute();

            // 否则成功
            return true;
        } catch (\Exception \$err) {

            // 记录下错误日志
            \Yii::error([
                "````````````````````````````````````````````````````````",
                "``                      数据库错误                      ``",
                "`` 错误详情: 状态改为{\$sort}失败，{\$err->getMessage()}    ``",
                "`` SQL语句: {\$db->getRawSql}                           ``",
                "`` 错误信息和参数详情:                                   ``",
                "````````````````````````````````````````````````````````",
                \$err->getTraceAsString()
            ], 'normal');

            self::\$error_ = CommonModel::renderModelError('sort', \$err->getMessage());
            return false;
        }
    }
    
EOT;
}
if ($model->hasAttribute('status')) {
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
     * 获取[正常]状态 值
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
        return self::\$statusList['delete'];
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
     * 获取[状态]文本
     * @param \$value
     * @return mixed|string
     */
    public static function getStatusText(\$value) {

        // 列表
        \$list = self::\$statusListText;
        // 不合法 - 不存在
        if (empty(\$list[\$value]))

            return '--';

        // 最终正常返回
        return \$list[\$value];
    }
}

EOT;
