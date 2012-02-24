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

class CheckFchw extends SpecialPage {
    function CheckFchw() {
        SpecialPage::SpecialPage("CheckFchw");
        wfLoadExtensionMessages('checkfchw');
    }

    function execute( $par ) {
        global $wgRequest, $wgOut, $wgScriptPath, $fchw, $dbr, $wgVersion, $IP, $wgDBprefix;
        global $wgUploadDirectory;
        $dbr = wfGetDB( DB_SLAVE );
        $this->setHeaders();

        # Get request data from, e.g.
        $param = $wgRequest->getText('param');

        # Do stuff
        # ...

        function Status($Ok, $Text, $Hint) {
            //<strong>".(($Ok) ? "ok" : "failed")."</strong>
            return "<tr><td style='padding-left: 4px; padding-right: 16px; background: ".(($Ok) ? "green" : "red").";'><strong>".(($Ok) ? "OK" : "ERROR")."</strong></td><td valign='top' style='padding: 8px; background: ".(($Ok) ? "green" : "red").";'><strong>$Text</strong><br /><font color='white'>".(($Ok) ? "" : $Hint)."</font></td></tr>";
        }


        # Output

        # Output
        $output = "";
        $output .= "<p>PHP version: <strong>".phpversion()."</strong></p>";
        $output .= "<p>Platform: <strong>".php_uname()."</strong></p>";
        $output .= "<p>Mediawiki version: <strong>".$wgVersion."</strong></p>";
        $output .= "<p>Database: <strong>".$dbr->getSoftwareLink()." ".$dbr->getServerVersion()."</strong></p>";
        $output .= "<p>Database prefix: <strong>".$wgDBprefix."</strong></p>";
        $output .= "<p>FlowChartWiki version: <strong>".fchw_version."</strong></p>";
        $output .= "<p>MediaWiki \$wgUploadDirectory: <strong>".$wgUploadDirectory."</strong></p>";
        $output .= "<table border='0' cellpadding='0' cellspacing='0'><tr><td>";

        $output .= "<table border='0' cellpadding='0' cellspacing='0'>";
        $dir = dirname(dirname(dirname(__FILE__))) . '/images/flowchartwiki';
        $dircheck = file_exists($dir);
        $output .= Status($dircheck, wfMsg('checkfchwFolderCreated'), wfMsg('checkfchwFolderCreatedHint'));

        $dirtouch = false;
        if ($dircheck) {
            $dirtouch = (is_writable($dir));
        }
        $output .= Status($dirtouch, wfMsg('checkfchwFolderPermissions'), wfMsg('checkfchwFolderPermissionsHint'));

        $graphvizpath = isset( $fchw['GraphvizDot']);
        $output .= Status($graphvizpath, wfMsg('checkfchwGraphVizPath'), wfMsg('checkfchwGraphVizPathHint'));

        $graphvizexec = false;
        if ($graphvizpath) {
            $graphvizexec = is_executable( $fchw['GraphvizDot']);
        }
        $output .= Status($graphvizexec, wfMsg('checkfchwGraphVizExec'), wfMsg('checkfchwGraphVizExecHint'));

        $fchwdb = false;
        $dbr = &wfGetDB(DB_MASTER);
        try {
            $dbr->query("select * from ".$wgDBprefix."fchw_relation where from_id=0");
            $fchwdb = true;
        } catch (Exception $e) {
            $fchwdb = false;
        }
        $output .= Status($fchwdb, wfMsg('checkfchwDbTables'), wfMsg('checkfchwDbTablesHint'));

        // REAL GRAPHVIZ TEST
        $execTest = false;
        $execTestMessage = wfMsg('checkfchwGraphVizExecTestHint1');
        if (($dirtouch) && ($dircheck) && ($graphvizpath) && ($graphvizexec) && ($fchwdb)) {
            $execTestMessage = wfMsg('checkfchwGraphVizExecTestHint2');
            global $fchw, $wgUploadPath, $wgUploadDirectory;
            $ImgDirWeb  = "/flowchartwiki/";
            $ImgDir  = str_replace('/', DIRECTORY_SEPARATOR, $ImgDirWeb);
            $DotDir  = str_replace('/', DIRECTORY_SEPARATOR, $fchw['GraphvizDot']);
            $DataDir = str_replace('/', DIRECTORY_SEPARATOR, $wgUploadDirectory.$ImgDir);
            $Filename = "FchwTest";
            $PNGFile = $DataDir."$Filename.png";
            $PNGPath = $wgUploadPath.$ImgDirWeb."$Filename.png";
            $DOTFile = $DataDir."$Filename.dot";

            //$DataDir should exist due to tests above.
            //if (!is_dir($DataDir))
            //mkdir($DataDir, 0777);
            if (file_exists($DOTFile))
            unlink($DOTFile);
            if (file_exists($PNGFile))
            unlink($PNGFile);

            $gd = fopen($DOTFile, "w");
            fwrite($gd, "
digraph G { size =\"7,2.4\"; { rank = same; \"Flightbooking\";  }
{ rank = same; \"ValueStream\";  }
{ rank = same; \"GettingStarted\";  }
{ rank = same; \"ShapeTest\";  }
\"Flightbooking\" [URL=\"/wiki/index.php/Category:Flightbooking\",shape=rect,color=yellow, fontcolor=black, style=filled,];
\"Flightbooking\" [URL=\"/wiki/index.php/Category:Flightbooking\",];
\"GettingStarted\" [URL=\"/wiki/index.php/Category:GettingStarted\",shape=rect,color=firebrick2, fontcolor=black, style=filled,];
\"GettingStarted\" [URL=\"/wiki/index.php/Category:GettingStarted\",];
\"ShapeTest\" [URL=\"/wiki/index.php/Category:ShapeTest\",shape=rect,color=forestgreen, fontcolor=black, style=filled,];
\"ShapeTest\" [URL=\"/wiki/index.php/Category:ShapeTest\",];
\"ValueStream\" [URL=\"/wiki/index.php/Category:ValueStream\",shape=box,color=blue, fontcolor=black, style=filled,];
\"ValueStream\" [URL=\"/wiki/index.php/Category:ValueStream\",];
\"Flightbooking\"->\"ValueStream\" [  ];
\"Flightbooking\"->\"ValueStream\" [  ];
\"GettingStarted\"->\"ShapeTest\" [  ];
\"GettingStarted\"->\"ShapeTest\" [  ];
\"ValueStream\"->\"GettingStarted\" [  ];
}

        ");
            fclose($gd);

            $exec = escapeshellarg($DotDir)." -Tpng -o ".escapeshellarg($PNGFile)." ".escapeshellarg($DOTFile);
            //$exec = escapeshellarg($DotDir)." -Tpng -o ".$PNGFile." ".$DOTFile;
            if (substr(php_uname(), 0, 7) == "Windows") {
                $obj = new COM("WScript.Shell") or die("Unable to init WScript.Shell for Png file");
                $Res = $obj->Run("".$exec, 0, true);
                //if ($Res != 0)
                //die("Dot error - Png file");
                $obj = null;
            } else {
                exec($exec);
            }

            $execTest = file_exists($PNGFile);
        }
        $output .= Status($execTest, wfMsg('checkfchwGraphVizExecTest'), $execTestMessage );

        // FINAL STATUS
        $output .= "<tr><td colspan='2' style='height: 2px'>&nbsp;</td></tr>";
        $output .= Status(($dirtouch) && ($dircheck) && ($graphvizpath) && ($graphvizexec) && ($fchwdb) && ($execTest), wfMsg('checkfchwTotal'), "");
        $output .= "</table>";

        $output .= "</td><td style='padding-left: 32px;'>";

        if ($execTest) {
            $output .= "<h5>Test-Graphics</h5><img src='$PNGPath?TimeStamp=".date("YmdHis")."' alt='' />";
        }

        $output .= "</td></tr></table>";

        $wgOut->addHTML( $output );
    }
}
