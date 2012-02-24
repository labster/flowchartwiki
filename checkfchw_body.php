<?php
class CheckFchw extends SpecialPage {
        function CheckFchw() {
                SpecialPage::SpecialPage("CheckFchw");
                wfLoadExtensionMessages('checkfchw');
        }
 
        function execute( $par ) {
                global $wgRequest, $wgOut, $wgScriptPath, $fchw, $dbr;
 
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
		$output = "";
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
		    $dbr->query("select * from fchw_relation where from_id=0");
		    $fchwdb = true;
	    	} catch (Exception $e) {
		    $fchwdb = false;
		}		
		$output .= Status($fchwdb, wfMsg('checkfchwDbTables'), wfMsg('checkfchwDbTablesHint'));
		
		$output .= "<tr><td colspan='2' style='height: 2px'>&nbsp;</td></tr>";
		$output .= Status(($dirtouch) && ($dircheck) && ($graphvizpath) && ($graphvizexec) && ($fchwdb), wfMsg('checkfchwTotal'), "");
		$output .= "</table>";

		
		
		
                $wgOut->addHTML( $output );
        }
}

