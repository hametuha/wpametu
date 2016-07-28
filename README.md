# wpametu

Contributors: Takahashi_Fumiki  
Tags: theme  
Requires at least: 4.4
Tested up to: 4.5.3
Stable tag: 0.8
License: MIT

破滅派で利用しているWordPress開発用フレームワークです。「うぱめつ」と読みます。

![Build Status](https://travis-ci.org/hametuha/wpametu.svg)

## Desceription

これはプラグインではなく、テーマ開発用フレームワークです。コンポーザーを利用してインストールします。あなたのテーマはPSR-0に準拠した名前空間ベースの構造になっている必要があります。

## Install

テーマフォルダにcomposer.jsonを配置し、依存関係を書き込みます。

```
{
  "require": {
    "hametuha/wpametu": "dev-master",
  }
}
```

インストールしてください。

```
composer install
```

`vendor`ディレクトリに色々とインストールされるので、テーマの`functions.php`からオートローダーを読み込みます。

```php
if ( $autoloader = file_exists( __DIR__.'/vendor/autoload.php' ) ) {
    require $autoloader;
}
```

これで利用準備ができました。

## Requirements

- PHP 5.5以上
- WordPress 4.4以上

## LICENSE

[MITライセンス](https://raw.githubusercontent.com/hametuha/wpametu/master/LICENSE)です。