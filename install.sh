#!/bin/bash
# 
# Author    : Rio Astamal
# Created   : Wed, 23 Feb 2008 02:46:56 GMT+7
# Updated   : Fri, 08 Peb 2008 01:24:14 GMT+7 
# Version   : 0.1
# Website   : http://astahttpd.sourceforce.net/
# Email     : c0kr3x@gmail.com
# Desc.     : astahttpd script installer
# 

IS_ROOT=`whoami`
if [ "$IS_ROOT" != "root" ]; then
   echo "You MUST run this command as root."
   exit 1
fi

# ready made color variables
# source http://www.intuitive.com/wicked/showscript.cgi?011-colors.sh
initializeANSI()
{
  esc=""
  #esc="\E"

  blackf="${esc}[30m";   redf="${esc}[31m";    greenf="${esc}[32m"
  yellowf="${esc}[33m"   bluef="${esc}[34m";   purplef="${esc}[35m"
  cyanf="${esc}[36m";    whitef="${esc}[37m"
  
  blackb="${esc}[40m";   redb="${esc}[41m";    greenb="${esc}[42m"
  yellowb="${esc}[43m"   blueb="${esc}[44m";   purpleb="${esc}[45m"
  cyanb="${esc}[46m";    whiteb="${esc}[47m"

  boldon="${esc}[1m";    boldoff="${esc}[22m"
  italicson="${esc}[3m"; italicsoff="${esc}[23m"
  ulon="${esc}[4m";      uloff="${esc}[24m"
  invon="${esc}[7m";     invoff="${esc}[27m"

  reset="${esc}[0m"
}

initializeANSI

targetconf="$HOME/.astahttpd"
targetdir=""
phpdir=""

cat << INTRO
${invon}
==========================================================
        ${italicson}astahttpd v0.1-RC1 Installer Script  ${italicsoff}             
==========================================================${reset}

INTRO

echo -n "${redf}${boldon}Do not add ending slash on directory, "
echo "e.g: /usr/local/php5.2.3${reset}"
echo -n "Where is your PHP directory?:${reset} "
read phpdir

# check
ls $phpdir/bin/php 1>/dev/null 2>/dev/null
if [ $? -ne 0 ]; then
   echo ""
   echo "Could not find PHP directory in '$phpdir', make sure it exists."
   echo "${redf}${ulon}${boldon}INSTALATION ABORTED${reset}"
   exit 111
fi

echo ""
echo -n "${redf}${boldon}Do not add ending slash on directory, "
echo "e.g: $HOME${reset}"
echo -n "Enter the instalation destination path: "
read targetdir

ls $targetdir 1>/dev/null 2>/dev/null
# check the directory whether is exists or not
if [ $? -ne 0 ]; then
	echo -n "Directory '$targetdir' is not exists, trying to create..."
	# dir is not exists, force to create it
	mkdir -p $targetdir 1>/dev/null 2>/dev/null
	# check the result
	if [ $? -ne 0 ]; then
		echo "${redf}${boldon}FAILED.${reset}"
		echo -n "Could not create directory '$targetdir', make sure you have"
		echo -e " write permission on it. \n(Tips: try use \"sudo\" command)."
		echo "${redf}${ulon}${boldon}INSTALATION ABORTED${reset}"
		exit 111
	fi
	echo "${greenf}done.${reset}"
fi

echo ""
echo "astahttpd will be installed to '${targetdir}/astahttpd'."
ask=""
echo -n "Are you sure (y/n)? "
read ask
if [ "$ask" = "n" ]; then
   echo "${redf}${ulon}${boldon}INSTALATION ABORTED BY USER${reset}"
   exit 111 
fi

#check for previous instalation
ls $targetdir/astahttpd 1>/dev/null 2>/dev/null
if [ $? -eq 0 ]; then
   echo ""
   echo "Directory astahttpd on $targetdir already exists."
   ask=""
   echo -n "Do you want to remove it (y/n)? "
   read ask
   if [ "$ask" = "y" ]; then
      rm -dfr $targetdir/astahttpd 1>/dev/null 2>/dev/null
      if [ $? -ne 0 ]; then
         echo "Could not delete old astahttpd directory on $targetdir."
         echo "${redf}${ulon}${boldon}INSTALATION ABORTED${reset}"
      fi
   else
      echo "${redf}${ulon}${boldon}INSTALATION ABORTED BY USER${reset}"
      exit
   fi
fi

# make the root directory of astahttpd web server
echo ""
echo -n "Creating astahttpd dir on '$targetdir'..."
mkdir $targetdir/astahttpd 1>/dev/null 2>/dev/null
# check result
if [ $? -ne 0 ]; then
	echo "${redf}${boldon}FAILED.${reset}"
	echo -n "Could not create directory 'astahttpd' on '$targetdir', "
	echo "make sure you have permission on it."
	echo "${redf}${ulon}${boldon}INSTALATION ABORTED${reset}"
	exit 111
fi

echo "${greenf}done.${reset}"
targetdir="$targetdir/astahttpd"

