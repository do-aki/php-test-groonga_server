# dooaki\Test\GroongaServer

[![Build Status](https://travis-ci.org/do-aki/php-test-groonga_server.png?branch=master)](https://travis-ci.org/do-aki/php-test-groonga_server)
[![Coverage Status](https://coveralls.io/repos/do-aki/php-test-groonga_server/badge.png?branch=master)](https://coveralls.io/r/do-aki/php-test-groonga_server?branch=master)

Groonga server runner for tests

Requirements
-------------
* PHP 5.3 or later
* [Groonga](http://groonga.org/)

SYNOPSIS
-------------
```php
<?php

use dooaki\Test\GroongaServer;

$server = new GroongaServer(array('protocol' => 'http'));
$server->run();
$fp = stream_socket_client("tcp://localhost:{$server->getPort()}");

fwrite("GET /d/status HTTP/1.0\r\n\r\n");
while (!feof($fp)) {
    echo fgets($fp, 1024);
}

fclose($fp);
```

Installation
-------------
you can install the script with [Composer](http://getcomposer.org/).

in your `composer.json` file:
```
{
    "require": {
        "dooaki/test-groonga_server": "dev-master"
    }
}
```

```
composer.phar install
```

起動オプション
-------------

コンストラクタで groonga サーバの起動オプションを変更できます

例:
```php
new GroongaServer(array(
    'protocol' => 'gqtp',
    'encoding' => 'euc',
));

```

* db

    既存のデータベースファイルを指定することができます。
    指定した場合、このファイルが存在するディレクトリが `作業ディレクトリ` となります

* tempdir

    db を指定しなかった場合に作られる `作業ディレクトリ` の格納先を指定します。
    デフォルトは `sys_get_temp_dir()` が使われます

* protocol (--protocol)

    プロトコル を指定 `http` もしくは `gqtp` (default: `http`)

* port (--port)

    サーバのポートを指定。指定しない場合は、空きポートを探索して自動設定されます

* pid-path (--pid-path)

    サーバのプロセスID を格納するファイルを指定。起動前に削除されます。
    絶対パスを指定した場合はそのファイルが、そうでない場合は 作業ディレクトリからの相対パスとなります (default: 'pid')

* log-path (--log-path)

    ログファイルの保存先を指定。
    絶対パスを指定した場合はそのファイルが、そうでない場合は 作業ディレクトリからの相対パスとなります (default: 'log')

* query-log-path (--query-log-path)

    クエリログを記録する場合、クエリログファイルの保存先を指定。
    絶対パスを指定した場合はそのファイルが、そうでない場合は 作業ディレクトリからの相対パスとなります (default: 指定なし)


以下のオプションについては、指定した場合、それぞれの起動オプションの値としてそのまま渡されます。
各オプションの説明は [groonga コマンドの説明](http://groonga.org/ja/docs/reference/executables/groonga.html) を参照してください

* encoding (--encoding)
* log-level (--log-level)
* bind-address (--bind-address)
* server-id (--server-id)
* document-root (--document-root)
* max-threads (--max-thread)
* config-path (--config-path)
* cache-limit (--cache-limit)
* default-match-escalation-threshold (--default-match-escalation-threshold)


Methods
-------------

### run([$options])
`db` を指定せずに実行すると、一時的な作業ディレクトリを作りその中に DB ファイルを作成します。
一時的な作業ディレクトリはオブジェクトが破棄されるときに自動的に削除されます。

### getPid()
サーバのプロセスID を返します

### getWorkDir()
`作業ディレクトリ` を返します

### getPort()
サーバのポート番号を返します

### getDb()
サーバが開いている DB ファイルを返します

### getLogFile()
ログファイルのパスを返します

### getQueryLogFile()
クエリログファイルのパスを返します


Author
-------------
do_aki <do.hiroaki at gmail.com>

License
-------------
MIT License

