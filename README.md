Pox
===


Pox is (yet another) PHP MVC web framework with the following features.

- Built-in caching layer transparently caches business objects to a global,
  shared memory (memcached, eaccelerator, APC, etc).
- Data is lazily-loaded enabling objects to be instantiated without hitting the
  database.
- Collections of objects are automatically handled and lazily loaded as the data
  is paged.

Pox includes some or all of the following open-source software:
- Smarty templating system <http://www.smarty.net>
- PEAR <http://pear.php.net>
- Zend Framework <http://framework.zend.com>
- Phing build system <http://phing.info>


Configuring Apache
==================


(These instructions are specific to Apache, however most web servers support
similar features.)

To enable URL rewriting, be sure to allow `.htaccess` for this server context.

    AllowOverride All

TIP: While `.htaccess` files make for easier development, disable
`AllowOverride` in production use, and place your `mod_rewrite` rules direclty
in the Apache config.

Pox is intended to be deployed using Capistrano-style symlinks for user- and
system-generated data.  User-uploaded data should be stored in
`artifacts/uploaded`. You'll need an Apache `Alias` directive to make this
content web-accessible.

    Alias /uploaded /path/to/real/uploaded


----


More documentation coming soon...


_see LICENSE for copyright and license info_
