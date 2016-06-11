#!/usr/bin/python  

import sys, os    
print "Content-Type: text/plain\r\n\r\n";
print "<<<< CGI Environtment variables in Python >>>>\n\n"
for name, value in os.environ.items():
   print "%s = %s " % (name, value)

