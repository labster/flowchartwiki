<?php

//////////////////////////////////////////////////////////////
//                                                            
//    Copyright (C) Bratislava 2008 Thomas Kock                                         
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
    global $fchw, $wgUploadPath, $wgUploadDirectory;
    $ImgDir  = "/fchw/"; 
	$ImgDir  = str_replace('/', DIRECTORY_SEPARATOR, $ImgDir);
	$DotDir  = str_replace('/', DIRECTORY_SEPARATOR, $fchw['GraphvizDot']);
    $DataDir = str_replace('/', DIRECTORY_SEPARATOR, $wgUploadDirectory.$ImgDir);
	$Filename = hash('ripemd160', $Filename);
    $PNGFile = $DataDir."$Filename.png";
    $MAPFile = $DataDir."$Filename.map";
    $TXTFile = $DataDir."$Filename.txt";
    
    // create upload directory
    if (!is_dir($DataDir)) 
	mkdir($DataDir, 0777); 
    // we need to remove png and map if exists
    if (file_exists($PNGFile)) unlink($PNGFile);
    if (file_exists($MAPFile)) unlink($MAPFile);
    if (file_exists($TXTFile)) unlink($TXTFile);

    error_reporting(E_ALL);
    ini_set('error_reporting', E_ALL);
    ini_set('display_errors', 'On');

    // prepare graphdata and create graph
    $gd = fopen($TXTFile, "w");
    fwrite($gd, $GraphData);
    fclose($gd);    
    $exec = escapeshellarg($DotDir)." -Tpng -o ".escapeshellarg($PNGFile)." ".escapeshellarg($TXTFile);
    if (substr(php_uname(), 0, 7) == "Windows") { 
        $obj = new COM("WScript.Shell");
	$obj->Run("".$exec, 0, true);
    } else {
        exec($exec);
    }
    $exec = escapeshellarg($DotDir)." -Tcmapx -o ".escapeshellarg($MAPFile)." ".escapeshellarg($TXTFile);
    if (substr(php_uname(), 0, 7) == "Windows") { 
	$obj->Run("".$exec, 0, true);
    } else {
        exec($exec);
    }
    
    // results...
    $ImgWeb  = str_replace(DIRECTORY_SEPARATOR, "/", "$wgUploadPath$ImgDir$Filename.png");
    $MAPFile  = str_replace("/", DIRECTORY_SEPARATOR, $MAPFile);
    $temp = trim(str_replace("/>\n", "/>", preg_replace("#<ma(.*)>#", " ", str_replace("</map>", "", implode("", file($MAPFile))))));
    return "<map name='$Filename'>$temp</map><img usemap='#$Filename' src='$ImgWeb' />";
}

