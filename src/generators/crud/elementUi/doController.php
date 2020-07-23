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
$modelClass = StringHelper::basename($generator->modelClass);

/* @var $class ActiveRecordInterface */
$class = new $generator->baseModelClass();
$pks = $class::primaryKey();
$urlParams = $generator->generateUrlParams();
$actionParams = $generator->generateActionParams();
$actionParamComments = $generator->generateActionParamComments();

echo "<?php\n";
?>

namespace <?= StringHelper::dirname(ltrim($generator->controllerDoClass, '\\')) ?>;

use <?= ltrim($generator->modelClass, '\\') ?>;
use <?= ltrim($generator->baseControllerClass, '\\') ?>;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use app\models\CommonModel;

/**
 * [<?= $expName ?>]操作控制器
 */
class <?= $controllerClass ?> extends <?= StringHelper::basename($generator->baseControllerClass) . "\n" ?>
{

    /**
     * 控制器类型 - 展示类型控制器
     */
    public $doType = 'show';

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [ // 请求方式
                'class' => VerbFilter::className(),
                'actions' => [
                    'list' => ['GET'],
                    'detail' => ['GET'],
                    'create' => ['POST'],
                    'update' => ['POST'],
                    'disabled' => ['POST'],
                    'open' => ['POST'],
                    'sort' => ['POST']
                ],
            ],
            'access' => [ // 是否游客可以访问
                'class' => AccessControl::className(),
                'rules' => [
                    [ // 必须登陆才能访问
                        'actions' => [
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
                    [ // 无需登陆即可访问
                        'actions' => [],
                        'allow' => true,
                        'roles' => ['?']
                    ]
                ]
            ]
        ];
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
        $model = <?= $modelClass ?>::loadModel();

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

        $model = <?= $modelClass ?>::loadModel($id);

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
        $detail['content'] = htmlspecialchars_decode($detail['content']);;
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
        $model = <?= $modelClass ?>::loadModel();
        // 加载类数据
        $model->load($data, '');

        // 数据保存失败并返回错误信息
        if (!$model->saveData()) {

            $err = $model->getFirstErrors();
            return $this->jsonFail('修改失败, 请确认各项数据是否合法', 400, [
                'columnError' => $err
            ]);
        }

        // 成功返回
        return $this->jsonSuccess('成功');
    }

    /**
     * 更新数据
     * @return mixed
     */
    public function actionUpdate()
    {

        // 编号
        $id = $this->post('id');

        // 实例化类 - 并根据编号查询
        $model = <?= $modelClass ?>::loadModel($id);
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

            $err = $model->getFirstErrors();
            return $this->jsonFail('添加失败, 请确认各项数据是否合法', 400, [
                'columnError' => $err
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

        $idList = $this->post('idList');

        // 看获取的编号列表是否合法
        if (empty($idList) || count($idList) <= 0) {

            return $this->jsonFail('请选择一个条目', 400, [
                'error_hint' => '未传输任何编号',
                'error_code' => 403
            ]);
        }

        // 是否操作成功 - 错误一般记录到[log]日志
        if (!<?= $modelClass ?>::updateStatus($idList, <?= $modelClass ?>::getStatDisabled())) {

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

        $idList = $this->post('idList');

        // 看获取的编号列表是否合法
        if (empty($idList) || count($idList) <= 0) {

            return $this->jsonFail('请选择一个条目', 400, [
                'error_hint' => '未传输任何编号',
                'error_code' => 403
            ]);
        }

        // 是否操作成功 - 错误一般记录到[log]日志
        if (!<?= $modelClass ?>::updateStatus($idList, <?= $modelClass ?>::getStatOpen())) {

            // 将返回数据库级错误，暂不返回 会在日志中记录
            return $this->jsonFail('请求频繁请稍后尝试', 500, [
                'err_detail' => '出现致命错误。请查看日志',
                'err_code' => 500
            ]);
        }

        return $this->jsonSuccess('成功');
    }

    /**
    * 排序
    * @param array $idList 数据条目|多条数组格式，如：[1,2,3]
    * @return mixed
    */
    public function actionSort()
    {

        $idList = $this->post('id');

        // 看获取的[data]是否为空
        if (empty($idList) || count($idList) <= 0) {

            return $this->jsonFail('编号不能为空', 400);
        }

        $sort = intval(trim($this->post('sort')));;

        // 看获取的[data]是否为空
        if (empty($sort)) {

            return $this->jsonFail('排序不能为空', 400);
        }

        // 排序不得超过 7 位数字 小于-6位数
        if ($sort > <?= $modelClass ?>::getSortMax() || $sort < <?= $modelClass ?>::getSortMin()) {

            return $this->jsonFail('排序不得超过999999，不得小于-999999', 400);
        }

        // 排序操作
        if (!<?= $modelClass ?>::updateSort($idList, $sort)) {

            // 将返回数据库级错误，暂不返回 会在日志中记录
            return $this->jsonFail('请求频繁请稍后尝试', 500, [
                'err_detail' => '出现致命错误。请查看日志',
                'err_code' => 500
            ]);
        }

        return $this->jsonSuccess('成功');
    }
}
