<p align="center">
    <a href="https://github.com/yiit" target="_blank">
        <img src="https://avatars2.githubusercontent.com/u/27763459" height="100px">
    </a>
    <h1 align="center">Wpj Code Wii Extension for Yii 2</h1>
    <br>
</p>

This extension is copy from [GII] and provides a Web-based code generator, called Wii, for [Yii framework 2.0](http://www.yiiframework.com) applications.
You can use Wii to quickly generate vue crud for models, vue element ui forms, modules, CRUD, js, css etc.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --dev --prefer-dist yiisoft/yii2-gii
```

or add

```
"yiisoft/yii2-gii": "~2.0.0"
```

to the require-dev section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply modify your application configuration as follows:

```php
return [
    'bootstrap' => ['gii'],
    'modules' => [
        'gii' => [
            'class' => 'yii\gii\Module',
        ],
        // ...
    ],
    // ...
];
```

You can then access Gii through the following URL:

```
http://localhost/path/to/index.php?r=gii
```

or if you have enabled pretty URLs, you may use the following URL:

```
http://localhost/path/to/index.php/gii
```

Using the same configuration for your console application, you will also be able to access Gii via
command line as follows,

```
# change path to your application's base path
cd path/to/AppBasePath

# show help information about Gii
yii help gii

# show help information about the model generator in Gii
yii help gii/model

# generate City model from city table
yii gii/model --tableName=city --modelClass=City
```