thedir="bin conf htdocs icons lib logs modules vhost"
for a in $thedir
do
	echo -n "copying $a to $targetdir..."
	sleep 0.5
	cp -fLR $a $targetdir 1>/dev/null 2>/dev/null
	# check result
	if [ $? -ne 0 ]; then
		echo "${redf}${boldon}FAILED.${reset}"
		echo -n "Could not copy $a to destination dir. Make sure "
		echo "you have write permission to it."
      echo "${redf}${ulon}${boldon}INSTALATION ABORTED${reset}"
		exit 111
	fi
	chmod 755 $targetdir/$a 1>/dev/null 2>/dev/null
	chmod -R 644 $targetdir/$a/* 1>/dev/null 2>/dev/null
   echo "${greenf}done.${reset}"
done

# give execution permission to aws
chmod +x $targetdir/bin/aws 1>/dev/null 2>/dev/null
# vhost
chmod -R 755 $targetdir/vhost/local.vhost1
chmod -R 755 $targetdir/vhost/local.vhost2
chmod 644 $targetdir/vhost/local.vhost1/pub/restrict.txt

data="<?php\n"
data=$data"// astahttpd root directory location\n"
data=$data"define('AWS_ROOT_DIR', '$targetdir');\n\n"
data="$data// location of the php-cgi binary\n"
data=$data"define('PHP_CGI_LOC', '$phpdir/bin/php-cgi');"
data="$data\n?>"

# make a backup
cat $targetdir/bin/aws > /tmp/aws.tmp 2>/dev/null
if [ $? -ne 0 ]; then
   echo "Could not create temporary file in /tmp directory."
   echo "Make sure you have write permission on /tmp dir."
   echo -n "Rolling back..."
   rm -dfr $targetdir 1>/dev/null 2>/dev/null
   if [ $? -ne 0 ]; then
      echo "${redf}${boldon}FAILED.${reset}"
      echo "Could not delete '$targetdir'. You can delete it manually."
      echo "${redf}${ulon}${boldon}INSTALATION ABORTED${reset}"
      exit 111
   fi
   echo "${greenf}done.${reset}"
   echo "${redf}${ulon}${boldon}INSTALATION ABORTED${reset}"
   exit 111
fi

echo ""
echo -n "Writing configuration to $targetconf..."

# overwrite aws
echo \#\!$phpdir/bin/php > $targetdir/bin/aws
# append with the backup
sleep 1
cat /tmp/aws.tmp >> $targetdir/bin/aws&&rm /tmp/aws.tmp

# overwrite aws.conf.php
cp "${targetdir}/conf/aws.conf.php" /tmp/aws.conf.bak 1>/dev/null 2>/dev/null
# replace some data
sed -e 's:%PERL_PATH%:/usr/bin/perl:' -e 's:%PYTHON_PATH%:/usr/bin/python:' \
    -e 's:%SHELL_SCRIPT%:/bin/bash:' -e 's:%TEMP_DEF%:/tmp/localhost.bwt:' \
    -e "s:%DOC_ROOT_V1%:${targetdir}/vhost/local.vhost1:" \
    -e "s:%TEMP_V1%:/tmp/local.vhost1.bwt:" \
    -e "s:%DOC_ROOT_V2%:${targetdir}/vhost/local.vhost2:" \
    -e "s:%TEMP_V2%:/tmp/local.vhost2.bwt:" \
    /tmp/aws.conf.bak > "${targetdir}/conf/aws.conf.php"
# delete backup
rm /tmp/aws.conf.bak 1>/dev/null 2>/dev/null

#make backup of /etc/hosts file
ls /etc/hosts.before.aws 1>/dev/null 2>/dev/null
if [ $? -ne 0 ]; then
   # the file didn't exists yet
   cp /etc/hosts /etc/hosts.before.aws 1>/dev/null 2>/dev/null
else
   # the was exists
   cp /etc/hosts.before.aws /etc/hosts 1>/dev/null 2>/dev/null
fi

VHOST_DATA="# Line below was added by astahttpd web server\n"
VHOST_DATA="${VHOST_DATA}127.0.0.1  local.vhost1 local.vhost2\n"
echo -e $VHOST_DATA > /tmp/hosts.bak

# write virtual host
while read line
do
   echo -e $line >> /tmp/hosts.bak
done < /etc/hosts

# now copy the hosts.bak
cp -fLR /tmp/hosts.bak /etc/hosts 1>/dev/null 2>/dev/null
rm /tmp/hosts.bak 1>/dev/null 2>/dev/null

echo -e $data > $targetconf
echo -e $data > /root/.astahttpd
sleep 1
echo "${greenf}done.${reset}"

echo -e "\nastahttpd has been successfully installed to '$targetdir'.\n"
echo "${invon}***********************************************"
echo "*           astahttpd v0.1-RC1                *"
echo "*        Copyright (c) 2008 Rio Astamal       *"
echo "*      http://astahttpd.sourceforge.net/      *"
echo "***********************************************${invoff}"
sleep 1
echo -e "\nThank you for using astahttpd web server.\n"
echo ${reset}

