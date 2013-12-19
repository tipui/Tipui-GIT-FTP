PHP-GIT-FTP
===========

Send modified files to FTP based on GIT changes


How to use
===========

Into command shell, enter on path of your git repository sources.
Type the command (sample):

> git whatchanged --name-status --since '3 days ago' --oneline > git_changes.txt


The file "git_changes.txt" will be createad with something like this

```
M  folder/subfolder/file.php
M  folder/other.php
A  folder/foo/file.txt
```


The file must have this format to works with PHP-GIT-FTP scripts.

No problem with file containing the git comments like:

```
7bd2e6c  - commited comments...
M  folder/subfolder/file.php
M  folder/other.php
A  folder/foo/file.txt
```

All those comments are ignored and filtered.




Optional default settings
===========

In the file **settings.php**:

* $ftp_host
* $ftp_user
* $ftp_pass
* $local_base
* $remote_base
* $queue_file



How to run?
===========

Just run in your browser.
ie: http://localhost/php-git-ftp



Requirements
===========
PHP 5+ with FTP library enabled

OS: Windows or Unix (lLinux, OS-X)
Webserver: Apache, Xitami, IIS, Nginx

Note: It is possible to run using only PHP without local webserver and browser.
The next updates will have full details on how to do and more improvements.
