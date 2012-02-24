<?php

//////////////////////////////////////////////////////////////
//
//    Copyright (C) Thomas Kock, Delmenhorst, 2009
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License along
// with this program; if not, write to the Free Software Foundation, Inc.,
// 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
// http://www.gnu.org/copyleft/gpl.html
//
//////////////////////////////////////////////////////////////

function Graphviz($Filename, $GraphData) {
    global $fchw, $wgUploadPath, $wgUploadDirectory, $wgDBprefix;
    $ImgDir  = "/flowchartwiki/";
    $ImgDir  = str_replace('/', DIRECTORY_SEPARATOR, $ImgDir);
    $DotDir  = str_replace('/', DIRECTORY_SEPARATOR, $fchw['GraphvizDot']);
    $DataDir = str_replace('/', DIRECTORY_SEPARATOR, $wgUploadDirectory.$ImgDir);
    $Filename = hash('md5', $wgDBprefix.$Filename);
    $PNGFile = $DataDir."$Filename.png";
    $MAPFile = $DataDir."$Filename.map";
    $DOTFile = $DataDir."$Filename.dot";
    $MD5File = $DataDir."$Filename.dot.md5";

    // create upload directory
    if (!is_dir($DataDir))
    mkdir($DataDir, 0777);

    if (file_exists($MD5File))
    $OldMD5 = implode('', file($MD5File));
    else
    $OldMD5 = "";
    $NewMD5 = md5($GraphData);
    if (Trim($OldMD5) != $NewMD5) {

        // prepare graphdata and create graph
        if (file_exists($DOTFile)) unlink($DOTFile);
        $gd = fopen($DOTFile, "w");
        fwrite($gd, $GraphData);
        fclose($gd);

        // save new md5
        if (file_exists($MD5File)) unlink($MD5File);
        $gd = fopen($MD5File, "w");
        fwrite($gd, $NewMD5);
        fclose($gd);

        // delete png and map
        if (file_exists($PNGFile)) unlink($PNGFile);
        if (file_exists($MAPFile)) unlink($MAPFile);

        // generate graphs
        $exec = escapeshellarg($DotDir)." -Tpng -o ".escapeshellarg($PNGFile)." ".escapeshellarg($DOTFile);
        //$exec = escapeshellarg($DotDir)." -Tpng -o ".$PNGFile." ".$DOTFile;
        if (substr(php_uname(), 0, 7) == "Windows") {
            $obj = new COM("WScript.Shell") or die("Unable to init WScript.Shell for Png file");
            //die("COMMAND: ".$exec);
            $Res = $obj->Run("".$exec, 0, true);
            if ($Res != 0)
            die("Dot error - Png file");
            $obj = null;
        } else {
            exec($exec);
        }
        $exec = escapeshellarg($DotDir)." -Tcmapx -o ".escapeshellarg($MAPFile)." ".escapeshellarg($DOTFile);
        //$exec = escapeshellarg($DotDir)." -Tcmapx -o ".$MAPFile." ".$DOTFile;
        if (substr(php_uname(), 0, 7) == "Windows") {
            $obj = new COM("WScript.Shell") or die("Unable to init WScript.Shell for Map file");
            $Res = $obj->Run("".$exec, 0, true);
            if ($Res != 0)
            die("Dot error - Map file");
            $obj = null;
        } else {
            exec($exec);
        }

    }

    if (!file_exists($MAPFile)) {
        return "MAP file doesn't exists !!!";
    }

    // results...
    $ImgWeb  = str_replace(DIRECTORY_SEPARATOR, "/", "$wgUploadPath$ImgDir$Filename.png");
    $MAPFile  = str_replace("/", DIRECTORY_SEPARATOR, $MAPFile);
    $temp = trim(str_replace("/>\n", "/>", preg_replace("#<ma(.*)>#", " ", str_replace("</map>", "", implode("", file($MAPFile))))));
    return "<map name='$Filename'>$temp</map><img usemap='#$Filename' src='$ImgWeb?TimeStamp=".date("YmdHis", filemtime($MD5File))."' />";
}

