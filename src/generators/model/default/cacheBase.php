<?php

use \yii\helpers\StringHelper;

/* @var $generator wpjCode\wii\generators\model\Generator */

$times = time();
$createDate = date('Y/m/d', $times);
$createTime = date('H:i:s', $times);

echo "<?php\n";
?>

/**
 * [Redis]缓存数据表基础模型
 * User: jees
 * Date: <?=$createDate?>
 * Time: <?=$createTime?>
*/

namespace <?= StringHelper::dirname(ltrim($generator->cacheBaseClass, '\\')) ?>;

use \yii\redis\ActiveRecord as CacheActiveRecord;
use \yii\db\ActiveRecord as DBActiveRecord;

class CacheBase extends CacheActiveRecord
{

    /**
     * 数据库类实例
     * @var DBActiveRecord
    */
    protected $dbInstance;

    /**
     * 条件暂存
     * @var
    */
    protected $where;
}
