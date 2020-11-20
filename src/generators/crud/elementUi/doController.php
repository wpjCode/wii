<?php
/**
 * This is the template for generating a CRUD controller class file.
 */

use yii\db\ActiveRecordInterface;
use yii\helpers\StringHelper;


/* @var $this yii\web\View */
/* @var $generator wpjCode\wii\generators\crud\Generator */

$expName = StringHelper::basename($generator->expName);
$controllerClass = StringHelper::basename($generator->controllerDoClass);
$baseModelClass = StringHelper::basename($generator->baseModelClass);
/* @var $class \yii\db\ActiveRecord */
$class = new $generator->baseModelClass();
$pks = $class::primaryKey();
$urlParams = $generator->generateUrlParams();
$actionParams = $generator->generateActionParams();
$actionParamComments = $generator->generateActionParamComments();

$times = time();
$createDate = date('Y/m/d', $times);
$createTime = date('H:i:s', $times);

echo "<?php\n";
?>

namespace <?= StringHelper::dirname(ltrim($generator->controllerDoClass, '\\')) ?>;

use <?= ltrim($generator->baseModelClass, '\\') ?>;
use <?= ltrim($generator->baseControllerClass, '\\') ?>;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use app\models\CommonModel;

/**
 * [<?= $expName ?>]操作控制器
 * User: Administrator
 * Date: <?=$createDate . "\n";?>
 * Time: <?=$createTime . "\n";?>
*/
class <?= $controllerClass ?> extends <?= StringHelper::basename($generator->baseControllerClass) . "\n" ?>
{

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [ // 请求方式
                'class' => VerbFilter::className(),
                'actions' => [
                    'setting' => ['GET'],
                    'list' => ['GET'],
                    'detail' => ['GET'],
                    'create' => ['POST'],
                    'update' => ['POST'],
<?php if ($class->hasAttribute('status')) {?>
                    'disabled' => ['POST'],
                    'open' => ['POST'],
<?php } if ($class->hasAttribute('sort') || $class->hasAttribute('list_order')) {?>
                    'sort' => ['POST']
<?php } ?>
                ],
            ],
            'access' => [ // 是否游客可以访问
                'class' => AccessControl::className(),
                'user' => 'admin',
                'rules' => [
                    [ // 必须登陆才能访问
                        'actions' => [
                            'setting',
                            'list',
                            'detail',
                            'create',
                            'update',
                            'disabled',
                            'open',
                            'sort'
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [ // 无需登陆即可访问 - 空的请保证里面有一个空字符串
                        'actions' => [''],
                        'allow' => false,
                        'roles' => ['?']
                    ]
                ],
                'denyCallback' => function ($rule, $action) {
                    // 未登录检测
                    if (\Yii::$app->user->isGuest) {
                        return $this->jsonFail('请先登录', 403, [
                        'errorHint' => '您还未登录'
                        ]);
                    }
                }
            ]
        ];
    }

    /**
     * 获取设置
     *  ` PS: 获取某些设置(如：状态列表等)以供前端使用
     * @return mixed
     */
    public function actionSetting()
    {

        // 类型 - 一般为首页[index]、表单[form]
        $type = $this->post('type');
        // 模型
        $model = <?= $baseModelClass ?>::loadModel();
        return $this->jsonSuccess('成功', [
<?php if ($class->hasAttribute('status') && $class->hasMethod('getStatNormal')) { ?>
            'defaultStatus' => !empty($type) && $type == 'index' ?
                $model::getStatNormal() : $model::getStatOpen(), // 默认选中状态
<?php } else { ?>
            'defaultStatus' => $model::getStatOpen(), // 默认选中状态
<?php } if ($class->hasAttribute('status')) { ?>
            'statusList' => $model::getStatList(), // 状态文本列表值
            'statusTextList' => $model::getStatusTextList(), // 状态文本列表值
<?php } if ($class->hasAttribute('type')) { ?>
            'typeList' => $model::getTypeList(), // 类型列表值
            'typeTextList' => $model::getTypeTextList(), // 类型文本列表值
<?php } if ($class->hasAttribute('sort') || $class->hasAttribute('list_order')) { ?>
            'sortMin' => $model::getSortMin(), // 最小排序值
            'sortMax' => $model::getSortMax(), // 最大排序值
<?php } ?>
        ]);
    }

