Update
------

I wrote astahttpd back in early 2008 as a fun project and stop developing it
at the same year. At that time PHP did not have built-in web server. I don't
have plan neither to make new features nor do bug fixing on astahttpd.

What is astahttpd?
------------------

astahttpd is free and open source web server written using 100% pure PHP. It
uses socket_select() to achieve non-blocking TCP connection 
(asynchronus alike). Currently astahttpd v0.1-RC1 support these 
following features:

   - GET, POST, and HEAD methods
   - Alias directories
   - Modular Architecture
   - CGI Processing (mod_cgi & mod_cgi_header)
   - Virtual Host (mod_vhost)
   - URL Rewrite (mod_rewrite)
   - Basic Authentication  (mod_auth_basic)
   - Digest Authentication (mod_auth_digest)
   - Log written to file (mod_log *)
   - Bandwidth Limiting (mod_bandwidth)
   - Live server status (mod_status)
   - Content encoding using gzip or deflate (mod_encoding)
   - Directory Browsing (mod_dir_browser *)
   - Win32 Support *
   - Simple caching (mod_cache) *
   - Autofix URI (mod_autofix_uri) *
   - Static content (mod_static *)

Note: * new in this release

You need at least PHP 5.2.3 to run astahttpd since it implements Object
Oriented features that only available on PHP5.

astahttpd SHOULD NOT be used in production environment due many performance and
security issue that has not been fixed yet.

The Idea
--------

1. The idea behind the making of astahttpd is simple, I just need simple
web server for astasms (an sms server written also in pure PHP). Since
astasms front-end is web based, so it needs a web server. I don't want
to use Apache or other mainstream web server player, because i think
it's too heavy if used only for supporting front-end.

   After tried few simple httpd server written by somebody else, but most of them
just make me more frustating. Many of them is really hard to install and
configure and the worst, i even cannot compile them :( .

   So I started to make my own web server using my favorite language PHP. The first
thnik I need to decide is whether to use PHP4 or PHP5. I think OOP in PHP5
is very good and make software maintanance easier. So I decided to use PHP5.

2. Just for fun :) .

Current Version
---------------

Current version that shipped with this distribution is astahttpd v0.1-RC1

Latest Release
--------------

You can obtain astahttpd latest release on project homepage at
https://github.com/astasoft/astahttpd

Installation
------------

Quick start

```
$ git clone git@github.com:astasoft/astahttpd
$ cd astahttpd
$ cp conf/aws.conf.php.sample conf/aws.conf.php
$ php bin/aws
```

Open your web browser and point to http://localhost:8000

For details see file called INSTALL for Linux and INSTALL_WIN32 for
Windows users.

License
-------

astahttpd is licensed under GNU GPL v3, for more information see file
called LICENSE.txt

Author
-------
Rio Astamal <me@rioastamal.net>

Screenshots
------
![alt text](https://a.fsdn.com/con/app/proj/astahttpd/screenshots/156734.jpg "Directory Browsing")

![alt text](https://a.fsdn.com/con/app/proj/astahttpd/screenshots/156732.jpg "PHP Info - CGI")

![alt text](https://a.fsdn.com/con/app/proj/astahttpd/screenshots/158123.jpg "HTTP Basic Authentication")

![alt text](https://a.fsdn.com/con/app/proj/astahttpd/screenshots/158125.jpg "Default Document Root")

![alt text](https://a.fsdn.com/con/app/proj/astahttpd/screenshots/166194.jpg "Server Status via mod_status")
