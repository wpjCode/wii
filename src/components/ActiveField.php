<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace wpjCode\wii\components;

use \wpjCode\wii\Generator;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveField extends \yii\widgets\ActiveField
{
    /**
     * {@inheritdoc}
     */
    public $template = "{label}\n{input}\n{list}\n{error}";
    /**
     * @var Generator
     */
    public $model;
    /**
     * 是否可以选择文件
     * @var bool
     */
    public $hasFileSvg = false;
    /**
     * 是否可以选择文件 说明
     * @var string
     */
    public $fileSvgTitle = 'chose file/folder';
    /**
     * 是否可以同步文件名
     * @var bool
     */
    public $syncFileSvg = false;
    /**
     * 是否可以同步文件名
     * @var string
     */
    public $syncFileTitle = 'copy name';
    /**
     * 是否可以选择文件 使用别名返回模式
     * @var string
     */
    public $useAlias = false;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $stickyAttributes = $this->model->stickyAttributes();
        if (in_array($this->attribute, $stickyAttributes, true)) {
            $this->sticky();
        }
        $hints = $this->model->hints();
        if (isset($hints[$this->attribute])) {
            $this->hint($hints[$this->attribute]);
        }
        $autoCompleteData = $this->model->autoCompleteData();
        if (isset($autoCompleteData[$this->attribute])) {
            if (is_callable($autoCompleteData[$this->attribute])) {
                $this->autoComplete(call_user_func($autoCompleteData[$this->attribute]));
            } else {
                $this->autoComplete($autoCompleteData[$this->attribute]);
            }
        } else {
            $this->parts['{list}'] = '';
        }

        // 如果有文件夹图标
        if ($this->hasFileSvg || $this->syncFileSvg) {
            $this->template = "{label}\n{input}\n{list}\n{error}\n{opt}";
        }
    }


    /**
     * Renders the whole field.
     * This method will generate the label, error tag, input tag and hint tag (if any), and
     * assemble them into HTML according to [[template]].
     * @param string|callable $content the content within the field container.
     * If `null` (not set), the default methods will be called to generate the label, error tag and input tag,
     * and use them as the content.
     * If a callable, it will be called to generate the content. The signature of the callable should be:
     *
     * ```php
     * function ($field) {
     *     return $html;
     * }
     * ```
     *
     * @return string the rendering result.
     */
    public function render($content = null)
    {
        if ($content === null) {
            if (!isset($this->parts['{input}'])) {
                $this->textInput();
            }
            if (!isset($this->parts['{label}'])) {
                $this->label();
            }
            if (!isset($this->parts['{error}'])) {
                $this->error();
            }
            if (!isset($this->parts['{hint}'])) {
                $this->hint(null);
            }
            if (!isset($this->parts['{opt}'])) {
                $this->opt();
            }
            $content = strtr($this->template, $this->parts);
        } elseif (!is_string($content)) {
            $content = call_user_func($content, $this);
        }

        return $this->begin() . "\n" . $content . "\n" . $this->end();
    }


    /**
     * 渲染span
     * @param null $span
     * @param array $options
     * @return $this|\yii\widgets\ActiveField
     */
    public function opt($span = null, $options = [])
    {

        if ($span === false) {
            $this->parts['{span}'] = '';
            return $this;
        }

        $html = [];
        // 有文件选择器
        if ($this->hasFileSvg) {
            $html[] = Html::tag('div', "", [
                'class' => 'folder can can-chose-folder',
                'title' => $this->fileSvgTitle,
                'data-use-alias' => $this->useAlias ? 1 : 0
            ]);
        }

        // 有同步文件名
        if ($this->syncFileSvg) {
            $html[] = Html::tag('div', '', [
                'class' => 'cpy-some can can-copy-name',
                'title' => $this->syncFileTitle,
                'data-use-alias' => $this->useAlias ? 1 : 0
            ]);
        }

        $html = implode("\n", $html);
        $this->parts['{opt}'] = Html::tag('div', $html, [
            'class' => 'column-opt-wrapper'
        ]);
        return $this;
    }

    /**
     * Makes field remember its value between page reloads
     * @return $this the field object itself
     */
    public function sticky()
    {
        Html::addCssClass($this->options, 'sticky');

        return $this;
    }

    /**
     * Makes field auto completable
     * @param array $data auto complete data (array of callables or scalars)
     * @return $this the field object itself
     */
    public function autoComplete($data)
    {
        $inputID = $this->getInputId();
        ArrayHelper::setValue($this->inputOptions, 'list', "$inputID-list");

        $html = Html::beginTag('datalist', ['id' => "$inputID-list"]) . "\n";
        foreach ($data as $item) {
            $html .= Html::tag('option', $item) . "\n";
        }
        $html .= Html::endTag('datalist');

        $this->parts['{list}'] = $html;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hint($content, $options = [])
    {
        Html::addCssClass($this->labelOptions, 'help');
        ArrayHelper::setValue($this->labelOptions, 'data.toggle', 'popover');
        ArrayHelper::setValue($this->labelOptions, 'data.content', $content);
        ArrayHelper::setValue($this->labelOptions, 'data.placement', 'right');

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function checkbox($options = [], $enclosedByLabel = false)
    {
        $this->template = "{input}\n{label}\n{error}";
        Html::addCssClass($this->options, 'form-check');
        Html::addCssClass($options, 'form-check-input');
        Html::addCssClass($this->labelOptions, 'form-check-label');
        return parent::checkbox($options, $enclosedByLabel);
    }

    /**
     * {@inheritdoc}
     */
    public function radio($options = [], $enclosedByLabel = false)
    {
        $this->template = "{input}\n{label}\n{error}";
        Html::addCssClass($this->options, 'form-check');
        Html::addCssClass($options, 'form-check-input');
        Html::addCssClass($this->labelOptions, 'form-check-label');
        return parent::radio($options, $enclosedByLabel);
    }
}
