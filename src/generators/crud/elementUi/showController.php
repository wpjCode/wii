<?php
/**
 * This is the template for generating a CRUD controller class file.
 */

use yii\db\ActiveRecordInterface;
use yii\helpers\StringHelper;


/* @var $this yii\web\View */
/* @var $generator wpjCode\wii\generators\crud\Generator */

$expName = StringHelper::basename($generator->expName);
$controllerClass = StringHelper::basename($generator->controllerShowClass);
$baseModelClass = StringHelper::basename($generator->baseModelClass);

/* @var $class ActiveRecordInterface */
$class = $generator->baseModelClass;
$pks = $class::primaryKey();
$urlParams = $generator->generateUrlParams();
$actionParams = $generator->generateActionParams();
$actionParamComments = $generator->generateActionParamComments();

$times = time();
$createDate = date('Y/m/d', $times);
$createTime = date('H:i:s', $times);

echo "<?php\n";
?>

namespace <?= StringHelper::dirname(ltrim($generator->controllerShowClass, '\\')) ?>;

use <?= ltrim($generator->baseModelClass, '\\') ?>;
use <?= ltrim($generator->baseControllerClass, '\\') ?>;
use yii\filters\AccessControl;

/**
 * [<?= $expName ?>]页面展示控制器
 * User: Administrator
 * Date: <?=$createDate . "\n";?>
 * Time: <?=$createTime . "\n";?>
*/
class <?= $controllerClass ?> extends <?= StringHelper::basename($generator->baseControllerClass) . "\n" ?>
{

    /**
     * 页面布局文件
     */
    public $layout = '<?=$generator->controllerShowLayout?>';

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [ // 是否游客可以访问
                'class' => AccessControl::className(),
                'user' => 'admin',
                'rules' => [
                    [ // 必须登陆才能访问
                        'actions' => [
                            'index',
                            'detail',
                            'create',
                            'update'
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
     * 列表页面
     * @return mixed
     */
    public function actionIndex()
    {

        return $this->render('<?=$generator->getRenderViewPath('index')?>');
    }

    /**
     * 详情页面
     * @param string $id 用户编号
     * @return mixed
     */
    public function actionDetail()
    {

        // 获取全部POST数据
        $id = $this->get('id');

        // 验证 规格编号
        if (empty($id)) {

            return $this->showError(404, '请传输编号，请确认信息编号是否正确。');
        }

        $model = <?= $baseModelClass ?>::loadModel($id);

        if ($model == null) {

            return $this->showError(404, '数据不存在，请确认信息编号是否正确。');
        }

        return $this->render('<?=$generator->getRenderViewPath('view')?>');
    }

    /**
     * 创建页面
     * @return mixed
     */
    public function actionCreate()
    {

        return $this->render('<?=$generator->getRenderViewPath('create')?>');
    }

    /**
     * 更新页面
     * @return mixed
     */
    public function actionUpdate()
    {

        // 编号
        $id = $this->get('id');

        // 验证 规格编号
        if (empty($id)) {

            return $this->showError(404, '请传输编号，请确认信息编号是否正确。');
        }

        // 实例化类 - 并根据编号查询
        $model = <?= $baseModelClass ?>::loadModel($id);
        // 编号非法返回
        if (empty($model)) {

            return $this->showError(404, '数据条目不存在，请确认信息编号是否正确。');
        }

        return $this->render('<?=$generator->getRenderViewPath('update')?>');
    }
}
