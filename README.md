# シェアハウス管理Webアプリ

実際のシェアハウスで、ゴミ捨て当番を住人へLINE通知するためのWebアプリです。
最初のMVPでは、週替わりの担当者を自動計算し、収集日の前日に通知することを目指します。

現在はM1として、LaravelのREST APIとReactを同じオリジンで動かす開発基盤を構築しています。

## 技術構成

| 分類 | 技術 |
| --- | --- |
| バックエンド | Laravel 13 / PHP 8.5 |
| フロントエンド | React / TypeScript / Vite |
| API | Laravel REST API |
| データベース | MySQL 8.4 |
| 開発環境 | Laravel Sail / Docker |
| バックエンドテスト | PHPUnit 12 |
| フロントエンドテスト | Vitest / Testing Library |
| CI | GitHub Actions |

## アプリケーションの構成

ブラウザはLaravelへアクセスし、LaravelがReactの読み込み先を含むHTMLを返します。
Reactは同じオリジンの`/api/health`を呼び出すため、開発時もVite proxyは使用しません。

```text
Browser
  └─ http://localhost:8080
       ├─ Laravel: HTMLを返す
       ├─ Vite: ReactのJavaScriptとCSSを配信する
       └─ /api/health: LaravelがJSONを返す
```

## 必要な環境

- Git
- Docker Desktop（Docker Compose v2を含む）
- macOS、Linux、またはWindows WSL2
- 次のポートが空いていること
  - `8080`: Laravel
  - `3308`: MySQLのホスト側ポート
  - `5174`: Vite開発サーバー

PHP、Composer、Node.js、MySQLをホストOSへ直接インストールする必要はありません。
これらはLaravel SailのDockerコンテナ内で実行します。

## 初回セットアップ

### 1. リポジトリを取得する

```bash
git clone git@github.com:hidebou0113/share_house.git
cd share_house
```

### 2. Composer依存関係を導入する

クリーンクローン直後は`vendor/bin/sail`がまだ存在しません。
最初の`composer install`だけ、SailのPHP 8.5用Composerイメージで実行します。

```bash
docker run --rm \
  --user "$(id -u):$(id -g)" \
  --volume "$PWD:/var/www/html" \
  --workdir /var/www/html \
  laravelsail/php85-composer:latest \
  composer install --ignore-platform-reqs
```

このコマンドはmacOS、Linux、WSL2のシェルを対象としています。

### 3. 環境設定ファイルを用意する

`.env`が存在しない場合だけ、`.env.example`をコピーします。
既存の`.env`にはアプリケーションキーやローカル設定が含まれるため、上書きしないでください。

```bash
test -f .env || cp .env.example .env
```

### 4. Sailを起動する

```bash
./vendor/bin/sail up -d --wait
```

`--wait`は、MySQLを含むコンテナが利用可能になるまで待つオプションです。

### 5. Laravelを初期化する

`.env`の`APP_KEY`が空の場合だけ、アプリケーションキーを生成します。
すでに値が設定されている場合は、`key:generate`を実行しないでください。

```bash
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
```

アプリケーションキーは暗号化やセッション保護に使われます。`migrate`はMySQLへ必要なテーブルを作成します。

### 6. フロントエンド依存関係を導入してビルドする

```bash
./vendor/bin/sail npm ci
./vendor/bin/sail npm run build
```

`npm ci`は`package-lock.json`に記録されたバージョンをそのまま導入します。

## 起動方法

コンテナを起動します。

```bash
./vendor/bin/sail up -d --wait
```

開発中にReactやCSSの変更を即時反映する場合は、別のターミナルでViteを起動します。

```bash
./vendor/bin/sail npm run dev
```

ブラウザで次のURLを開きます。

- アプリケーション: <http://localhost:8080>
- Health API: <http://localhost:8080/api/health>

Health APIは次のJSONを返します。

```json
{
  "status": "ok"
}
```

## 停止方法

```bash
./vendor/bin/sail down
```

このコマンドではMySQLのDocker volumeは削除されないため、次回起動時もデータが残ります。

## テストと品質チェック

### バックエンド

```bash
# PHPコードがLaravelの書式ルールに合っているか確認する
./vendor/bin/sail pint --test

# Laravelの設定キャッシュを消去してPHPUnitを実行する
./vendor/bin/sail composer test
```

### フロントエンド

```bash
# Reactコンポーネントのテスト
./vendor/bin/sail npm run test

# ESLintによる静的解析
./vendor/bin/sail npm run lint

# TypeScriptの型検査
./vendor/bin/sail npm run typecheck

# 本番用アセットを生成できることの確認
./vendor/bin/sail npm run build
```

## ローカルとCIの対応

GitHub ActionsはPull Requestと`main`へのpushで実行されます。
CIでは、バックエンドとフロントエンドを別々のジョブとして並列に確認します。

| 確認内容 | ローカル（Sail） | GitHub Actions |
| --- | --- | --- |
| PHP依存関係 | `sail composer install` | `composer install` |
| DBマイグレーション | `sail artisan migrate` | `php artisan migrate --force` |
| PHP書式 | `sail pint --test` | `./vendor/bin/pint --test` |
| PHPUnit | `sail composer test` | `composer test` |
| npm依存関係 | `sail npm ci` | `npm ci` |
| フロントエンドテスト | `sail npm run test` | `npm run test` |
| ESLint | `sail npm run lint` | `npm run lint` |
| TypeScript | `sail npm run typecheck` | `npm run typecheck` |
| フロントエンドビルド | `sail npm run build` | `npm run build` |

表の`sail`は`./vendor/bin/sail`の省略表記です。

## 主なディレクトリ

| パス | 役割 |
| --- | --- |
| `app/` | Laravelのアプリケーションコード |
| `bootstrap/` | Laravelの起動処理とキャッシュ |
| `config/` | Laravelや外部サービスの設定 |
| `database/` | migration、factory、seeder |
| `resources/js/` | ReactとTypeScriptのコード |
| `resources/css/` | CSSのエントリーポイント |
| `resources/views/` | Laravelが返すBladeテンプレート |
| `routes/` | Web、API、コンソールのルート定義 |
| `tests/` | PHPUnitのUnit testとFeature test |
| `.github/workflows/` | GitHub ActionsのCI設定 |

## CIの安全性

- GitHub Actionsの権限はリポジトリ内容の読み取りだけです。
- 外部Actionは完全なcommit SHAへ固定しています。
- デプロイ処理やproduction secretは扱いません。
- MySQLの認証情報はCI内だけで使用する使い捨てのテスト値です。
