<?php
/**
 * This is the template for generating the model class of a specified table.
 */

/* @var $this yii\web\View */
/* @var $generator wpjCode\wii\generators\model\Generator */
/* @var $tableName string full table name */
/* @var $className string class name */
/* @var $queryClassName string query class name */
/* @var $tableSchema yii\db\TableSchema */
/* @var $properties array list of properties (property => [type, name. comment]) */
/* @var $labels string[] list of attribute labels (name => label) */
/* @var $rules string[] list of validation rules */
/* @var $relations array list of relations (name => relation declaration) */

$redisBase = ltrim($generator->cacheBaseClass, '\\');
$redisBase = Yii::getAlias('@' . str_replace('\\', '/', $redisBase), false);
$redisBaseFile = $redisBase . '.php';
$redisBaseInfo = pathinfo($redisBaseFile);


$doModel = $generator->ns . '\\' . $generator->modelClass . '.php';
$doModel = ltrim($doModel, '\\');
$doModel = Yii::getAlias('@' . str_replace('\\', '/', $doModel), false);
$doModel = $doModel . '.php';
$doModelInfo = pathinfo($doModel);

echo "<?php\n";
?>

namespace <?= $generator->ns ?>;

use Yii;
<?php if ($redisBaseInfo['dirname'] != $doModelInfo['dirname']):
echo "use " . $generator->cacheBaseClass . ';' . "\n";?>
<?php endif; ?>

/**
 * This is the cache model class for table "<?= $generator->generateTableName($tableName) ?>".
 *
<?php foreach ($properties as $property => $data): ?>
 * @property <?= "{$data['type']} \${$property}"  . ($data['comment'] ? ' ' . strtr($data['comment'], ["\n" => ' ']) : '') . "\n" ?>
<?php endforeach; ?>
<?php if (!empty($relations)): ?>
 *
<?php foreach ($relations as $name => $relation): ?>
 * @property <?= $relation[1] . ($relation[2] ? '[]' : '') . ' $' . lcfirst($name) . "\n" ?>
<?php endforeach; ?>
<?php endif; ?>
 */
class <?= $className ?> extends <?= 'CacheBase' . "\n" ?>
{

    /**
     * @return object|\yii\redis\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return Yii::$app->get('redis');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [<?= empty($rules) ? '' : ("\n            " . implode(",\n            ", $rules) . ",\n        ") ?>];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
<?php foreach ($labels as $name => $label): ?>
            <?= "'$name' => " . $generator->generateString($label) . ",\n" ?>
<?php endforeach; ?>
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributes()
    {
        return [
<?php foreach ($labels as $name => $label): ?>
            <?= "'$name'" . ",\n" ?>
<?php endforeach; ?>
        ];
    }
<?php foreach ($relations as $name => $relation): ?>

    /**
     * @return \yii\redis\ActiveQuery
     */
    public function get<?= $name ?>()
    {
        <?= $relation[0] . "\n" ?>
    }
<?php endforeach; ?>
<?php if ($queryClassName): ?>
<?php
    $queryClassFullName = ($generator->ns === $generator->queryNs) ? $queryClassName : '\\' . $generator->queryNs . '\\' . $queryClassName;
    echo "\n";
?>
    /**
     * {@inheritdoc}
     * @return <?= $queryClassFullName ?> the active query used by this AR class.
     */
    public static function find()
    {
        return new <?= $queryClassFullName ?>(get_called_class());
    }
<?php endif; ?>
}
