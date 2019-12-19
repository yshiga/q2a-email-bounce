# AWS Lambda Function
node.jsで作成する場合の手順です

## 準備
node.js を使用するので以下を参考にインストールします
- [Node\.js / npmをインストールする（for Windows） \- Qiita](http://qiita.com/taipon_rock/items/9001ae194571feb63a5e)
- [MacにNode\.js環境を作る\(nodebrew\) \- Qiita](http://qiita.com/saekis/items/d580d1c2ae4f32a6ae99)

AWS Lambdaでは v4.3 まで対応しているようです

request モジュールが必要なのでインストールします

```
$ cd lambda
$ npm install request
```

## index.js を編集
必要に応じてindex.jsを編集します

## lambda.zip を作成
アップロードするために .zip ファイルを作成します

```
$ cd lambda
$ zip -r lambda.zip index.js node_modules
```

## AWS Lambda にアップロード
以下を参考にLambda関数を作成しアップロードします
- [AWS SESのバウンスメールをchatworkに通知する](http://tk2-207-13211.vs.sakura.ne.jp/2016/03/764/)

1度 Function を作成した後は Code タブの Function Package からアップロードでます

### Lambda 環境変数を設定する
- API_TOKEN： APIトークンを設定する  (qa-plugin.php で定義した`EMAIL_BAUNCE_TOKEN`定数の値です)
- BASE_URL： APIのURLのベース部分 例）https://38qa.net/
