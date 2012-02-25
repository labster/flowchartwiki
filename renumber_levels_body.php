<?php

//////////////////////////////////////////////////////////////
//
//    Copyright (C) Thomas Kock, Delmenhorst, 2008, 2009
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

class FchwRenumLevels extends SpecialPage {
    function FchwRenumLevels() {
        SpecialPage::SpecialPage("FchwRenumLevels");
        wfLoadExtensionMessages('fchwrenumlevels');
    }

    function execute( $par ) {
        global $wgRequest, $wgOut, $wgScriptPath, $fchw, $dbr;
        global $wgDBprefix;
        $this->setHeaders();

        # Get request data from, e.g.
        $param = $wgRequest->getText('param');

        # Output
        $dbr = wfGetDB( DB_MASTER );
        $res = $dbr->query("select * from ".$wgDBprefix."category", 'FchwRenumLevels' );
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