    /**
     * 列表
     * @return mixed
     */
    public function actionList()
    {

        // 查询内容
        $find = $this->get('search');

        // 显示当前第几页
        $page = !$this->get('page') ? 0 : $this->get('page');

        // 每页显示多少条
        $pageSize = (!$this->get('pageSize') || $this->get('pageSize') <= 0 || $this->get('pageSize') > 100) ? \Yii::$app->params['dataLimit'] : $this->get('pageSize');

        // 字段1
        $field = [
<?php foreach ($class->attributes() as $kL => $vL) { ?>
            '<?=$vL?>',
<?php } ?>
        ];

        // 父级别[model]
        $model = <?= $baseModelClass ?>::loadModel();

        // 数据列表
        $list = $model->loadWhere($find)->getList($page, $pageSize, $field);

        // 总数
        $count = $model->loadWhere($find)->getCount();

        return $this->jsonSuccess('成功', [
            'total' => $count,
            'list' => $list
        ]);
    }

    /**
     * 详情
     * @param string $id 用户编号
     * @return mixed
     */
    public function actionDetail()
    {

        // 获取全部POST数据
        $id = $this->get('id');

        // 验证 规格编号
        if (empty($id)) {

            return $this->jsonFail('数据不存在', 404, [
                'error_hint' => '请传输编号',
                'error_code' => 400
            ]);
        }

        $model = <?= $baseModelClass ?>::loadModel($id);

        if ($model == null) {

            return $this->jsonFail('数据不存在', 404, [
                'error_hint' => '数据不存在，请核对',
                'error_code' => 404
            ]);
        }

        // 仅仅返回指定 字段
        $detail = $model->getAttributes([
<?php foreach ($class->attributes() as $kL => $vL) { ?>
            '<?=$vL?>',
<?php } ?>
        ]);

<?php if ($class->hasAttribute('content')) { ?>
        $detail['content'] = htmlspecialchars_decode($detail['content']);
        $detail['content'] = CommonModel::addHtmlImgHost($detail['content']);
<?php } ?>

        return $this->jsonSuccess('成功', $detail);
    }

    /**
     * 创建
     * @return mixed
     */
    public function actionCreate()
    {
        // 赋值所需数据
        $data = [
<?php foreach ($class->attributes() as $kL => $vL) { ?>
            '<?=$vL?>' => $this->post('<?=$vL?>'),
<?php } ?>
        ];

        // 实例化类
        $model = <?= $baseModelClass ?>::loadModel();
        // 加载类数据
        $model->load($data, '');

        // 数据保存失败并返回错误信息
        if (!$model->saveData()) {

            $error = $model->getFirstErrors();
            return $this->jsonFail('添加失败, 请确认各项数据是否合法', 400, [
                'columnError' => CommonModel::chineseErr($error)
            ]);
        }

        // 成功返回
        return $this->jsonSuccess('成功');
    }

    /**
     * 更新
     * @return mixed
     */
    public function actionUpdate()
    {

        // 编号
        $id = $this->post('id');

        // 实例化类 - 并根据编号查询
        $model = <?= $baseModelClass ?>::loadModel($id);
        // 编号非法返回
        if (empty($model)) {

            return $this->jsonFail('数据不存在', 400, [
                'error_hint' => '请传输编号',
                'error_code' => 400
            ]);
        }

        // 赋值所需数据
        $data = [
<?php foreach ($class->attributes() as $kL => $vL) { ?>
            '<?=$vL?>' => $this->post('<?=$vL?>'),
<?php } ?>
        ];

        // 加载类数据
        $model->load($data, '');

        // 数据保存失败并返回错误信息
        if (!$model->saveData()) {

            $error = $model->getFirstErrors();
            return $this->jsonFail('修改失败, 请确认各项数据是否合法', 400, [
                'columnError' => CommonModel::chineseErr($error)
            ]);
        }

        // 成功返回
        return $this->jsonSuccess('成功');
    }

