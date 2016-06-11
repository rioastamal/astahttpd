Option Explicit

' ====================================================
' Author    : Rio Astamal a.k.a c0kr3x
' Created   : Thu, 06 Mar 2008 17:07 GMT+7
' Updated   : - 
' Version   : 0.1
' Website   : http://astahttpd.sourceforce.net/
' Email     : c0kr3x@gmail.com
' Desc.     : astahttpd script installer for Windows
' ====================================================

Dim objFSO
Dim objFile
Const PROG_NAME = "astahttpd"
Const PROG_VER = "0.1-RC1"
Const FULL_PROG_NAME = "astahttpd v0.1-RC1"
Const TARGET_BASE_DIR = "astahttpd"
Const ForReading = 1
Const ForWriting = 2
Const ForAppending = 8

Set objFSO = WScript.CreateObject("Scripting.FileSystemObject")

' get scriptname information
Dim objScriptInfo, WShell, objSpecial, objEnv, key
' Set as dictionary object
Set objScriptInfo = WScript.CreateObject("Scripting.Dictionary")
Set WShell = WScript.CreateObject("WScript.Shell")
Set objEnv = WShell.Environment("Process")   ' Get Environment Process

Intro ' Call intro text

WScript.Echo "Initializing environment..." & vbCrLf
WScript.Sleep 1000

objScriptInfo.add "ScriptFullName", WScript.ScriptFullName
objScriptInfo.add "ScriptName", WScript.ScriptName
objScriptInfo.add "ScriptDrive", objFSO.GetDriveName(WScript.ScriptFullName)
objScriptInfo.add "ScriptFolder", WShell.CurrentDirectory
objScriptInfo.add "AppData", WShell.SpecialFolders("AppData")
objScriptInfo.add "MyDocuments", WShell.SpecialFolders("MyDocuments")
objScriptInfo.add "Programs", WShell.SpecialFolders("Programs")
objScriptInfo.add "Temp", objEnv("TEMP")
objScriptInfo.add "Windir", objEnv("WINDIR")
objScriptInfo.add "OS", objEnv("OS")

For Each key in objScriptInfo.Keys()
   WScript.StdOut.Write key & " => "
   WScript.StdOut.WriteLine objScriptInfo(key)
   WScript.Sleep 250
Next
WScript.Echo

Dim strPhpCgiLoc, strInstallDir
Dim boolPHPexists, boolInstallSuccess
Dim sourceDir, intOver, tempName

boolPHPexists = False
boolInstallSuccess = False
sourceDir = Array("bin", "conf", "htdocs", "icons", "lib", "logs", "modules", "vhost")

' ------- PHP Directory checking routine --------------------
Do
   strPhpCgiLoc = inputBox("Where is your PHP directory?" & _
                  vbCrLf & vbCrLf & _
                  "e.g: C:\php", "PHP Directory Location", _
                  "Enter The Location")
                  
   AskUserForExit(strPhpCgiLoc)

   If Not boolCheckPHP(strPhpCgiLoc) Then
      MsgBox "Could not find php-cgi.exe file." & _
      " Make sure the directory is exists and correct.", _
      vbExclamation + vbOkOnly, "Error - " & FULL_PROG_NAME
   Else
      boolPHPexists = True
   End If
Loop While Not boolPHPexists
' ------- End of PHP Directory checking routine --------------------

WScript.Echo
WScript.Echo "PHP-DIR => " & strPhpCgiLoc

