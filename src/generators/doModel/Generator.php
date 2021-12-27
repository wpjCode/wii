<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace wpjCode\wii\generators\doModel;

use wpjCode\wii\CodeFile;
use Yii;
use yii\db\ActiveRecord;

/**
 * This generator will generate one or multiple ActiveRecord classes for the specified database table.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Generator extends \wpjCode\wii\Generator
{

    /**
     * 基础缓存类
     * @var
     */
    public $baseModelClass;
    /**
     * 数据库操作类
     * @var
     */
    public $doDbModel;
    /**
     * 命名空间
     * @var
     */
    public $nameSpace;
    /**
     * 注释操作名称
     * @var
     */
    public $expName;
    /**
     * 是否为缓存模型
     * @var
     */
    public $isCacheModel;


    /**
     * 操作名称
     * @param bool $returnEn 返回英文
     * @return string
     */
    public function getName($returnEn = false)
    {
        $title = 'Do Model Generator';
        if ($returnEn) return $title;
        return $this->langString($title);
    }

    /**
     * 操作描述
     * @param bool $returnEn 返回英文
     * @return string
     */
    public function getDescription($returnEn = false)
    {
        $title = 'Do Model Generator Desc';
        if ($returnEn) return $title;
        return $this->langString($title);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['baseModelClass', 'doDbModel', 'nameSpace', 'expName'], 'filter', 'filter' => 'trim'],
            [['baseModelClass', 'nameSpace', 'expName'], 'required'],
            [['baseModelClass', 'nameSpace', 'doDbModel'], 'filter', 'filter' => function ($value) {
                return trim($value, '\\');
            }],
            [['baseModelClass', 'doDbModel', 'nameSpace'], 'match', 'pattern' => '/^[\w\\\\]+$/', 'message' => $this->langString('Only word and backslashes')],
            [['nameSpace'], 'validateNamespace'],
            [['baseModelClass'], 'validateClasses', 'skipOnEmpty' => false],
            ['doDbModel', 'validateDoDb', 'skipOnEmpty' => false],
            [['isCacheModel'], 'boolean'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'baseModelClass' => $this->langString('Base Model Class'),
            'doDbModel' => $this->langString('Do Db Model'),
            'nameSpace' => $this->langString('Name Space Label'),
            'expName' => $this->langString('Exp Name'),
            'isCacheModel' => $this->langString('Is Cache Model Label')
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function hints()
    {
        return array_merge(parent::hints(), [
            'baseModelClass' => $this->langString('Base Model Hint'),
            'doDbModel' => $this->langString('Do Db Model Hint'),
            'nameSpace' => $this->langString('Name Space Hint'),
            'expName' => $this->langString('Exp Name'),
            'isCacheModel' => $this->langString('Is Cache Model Hint')
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function requiredTemplates()
    {
        return [
            'cacheModel.php',
            'dbModel.php',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function stickyAttributes()
    {
        return array_merge(parent::stickyAttributes(), $this->attributes());
    }

    /**
     * Validates the namespace.
     *
     * @param string $attribute Namespace variable.
     */
    public function validateNamespace($attribute)
    {
        $value = $this->$attribute;
        $value = ltrim($value, '\\');
        $path = Yii::getAlias('@' . str_replace('\\', '/', $value), false);
        if ($path === false) {
            $this->addError($attribute, $this->langString('Namespace must be associated with an existing directory'));
        }
    }

    /**
     * Validates the `validateClass`
     * @param string $attribute
     * @return bool
     */
    public function validateClasses($attribute)
    {

        // 先验证文件是否存在
        $value = $this->$attribute;
        $value = ltrim($value, '\\');
        $path = Yii::getAlias('@' . str_replace('\\', '/', $value), false);
        $path = $path . '.php';
        if (!file_exists($path)) {
            $this->addError($attribute, $attribute . $this->langString('class not found'));
            return false;
        }

        // 验证类是否合法可使用
        if (!class_exists($this->$attribute)) {
            $this->addError($attribute, $attribute . $this->langString('class not be allow, namespace missing?'));
            return false;
        }

        return true;
    }

    /**
     * 检测数据库操作类
     * @param $attribute
     * @return bool
     */
    public function validateDoDb($attribute) {

        // 首先生成缓存模板才验证必须
        $list = [true, 1];
        if (in_array($this->isCacheModel, $list) && !$this->$attribute) {
            $this->addErrors([
                $attribute => $this->langString('Render Cache Model DoDbModel is required')
            ]);
            return false;
        }
        return true;
    }

    /**
     * 获取生成渲染的文件路径
     * @return bool|string
     */
    public function getRenderFilePath() {

        // 文件路径组成
        $value = ltrim($this->nameSpace, '\\');
        $path = Yii::getAlias(
            '@' . str_replace('\\', '/', $value),
            false
        );
        $basePathInfo = pathinfo(Yii::getAlias(
            '@' . str_replace('\\', '/', $this->baseModelClass),
            false
        ));
        // $path = $path . '/' . $basePathInfo['basename'] . 'Model.php';
        $path = $path . '.php';

        return $path;
    }

    public function generate()
    {

        // 获取生成文件路径
        $path = $this->getRenderFilePath();

        // 缓存操作类
        $list = [true, 1];
        if (in_array($this->isCacheModel, $list)) {
            $template = 'cacheModel.php';
        } else { // 数据库操作类
            $template = 'dbModel.php';
        }

        $files[] = new CodeFile($path, $this->render($template, []));

        return $files;
    }

    /**
     * Returns table schema for current model class or false if it is not an active record
     * @return bool|\yii\db\TableSchema
     * @throws \yii\base\InvalidConfigException
     */
    public function getTableSchema()
    {
        /* @var $class ActiveRecord */
        $class = $this->baseModelClass;
        if (is_subclass_of($class, 'yii\db\ActiveRecord')) {
            return $class::getTableSchema();
        }
        /* do db model */
        $class = $this->doDbModel;
        if (is_subclass_of($class, 'yii\db\ActiveRecord')) {
            return $class::getTableSchema();
        }

        return false;
    }

}
