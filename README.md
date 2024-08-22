This is a [Wordpress](https://wordpress.org/) application project.

## Getting Started

You can run the development server:

```bash
php -S localhost:8080 -t app
```

You can also run the Wordpress example using Wasmer (check out the [install guide](https://docs.wasmer.io/install)):

```bash
wasmer run .
```

Open [http://127.0.0.1:8080](http://127.0.0.1:8080) with your browser to see your Wordpress app.

## Deploy on Wasmer Edge

The easiest way to deploy your Wordpress app is to use the [Wasmer Edge](https://wasmer.io/products/edge).

Live example: https://wordpress-wasmer-examples.wasmer.app/

Deploy to Wasmer Edge:

```bash
wasmer deploy
```