    /**
     * 禁用条目
     * @param array $idList 数据条目|多条数组格式，如：[1,2,3]
     * @return mixed
     */
    public function actionDisabled()
    {

        // 编号列表获取
        $idList = $this->post('idList');
        // 不是数组塑造下 为了构成100%的数组
        if (!empty($idList) && !is_array($idList)) $idList = explode(',', $idList);
        // 看获取的编号列表是否合法
        if (empty($idList) || count($idList) <= 0) {

            return $this->jsonFail('请选择一个条目', 400, [
                'error_hint' => '未传输任何编号',
                'error_code' => 403
            ]);
        }

        // 检测多选条目
        $idMaxCount = empty(\Yii::$app->params['maxIdCount']) ? 100 : \Yii::$app->params['maxIdCount'];
        if (count($idList) > intval($idMaxCount)) {
            return $this->jsonFail('每次最多选择操作' . $idMaxCount . '条数据', 400);
        }

        // 条件
        $condition = ['AND', ['IN', 'id', $idList]];
        // 修改的字段
        $field = ['status' => <?= $baseModelClass ?>::getStatDisabled()];
        // 是否操作成功 - 错误一般记录到[log]日志
        if (!<?= $baseModelClass ?>::updateField($condition, $field)) {

            // 将返回数据库级错误，暂不返回 会在日志中记录
            return $this->jsonFail('请求频繁请稍后尝试', 500, [
                'err_detail' => '出现致命错误。请查看日志',
                'err_code' => 500
            ]);
        }

        return $this->jsonSuccess('成功');
    }

    /**
    * 开启条目
    * @param array $idList 数据条目|多条数组格式，如：[1,2,3]
    * @return mixed
    */
    public function actionOpen()
    {

        // 编号列表获取
        $idList = $this->post('idList');
        // 不是数组塑造下 为了构成100%的数组
        if (!empty($idList) && !is_array($idList)) $idList = explode(',', $idList);
        // 看获取的编号列表是否合法
        if (empty($idList) || count($idList) <= 0) {

            return $this->jsonFail('请选择一个条目', 400, [
                'error_hint' => '未传输任何编号',
                'error_code' => 403
            ]);
        }

        // 检测多选条目
        $idMaxCount = empty(\Yii::$app->params['maxIdCount']) ? 200 : \Yii::$app->params['maxIdCount'];
        if (count($idList) > intval($idMaxCount)) {
            return $this->jsonFail('每次最多选择操作' . $idMaxCount . '条数据', 400);
        }

        // 条件
        $condition = ['AND', ['IN', 'id', $idList]];
        // 修改的字段
        $field = ['status' => <?= $baseModelClass ?>::getStatOpen()];
        // 是否操作成功 - 错误一般记录到[log]日志
        if (!<?= $baseModelClass ?>::updateField($condition, $field)) {

            // 将返回数据库级错误，暂不返回 会在日志中记录
            return $this->jsonFail('请求频繁请稍后尝试', 500, [
                'err_detail' => '出现致命错误。请查看日志',
                'err_code' => 500
            ]);
        }

        return $this->jsonSuccess('成功');
    }

<?php if ($class->hasAttribute('sort') || $class->hasAttribute('list_order')) { ?>
    /**
    * 排序
    * @param array $idList 数据条目|多条数组格式，如：[1,2,3]
    * @return mixed
    */
    public function actionSort()
    {

        // 编号列表获取
        $idList = $this->post('idList');
        // 不是数组塑造下 为了构成100%的数组
        if (!empty($idList) && !is_array($idList)) $idList = explode(',', $idList);
        // 看获取的编号列表是否合法
        if (empty($idList) || count($idList) <= 0) {

            return $this->jsonFail('编号不能为空', 400);
        }

        // 检测多选条目
        $idMaxCount = empty(\Yii::$app->params['maxIdCount']) ? 200 : \Yii::$app->params['maxIdCount'];
        if (count($idList) > intval($idMaxCount)) {
            return $this->jsonFail('每次最多选择操作' . $idMaxCount . '条数据', 400);
        }

        // 排序值获取
        $sort = intval(trim($this->post('sort')));;
        // 看获取的[data]是否为空
        if (!isset($sort)) return $this->jsonFail('排序不能为空', 400);

        // 排序不得超过 7 位数字 小于-6位数
        if ($sort > <?= $baseModelClass ?>::getSortMax() || $sort < <?= $baseModelClass ?>::getSortMin()) {

            return $this->jsonFail('排序不得超过999999，不得小于-999999', 400);
        }

        // 条件
        $condition = ['AND', ['IN', 'id', $idList]];
        // 修改的字段
        $field = ['sort' => $sort];
        if (!<?= $baseModelClass ?>::updateField($condition, $field)) {

            // 将返回数据库级错误，暂不返回 会在日志中记录
            return $this->jsonFail('请求频繁请稍后尝试', 500, [
                'err_detail' => '出现致命错误。请查看日志',
                'err_code' => 500
            ]);
        }

        return $this->jsonSuccess('成功');
    }
<?php } ?>
}
