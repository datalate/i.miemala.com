# i.miemala.com
HQ Image Service

## Requirements

- A webserver (nginx is recommended)
- PHP (should work fine with 7.x or newer)
- mysqli-extension for PHP

There may be some other extensions needed as well, but didn't remember to check.

Before deploying the site, remember to edit the `mysql.php` to match your mysql host configuration, remove unnecesary stuff (`.htaccess` files if not using Apache) and maybe this README as well. Yes, this doesn't have any fancy deploy script so that has to be done by hand.

## Schema

```
CREATE TABLE `data` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `fname` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `time` int(10) NOT NULL,
  `ip` tinytext COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  UNIQUE KEY `fname` (`fname`)
) ENGINE=InnoDB AUTO_INCREMENT=2938 DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
```

## Sample nginx config

Add the config inside http-block and edit to your liking.

```
server {
    listen       80;
    server_name  i.miemala.com;
    root /usr/local/www/i.miemala.com/;

    index index.php;

    access_log  /var/log/nginx/i.miemala.com.access.log main;

    client_max_body_size 100M;

    location / {
        try_files $uri $uri/ @image;
    }

    location /img/ {
        autoindex off;
    }

    location @image {
        rewrite ^/(.*)$ /?i=$1 last;
    }

    location ~ \.php$ {
        try_files $fastcgi_script_name =404;

        include fastcgi_params;

        # fastcgi settings
        fastcgi_pass                    unix:/var/run/php-fpm.sock;
        fastcgi_index                   index.php;
        fastcgi_buffers                 8 16k;
        fastcgi_buffer_size             32k;

        # fastcgi params
        fastcgi_param DOCUMENT_ROOT     $realpath_root;
        fastcgi_param SCRIPT_FILENAME   $realpath_root$fastcgi_script_name;
    }
}
```
