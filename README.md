<p align="center">
    <a href="https://github.com/wpjCode" target="_blank">
        <img src="https://avatars2.githubusercontent.com/u/27763459" height="100px" style="text-align: center;">
    </a>
    <h1 align="center">Wpj Code Wii Extension for Yii 2</h1>
    <br>
</p>

This extension is copy from [WII] and provides a Web-based code generator, called Wii, for [Yii framework 2.0](http://www.yiiframework.com) applications.
You can use Wii to quickly generate vue crud for models, vue element ui forms, modules, CRUD, js, css etc.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require wpj-code/wii
```

or add

```
"wpj-code/wii": "~1.1.1"
```

to the require-dev section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply modify your application configuration as follows:

```php
return [
    'bootstrap' => ['wii'],
    'modules' => [
        'wii' => [
            'class' => 'wpjCode\wii\Module',
        ],
        // ...
    ],
    // ...
];
```

You can then access Wii through the following URL:

```
http://localhost/path/to/index.php?r=wii
```

or if you have enabled pretty URLs, you may use the following URL:

```
http://localhost/path/to/index.php/wii
```
