<?php
class CheckFchw extends SpecialPage {
        function CheckFchw() {
                SpecialPage::SpecialPage("CheckFchw");
                wfLoadExtensionMessages('checkfchw');
        }
 
        function execute( $par ) {
                global $wgRequest, $wgOut, $wgScriptPath, $fchw, $dbr, $wgVersion, $IP, $wgDBprefix;
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
		global $fchw, $wgUploadPath, $wgUploadDirectory;
		$ImgDir  = "/flowchartwiki/"; 
		$ImgDir  = str_replace('/', DIRECTORY_SEPARATOR, $ImgDir);
		$DotDir  = str_replace('/', DIRECTORY_SEPARATOR, $fchw['GraphvizDot']);
		$DataDir = str_replace('/', DIRECTORY_SEPARATOR, $wgUploadDirectory.$ImgDir);
		$Filename = "FchwTest";
		$PNGFile = $DataDir."$Filename.png";
		$PNGPath = $wgUploadPath.$ImgDir."$Filename.png";
		$DOTFile = $DataDir."$Filename.dot";
    
		if (!is_dir($DataDir)) 
		    mkdir($DataDir, 0777); 
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
		
		$exec = escapeshellarg($DotDir)." -Tpng -o ".$PNGFile." ".$DOTFile;
		if (substr(php_uname(), 0, 7) == "Windows") { 
    		    $obj = new COM("WScript.Shell") or die("Unable to init WScript.Shell for Png file"); 
		    $Res = $obj->Run("".$exec, 0, true);
		    if ($Res != 0) 
			die("Dot error - Png file");
		    $obj = null;
		} else {
    		    exec($exec);
		}
	    
		$ExecTest = file_exists($PNGFile);
		$output .= Status($ExecTest, wfMsg('checkfchwGraphVizExecTest'), wfMsg('checkfchwGraphVizExecTestHint'));
		
		// FINAL STATUS
		$output .= "<tr><td colspan='2' style='height: 2px'>&nbsp;</td></tr>";
		$output .= Status(($dirtouch) && ($dircheck) && ($graphvizpath) && ($graphvizexec) && ($fchwdb) && ($ExecTest), wfMsg('checkfchwTotal'), "");
		$output .= "</table>";

		$output .= "</td><td style='padding-left: 32px;'>";

		if ($ExecTest) {
		    $output .= "<h5>Test-Graphics</h5><img src='$PNGPath?TimeStamp=".date("YmdHis")."' alt='' />";
		}
		
		$output .= "</td></tr></table>";
		
                $wgOut->addHTML( $output );
        }
}
