PERFORMANCE NOTE
-----------------
The Windows version of astahttpd is much slower than the Linux version. If
you turn on live debugging, you'll realize that the thing that makes
astahttpd slow in Windows is proc_open() function.


Most of the source code I wrote in Linux environment, so the line ending is
\n. If you are using Windows and try to open using notepad, the result will
looks ugly. Make sure to use Wordpad or use powerful and open source code
editor like Notepad++.

See file INSTALL_WIN32.txt for installation instruction.