<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace wpjCode\wii\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DefaultController extends Controller
{

    public $layout = 'generator';
    /**
     * @var \wpjCode\wii\Module
     */
    public $module;
    /**
     * @var \wpjCode\wii\Generator
     */
    public $generator;

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        Yii::$app->response->format = Response::FORMAT_HTML;
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        $this->layout = 'main';

        return $this->render('index');
    }

    public function actionView($id)
    {
        $generator = $this->loadGenerator($id);
        $params = ['generator' => $generator, 'id' => $id];

        $preview = Yii::$app->request->post('preview');
        $generate = Yii::$app->request->post('generate');
        $answers = Yii::$app->request->post('answers');

        if ($preview !== null || $generate !== null) {
            if ($generator->validate()) {
                $generator->saveStickyAttributes();
                $files = $generator->generate();
                if ($generate !== null && !empty($answers)) {
                    $params['hasError'] = !$generator->save($files, (array) $answers, $results);
                    $params['results'] = $results;
                } else {
                    $params['files'] = $files;
                    $params['answers'] = $answers;
                }
            }
        }

        return $this->render('view', $params);
    }

    public function actionPreview($id, $file)
    {
        $generator = $this->loadGenerator($id);
        if ($generator->validate()) {
            foreach ($generator->generate() as $f) {
                if ($f->id === $file) {
                    $content = $f->preview();
                    if ($content !== false) {
                        return  $content;
                    }
                    return '<div class="error">Preview is not available for this file type.</div>';
                }
            }
        }
        throw new NotFoundHttpException("Code file not found: $file");
    }

    public function actionDiff($id, $file)
    {
        $generator = $this->loadGenerator($id);
        if ($generator->validate()) {
            foreach ($generator->generate() as $f) {
                if ($f->id === $file) {
                    return $this->renderPartial('diff', [
                        'diff' => $f->diff(),
                    ]);
                }
            }
        }
        throw new NotFoundHttpException("Code file not found: $file");
    }

    /**
     * Runs an action defined in the generator.
     * Given an action named "xyz", the method "actionXyz()" in the generator will be called.
     * If the method does not exist, a 400 HTTP exception will be thrown.
     * @param string $id the ID of the generator
     * @param string $name the action name
     * @return mixed the result of the action.
     * @throws NotFoundHttpException if the action method does not exist.
     */
    public function actionAction($id, $name)
    {
        $generator = $this->loadGenerator($id);
        $method = 'action' . $name;
        if (method_exists($generator, $method)) {
            return $generator->$method();
        }
        throw new NotFoundHttpException("Unknown generator action: $name");
    }

    /**
     * 拆看\渲染文件列表
     * @return false|string
     */
    public function actionSeeFolder()
    {

        // 项目目录，如：/var/www/html
        $rootPath = dirname(Yii::getAlias('@webroot'));

        // 默认传输的路径
        $parentPath = Yii::$app->request->post('parentFolder');

        // 当前打开的文件夹
        $openPath = Yii::$app->request->post('openPath');
        // 命名空间替换回路径
        $openPath = str_replace('app\\', '@app/', $openPath);
        // 命名空间替换回路径
        $openPath = str_replace('\\', '/', $openPath);
        // 转化下别名为路径
        $openPath = Yii::getAlias($openPath);
        // 去除下根路径
        $openPath = str_replace($rootPath, '', $openPath);
        $openPath = explode('/', $openPath);
        $openPath = array_values(array_filter($openPath));

        // 格式化
//        if (!empty($openPath)) {
//            $openPath = str_replace('@app', $rootPath, $temp[0]);
//        }
        // 如果叠加 默认传输的路径，如：/var/www/html/vendor/aferrandini
        $basePath = $rootPath;
        if (!empty($parentPath)) {
            $basePath = $basePath . $parentPath;
        }

        $endData = $this->getFileList($basePath, $openPath);

        return json_encode([
            'code' => 200,
            'msg' => 'success',
            'data' => $endData
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 获取文件夹下文件列表
     * @param string $path 路径
     * @param array $openPath 打开的文件列表
     * @return array
     */
    protected function getFileList($path = '', $openPath = []) {

        // 项目目录，如：/var/www/html
        $rootPath = dirname(Yii::getAlias('@webroot'));

        // 列举文件列表
        $file = glob($path . '/*');

        $sortDesc = -1;
        $sort = 0;
        $endData = [];
        $firstName = array_shift($openPath); // 要打开文件夹的第一个
        foreach ($file as $k => $v) {

            $sort = $k;

            $temp = explode('.', $v);
            // 是文件
            if (is_file($v)) {
                $sort = $sortDesc;
                $sortDesc--;

                $endData[$sort]['isFolder'] = -1;
            } else { // 是文件夹
                $endData[$sort]['isFolder'] = 1;
            }

            $endData[$sort]['name'] = pathinfo($v);
            $endData[$sort]['fileName'] = $endData[$sort]['name']['filename'];
            $endData[$sort]['name'] = $endData[$sort]['name']['basename'];
            $endData[$sort]['path'] = str_replace($rootPath, '', $temp[0]);
            $endData[$sort]['nameSpace'] = 'app' . str_replace('/', '\\',
                    $endData[$sort]['path']);
            $endData[$sort]['nameAlias'] = '@app' . $endData[$sort]['path'];

            if (strstr('app\web', $endData[$sort]['nameSpace'])) {
                $endData[$sort]['nameAlias'] = '@web' . preg_replace('/\/web/',
                        $endData[$sort]['path'], '');
            }

            if ($endData[$sort]['fileName'] == $firstName && $endData[$sort]['isFolder'] == 1) {

                $fileList = [];
                $endData[$sort]['children'] = $this->getFileList($v, $openPath);
            }
        }

        krsort($endData);

        return $endData;
    }

    /**
     * Loads the generator with the specified ID.
     * @param string $id the ID of the generator to be loaded.
     * @return \wpjCode\wii\Generator the loaded generator
     * @throws NotFoundHttpException
     */
    protected function loadGenerator($id)
    {
        if (isset($this->module->generators[$id])) {
            $this->generator = $this->module->generators[$id];
            $this->generator->loadStickyAttributes();
            $this->generator->load(Yii::$app->request->post());

            return $this->generator;
        }
        throw new NotFoundHttpException("Code generator not found: $id");
    }
}
