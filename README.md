# wpametu

Contributors: hametuha  
Tags: theme  
Requires at least: 5.0  
Tested up to: 5.7  
Stable tag: 1.0.0  
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

これで利用準備ができました。便利な各クラスを利用することができます。

### Auto Loader

テーマを名前空間に準拠させることで、便利なAuto Loaderを利用できます。これは単にファイルを配置しておくだけで、あらゆる機能が利用可能になることを意味します。

## Requirements

- PHP 5.6以上
- WordPress 5.0以上

## LICENSE

[MITライセンス](https://raw.githubusercontent.com/hametuha/wpametu/master/LICENSE)です。
