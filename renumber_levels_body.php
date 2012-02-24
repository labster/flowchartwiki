<?php
class FchwRenumLevels extends SpecialPage {
        function FchwRenumLevels() {
                SpecialPage::SpecialPage("FchwRenumLevels");
                wfLoadExtensionMessages('fchwrenumlevels');
        }
 
        function execute( $par ) {
                global $wgRequest, $wgOut, $wgScriptPath, $fchw, $dbr;
 
                $this->setHeaders();
 
                # Get request data from, e.g.	
                $param = $wgRequest->getText('param');
 
                # Output
		$dbr = wfGetDB( DB_MASTER );	
		$res = $dbr->query("select * from category", 'FchwRenumLevels' );
		$Categories = "";
    		while ( $row = $dbr->fetchObject( $res ) ) {
		    $n = $row->cat_title;
		    $Categories .= "<option value='$n'>$n</option>";
		}
    		$dbr->freeResult($res);
		$output = "<p><form method='post'>
<table border='0' cellpadding='0' cellspacing='0'>
<tr><td>".wfMsg("fchwrenumlevelsCategory")."</td><td><select name='RenumCategory'>$Categories</select></td></tr>
<tr><td>".wfMsg("fchwrenumlevelsStartWith")."</td><td><input name='RenumStart' type='text' value='1000'></td></tr>
<tr><td>".wfMsg("fchwrenumlevelsStep")."</td><td><input name='RenumStep' type='text' value='10'></td></tr>
<tr><td>&nbsp;</td><td><input type='submit' name='RenumButton' value='".wfMsg("fchwrenumlevelsRenum")."'></td></tr>
</table>
</form></p>";

		if (isset($_POST['RenumButton'])) {
    	    	    $output .= "<p><hr>";
		    $RenumStart = 1000;
		    if (isset($_POST['RenumStart'])) 
			$RenumStart = $_POST['RenumStart'];
		    $RenumStep = 10;
		    if (isset($_POST['RenumStep'])) 
			$RenumStep = $_POST['RenumStep'];
		    $RenumCategory = "";
		    if (isset($_POST['RenumCategory'])) 
			$RenumCategory = $_POST['RenumCategory'];

		    // 
		    $output.= "
Levels are not changing directly due a timing problems (timeout). Please, ask your administrator to run a command:
<pre>php ./extensions/flowchartwiki/maintenance/fchw_RenumLevels.php $RenumCategory $RenumStart $RenumStep</pre>";
		}		
		
                $wgOut->addHTML( $output );
        }
}

