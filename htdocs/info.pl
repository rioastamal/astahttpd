#!/usr/bin/perl
##
##  printenv -- demo CGI program which just prints its environment
##

print "Content-type: text/plain\r\n\r\n";
print "<<<< CGI Environtment variables in Perl >>>>\n\n";
foreach $var (sort(keys(%ENV))) {
    $val = $ENV{$var};
    $val =~ s|\n|\\n|g;
    $val =~ s|"|\\"|g;
    print "${var}=\"${val}\"\n";
}

