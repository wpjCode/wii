<?php
/**
 * This is the template for generating a CRUD controller class file.
 */

use yii\db\ActiveRecordInterface;
use yii\helpers\StringHelper;
use \yii\helpers\Inflector;


/* @var $this yii\web\View */
/* @var $generator wpjCode\wii\generators\crud\Generator */

$expName = StringHelper::basename($generator->expName);
$controllerClass = StringHelper::basename($generator->controllerShowClass);
$controllerDoClass = StringHelper::basename($generator->controllerDoClass);
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

$doController = StringHelper::dirname(ltrim($generator->controllerDoClass, '\\'));
// 取出[API]的模块所属
$modules = array_keys(Yii::$app->modules);
$apiModule = '';
foreach ($modules as $k => $v) {
    // 如果是 app\module\ 格式可以算是通过
    if (preg_match('/app\\\\[a-z|A-Z|0-9]*\\\\' . $v . '.*/', $doController)) {
        $apiModule = $v;break;
    }
}

echo "<?php\n";
?>

namespace <?= StringHelper::dirname(ltrim($generator->controllerShowClass, '\\')) ?>;

use <?= ltrim($generator->baseModelClass, '\\') ?>;
use yii\filters\AccessControl;

/**
 * [<?= $expName ?>]页面
 * User: Administrator
 * Date: <?=$createDate . "\n";?>
 * Time: <?=$createTime . "\n";?>
*/
class <?= $controllerClass ?> extends BaseController
{

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
                    [ // 必须登录才能访问
                        'actions' => [
                            'index',
                            'detail',
                            'create',
                            'update'
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [ // 无需登录即可访问 - 空的请保证里面有一个空字符串
                        'actions' => [''],
                        'allow' => true,
                        'roles' => ['?']
                    ]
                ],
                'denyCallback' => function ($rule, $action) {

                    // PS: 此处无法使用return, 会触发其他报错[Headers already sent]

                    // 未登录检测
                    if (\Yii::$app->admin->isGuest) {
                        exit($this->showError('抱歉，请先登录', 401, [
                            'title' => '抱歉，您还未登录',
                            'reasons' => [
                                '该页面暂时只对于已用户开放'
                            ]
                        ]));
                    }

                    // 其余为 [403]
                    exit($this->showError('抱歉，该页面您暂时无法访问', 403, [
                        'title' => '抱歉，该页面您暂时无法访问',
                        'reasons' => [
                            '该页面暂时只对于未登录用户开放'
                        ]
                    ]));
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

        return $this->render('<?=$generator->getRenderViewPath('index')?>', [
            'apiModule'     => $this->apiModule,     // API控制器名
            'apiController' => $this->apiController, // API模块名
        ]);
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

            return $this->showError('请传输编号，请确认信息编号是否正确。', 404);
        }

        $model = <?= $baseModelClass ?>::loadModel($id);

        if ($model == null) {

            return $this->showError('数据不存在，请确认信息编号是否正确。', 404);
        }

        return $this->render('<?=$generator->getRenderViewPath('view')?>', [
            'apiModule'     => $this->apiModule,     // API控制器名
            'apiController' => $this->apiController, // API模块名
        ]);
    }

    /**
     * 创建页面
     * @return mixed
     */
    public function actionCreate()
    {

        return $this->render('<?=$generator->getRenderViewPath('create')?>', [
            'apiModule'     => $this->apiModule,     // API控制器名
            'apiController' => $this->apiController, // API模块名
        ]);
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

            return $this->showError('请传输编号，请确认信息编号是否正确。', 404);
        }

        // 实例化类 - 并根据编号查询
        $model = <?= $baseModelClass ?>::loadModel($id);
        // 编号非法返回
        if (empty($model)) {

            return $this->showError('数据条目不存在，请确认信息编号是否正确。', 404);
        }

        return $this->render('<?=$generator->getRenderViewPath('update')?>', [
            'apiModule'     => $this->apiModule,     // API控制器名
            'apiController' => $this->apiController, // API模块名
        ]);
    }
}