' ------- Installation Directory checking routine --------------------
Do
   tempName = inputBox("Enter the destination directory?" & _
                  vbCrLf & _
                  "(The Directory will be created automatically if not exists)"  & _
                  vbCrLf & vbCrLf & _
                  "e.g: C: or C:\Program Files", "Installation Directory", _ 
                  "Enter The Location")
   
   AskUserForExit(tempName)
   strInstallDir = tempName & "\" & TARGET_BASE_DIR
   strInstallDir = objFSO.GetAbsolutePathName(strInstallDir)   ' Fix bad directory   
   
   If objFSO.FolderExists(tempName) Then
      If objFSO.FolderExists(strInstallDir) Then
         intOver = MsgBox("The destination directory """ & strInstallDir & """" & _
                  " already exists." & vbCrLf & vbCrLf & _
                  "Do you want to overwrite it?", _
                  vbQuestion + vbYesNo, PROG_NAME & " v." & PROG_VER)
         ' If user does not want to overwrite, prompt he/she again
         If intOver = vbYes Then
            objFSO.DeleteFolder strInstallDir, True
            boolInstallSuccess = True
         End If
      Else
         boolInstallSuccess = True
      End If
   Else
      MsgBox "The directory """ & tempName & """ not found!", vbExclamation, "Not Found"
   End If
Loop While Not boolInstallSuccess

WScript.Echo "TARGET-DIR => " & strInstallDir
WScript.Echo

' Final ask
Dim finalAsk
finalAsk = MsgBox("astahttpd will be installed to """ & strInstallDir & """" & _
          vbCrLf & _
          "Are you sure want to continue?", vbQuestion + vbYesNo, _
          FULL_PROG_NAME)
If finalAsk = vbNo Then
   MsgBox "Installation Aborted By User!", vbCritical, FULL_PROG_NAME
   WScript.Echo vbCrLf & "Installation Aborted!"
   WScript.Quit   
End If

' If we goes here then everything works fine, I think :)
DoCopy strInstallDir, True
WriteConf strPhpCgiLoc & "\php-cgi.exe", strInstallDir
CreateLauncher
CreateStartMenu
CreateUninstaller
Finish

'-----------------------------------------------------
Function boolCheckPHP(target)
   If objFSO.FolderExists(target) Then
      If objFSO.FileExists(target & "\php-cgi.exe") Then
         boolCheckPHP = True
      End If
   Else
      boolCheckPHP = False
   End If
End Function
'-----------------------------------------------------

'-----------------------------------------------------
Sub DoCopy(target, overwrite) 
   If IsNull(overwrite) or Not overwrite Then 
      overwrite = True
   End If
   
   ' Create the folder first
   objFSO.CreateFolder(strInstallDir)
   
   ' Now start copying
   Dim src, curFolder, curTarget
   For Each curFolder in SourceDir
      src = objScriptInfo("ScriptFolder") & "\" & curFolder
      curTarget = target & "\" & curFolder
      WScript.StdOut.Write "Copying " & curFolder & " to " & curTarget & "..."      
      
      objFSO.CopyFolder src, curTarget, overwrite
      WScript.Sleep 500
      WScript.StdOut.WriteLine " done."
   Next
End Sub
'-----------------------------------------------------

'----------------------------------------------------
Sub AskUserForExit(result)
   Dim ask
   If Trim(result) = "" Then
      ask = MsgBox("Are you sure want to quit?", vbYesNo + vbQuestion, FULL_PROG_NAME)
      If ask = vbYes Then
         MsgBox "Installation Aborted By User!", vbCritical, FULL_PROG_NAME
         WScript.Echo vbCrLf & "Installation Aborted!"
         WScript.Quit
      End if
   End If   
End Sub
'----------------------------------------------------

'-----------------------------------------------------
Sub WriteConf(CgiLoc, rootDir)
   WScript.Echo
   WScript.StdOut.Write "Writing configuration..."
   
   Dim conf
   conf = "<?php" & vbCrLf & vbCrLf & _
          "// WARNING - DO NOT MODIFY UNLESS YOU KNOW WHAT YOU'RE DOING" & vbCrLf & vbCrLf & _
          "// astahttpd root directory location" & vbCrLf & _
          "define('AWS_ROOT_DIR', '" & rootDir & "');" & vbCrLf & vbCrLf & _
          "// Full path to php-cgi.exe" & vbCrLf & _
          "define('PHP_CGI_LOC', '" & CgiLoc & "'); " & vbCrLf & vbCrLf & _
          "?>"
   
   ' write configuration to file
   Dim Stream, fileConf
   ' String filename, bool overwrite, bool unicode
   fileConf = objScriptInfo("AppData") & "\.astahttpd"
   Set Stream = objFSO.CreateTextFile(fileConf, True, False)
   Stream.WriteLine conf
   Stream.Close
   
   Dim strConfContent, aConfReplace(16, 2)
   Set Stream = objFSO.OpenTextFile(objScriptInfo("ScriptFolder") & "\conf\aws.conf.php")

   ' array of string need to be replaced in aws.conf.php file
   aConfReplace(0,0) = "%PERL_PATH%"
   aConfReplace(0,1) = "perl.exe"
   aConfReplace(1,0) = "%PYTHON_PATH%"
   aConfReplace(1,1) = "python.exe"
   aConfReplace(2,0) = "%SHELL_SCRIPT%"
   aConfReplace(2,1) = "cscript.exe"
   aConfReplace(3,0) = "%TEMP_DEF%"
   aConfReplace(3,1) = objScriptInfo("Temp") & "\localhost.bwt"
   aConfReplace(4,0) = "%DOC_ROOT_V1%"
   aConfReplace(4,1) = strInstallDir & "\vhost\local.vhost1"
   aConfReplace(5,0) = "%TEMP_V1%"
   aConfReplace(5,1) = objScriptInfo("Temp") & "\local.vhost1.bwt"
   aConfReplace(6,0) = "%DOC_ROOT_V2%"
   aConfReplace(6,1) = strInstallDir & "\vhost\local.vhost2"
   aConfReplace(7,0) = "%TEMP_V2%"
   aConfReplace(7,1) = objScriptInfo("Temp") & "\local.vhost2.bwt"

   strConfContent = Stream.ReadAll()
   Stream.Close() 
   
   ' replace
   For key = 0 to Ubound(aConfReplace)
      strConfContent = Replace(strConfContent, aConfReplace(key, 0), aConfReplace(key, 1))
      WScript.Sleep 150
   Next
   
   ' overwrite
   Set Stream = objFSO.CreateTextFile(strInstallDir & "\conf\aws.conf.php", True, False)
   Stream.WriteLine strConfContent
   Stream.Close
   
   ' Writing virtual host address to hosts file
   Dim hostsDir 
   hostsDir = objScriptInfo("Windir") & "\system32\drivers\etc"
   ' make a backup
   ' If the backup already exists don't make a backup again
   If Not objFSO.FileExists(hostsDir & "\hosts.before.aws") Then
      objFSO.CopyFile hostsDir & "\hosts", hostsDir & "\hosts.before.aws"
   Else
      ' stupid way, I'm too lazy to seek into the file ;)
      objFSO.CopyFile hostsDir & "\hosts.before.aws", hostsDir & "\hosts"
   End If
   
   ' Append the hosts file
   Set Stream = objFSO.OpenTextFile(hostsDir & "\hosts", ForAppending)
   Stream.WriteLine vbCrLf & "# The two lines below was added by astahttpd web server"
   Stream.WriteLine "127.0.0.1" & chr(9) & "local.vhost1"
   Stream.WriteLine "127.0.0.1" & chr(9) & "local.vhost2"
   
   WScript.Sleep 200
   WScript.StdOut.WriteLine " done."
   
End Sub
'-----------------------------------------------------

'-----------------------------------------------------
Sub CreateStartMenu()
   ' Make a folder inside start menu => programs
   Dim folTarget, fileShortcut, fileUninstall, fileConfig
   folTarget = objScriptInfo("Programs") & "\" & FULL_PROG_NAME
   fileShortcut = folTarget & "\start astahttpd.lnk"
   fileConfig = folTarget & "\edit configuration.lnk"   
   fileUninstall = folTarget & "\Uninstall.lnk"
   
   If objFSO.FolderExists(folTarget) Then
      objFSO.DeleteFolder folTarget, True
   End If
   
   objFSO.CreateFolder folTarget
   
   MakeShortCut folTarget, fileShortcut, strInstallDir & "\start_aws.bat", "Start astahttpd Web Server"
   MakeShortCut folTarget, fileConfig, strInstallDir & "\conf\aws.conf.php", "Edit Configuration"
   MakeShortCut folTarget, fileUninstall, strInstallDir & "\Uninstall.vbs", "Uninstall astahttpd Web Server"
End Sub
'-----------------------------------------------------

'-----------------------------------------------------
Sub CreateLauncher()
   ' Create batch file to launch astahttpd
   Dim batchFile, Stream, Executor, Target
   Executor = chr(34) & strPhpCgiLoc & "\php.exe" & chr(34)
   Target = chr(34) & strInstallDir & "\bin\aws" & chr(34)

   batchFile = "@ECHO OFF" & vbCrLf & _
               "ECHO ***********************************************************" & vbCrLf & _
               "ECHO To stop astahttpd press CTRL-C or just close this window." & vbCrLf & _
               "ECHO." & vbCrLf & _
               "ECHO Starting " & FULL_PROG_NAME & "..." & vbCrLf & _
               "ECHO ************************************************************" & vbCrLf & _
               "ECHO." & vbCrLf & _
               Executor & " " & Target
               
   Set Stream = objFSO.CreateTextFile(strInstallDir & "\start_aws.bat", True, False)
   Stream.WriteLine batchFile
   Stream.Close
End Sub
'-----------------------------------------------------

'-----------------------------------------------------
Sub MakeShortCut(WorkingDir, SCName, SCTarget, SCDesc)
   Dim objShorCut
   Set objShorCut = WShell.CreateShortcut(SCName)
   
   objShorCut.TargetPath = SCTarget
   objShorCut.Description = SCDesc
   objShorCut.WorkingDirectory = WorkingDir
   objShorCut.Save
End Sub
'-----------------------------------------------------

Sub CreateUninstaller()
   Dim strEval
   strEval = "Dim askForUninstall, FS" & vbCrLf & _
             "Set FS = WScript.CreateObject(""Scripting.FileSystemObject"")" & vbCrLf & _
             "askForUninstall = MsgBox(""Are you sure want to uninstall " & FULL_PROG_NAME & "?"", vbQuestion + vbYesNo, ""UNINSTALL"")" & vbCrLf & _
             "If askForUninstall = vbNo Then" & vbCrLf & _
             "WScript.Quit" & vbCrLf & _
             "End If" & vbCrLf & _
            "On Error Resume Next " & vbCrLf & _             
             "' Delete .astahttpd" & vbCrLf & _
            "FS.DeleteFile """ & objScriptInfo("AppData") & "\.astahttpd" & """, True" & vbCrLf & _
            "' Delete shortcut" & vbCrLf & _
            "FS.DeleteFolder """ & objScriptInfo("Programs") & "\" & FULL_PROG_NAME & """, True" & vbCrLf & _
            "' Delete main directory" & vbCrLf & _
            "FS.DeleteFolder """ & strInstallDir & """, True" & vbCrLf & _
            "' Restore" & vbCrLf & _
            "Dim etcDir" & vbCrLf & _
            "etcDir = """ & objScriptInfo("Windir") & "\system32\drivers\etc""" & vbCrLf & _
            "FS.CopyFile etcDir & ""\hosts.before.aws"", etcDir & ""\hosts"", True" & vbCrLf & _
            "FS.DeleteFile etcDir & ""\hosts.before.aws""" & vbCrLf & vbCrLf & _
            "MsgBox ""Uninstall completed."", vbInformation, ""Complete"""
   
   Dim Stream
   Set Stream = objFSO.CreateTextFile(strInstallDir & "\Uninstall.vbs", True, False)
   Stream.WriteLine strEval
   Stream.Close
   
End Sub

'-----------------------------------------------------
Sub Intro()
   WScript.Echo
   WScript.Echo "====================================================="
   WScript.Echo "=        Welcome to The " & FULL_PROG_NAME & "          ="
   WScript.Echo "=              Installation Process                 ="
   WScript.Echo "=====================================================" & vbCrLf
   WScript.Sleep 1000
End Sub
'-----------------------------------------------------

'-----------------------------------------------------
Sub Finish() 
   MsgBox FULL_PROG_NAME & " has been successfully installed to " & strInstallDir & "." , _
   vbInformation, FULL_PROG_NAME
   
   WScript.Echo
   WScript.Echo "******************************************************"
   WScript.Echo "*             " & FULL_PROG_NAME & "                     *"
   WScript.Echo "*        Copyright (2) 2008 Rio Astamal              *"
   WScript.Echo "*       http://astahttpd.sourceforge.net/            *"
   WScript.Echo "******************************************************"
   WScript.Echo
   WScript.Echo "Thank you for using astahttpd web server." & vbCrLf
   WScript.Sleep 1000
End Sub
'-----------------------------------------------------
