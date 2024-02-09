This is a simple [PHP](https://php.org/) application starter

## Getting Started

Modify the logic of your the PHP application in the `app/index.php` file.

```console
$ echo '<?php phpinfo() ?>' > ./app/index.php
```

You can run things locally with:

```
$ php -t app -S localhost:8080
```

Or you can also use `wasmer run` to run it locally (check out the [Wasmer install guide](https://docs.wasmer.io/install)):

```console
$ wasmer run . --net
```


> [!TIP]
> You can also run `wasmer run wasmer-examples/wordpress-wasmer-starter --net`to run the remote package

Open [http://localhost:8080](http://localhost:8080) with your browser to see the result.


## Deploy on Wasmer Edge

The easiest way to deploy your PHP app is to use the [Wasmer Edge](https://wasmer.io/products/edge).

Live example: https://wordpress-php-starter.wasmer.app/

Run this commmand to deploy to Wasmer Edge:

```bash
wasmer deploy
```

> [!NOTE]
> You will need to change the namespace in `wasmer.toml` to your own namespace and app name in `app.yaml` to your own app name.
