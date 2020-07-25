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
$modelClass = StringHelper::basename($generator->modelClass);

/* @var $class ActiveRecordInterface */
$class = $generator->baseModelClass;
$pks = $class::primaryKey();
$urlParams = $generator->generateUrlParams();
$actionParams = $generator->generateActionParams();
$actionParamComments = $generator->generateActionParamComments();

echo "<?php\n";
?>

namespace <?= StringHelper::dirname(ltrim($generator->controllerShowClass, '\\')) ?>;

use <?= ltrim($generator->modelClass, '\\') ?>;
use <?= ltrim($generator->baseControllerClass, '\\') ?>;
use yii\filters\AccessControl;

/**
 * [<?= $expName ?>]页面展示控制器
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
     * 首页列表页面
     * @return mixed
     */
    public function actionIndex()
    {

        return $this->render('<?=$generator->getRenderViewPath('index')?>');
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

            return $this->showError(404, '请传输编号', '请传输编号，请确认信息编号是否正确。');
        }

        $model = <?= $modelClass ?>::loadModel($id);

        if ($model == null) {

            return $this->showError(404, '数据不存在', '数据不存在，请确认信息编号是否正确。');
        }

        return $this->render('<?=$generator->getRenderViewPath('view')?>');
    }

    /**
     * 创建
     * @return mixed
     */
    public function actionCreate()
    {

        return $this->render('<?=$generator->getRenderViewPath('create')?>');
    }

    /**
     * 更新数据
     * @return mixed
     */
    public function actionUpdate()
    {

        // 编号
        $id = $this->get('id');

        // 验证 规格编号
        if (empty($id)) {

            return $this->showError(404, '请传输编号', '请传输编号，请确认信息编号是否正确。');
        }

        // 实例化类 - 并根据编号查询
        $model = <?= $modelClass ?>::loadModel($id);
        // 编号非法返回
        if (empty($model)) {

            return $this->showError(404, '数据未找到', '数据条目不存在，请确认信息编号是否正确。');
        }

        return $this->render('<?=$generator->getRenderViewPath('update')?>');
    }
}
