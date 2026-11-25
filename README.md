# NExT External CSS Loader

[![Try in WordPress Playground](https://img.shields.io/badge/Try%20in-WordPress%20Playground-3858e9?logo=wordpress)](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/AtsushiA/next-External-CSS-Loader/main/blueprint.json)

外部URLからCSSを読み込むWordPressプラグインです。

## 機能

- 外部URLからCSSファイルを読み込み、フロントエンドに適用
- 管理画面でCSSのURLを設定
- 強制ロード機能で即座にCSSを再読み込み
- 設定画面で読み込んだCSSの内容をプレビュー表示

## インストール

1. プラグインフォルダを `wp-content/plugins/` にアップロード
2. WordPress管理画面の「プラグイン」から有効化
3. 「設定」→「NExT CSS Loader」でCSSのURLを設定

## 使い方

### 基本設定

1. 管理画面の「設定」→「NExT CSS Loader」を開く
2. 「External CSS URL」に読み込みたいCSSファイルのURLを入力
3. 「Save Settings」をクリック
4. 「Force Load」ボタンをクリックしてCSSを読み込み

### CSS プレビュー

設定画面の下部に、現在読み込まれている外部CSSの内容が表示されます。

## フィルターフック

### `next_external_css_loader_sslverify`

外部CSSを取得する際のSSL検証を制御します。

```php
// SSL検証を無効化（開発環境向け）
add_filter( 'next_external_css_loader_sslverify', '__return_false' );

// URLに応じて制御
add_filter( 'next_external_css_loader_sslverify', function( $sslverify, $url ) {
    if ( strpos( $url, 'localhost' ) !== false ) {
        return false;
    }
    return $sslverify;
}, 10, 2 );
```

## 要件

- WordPress 5.0以上
- PHP 7.4以上

## ライセンス

GPL v2 or later
