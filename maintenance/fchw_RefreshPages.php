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

require_once ( getenv('MW_INSTALL_PATH') !== false
    ? getenv('MW_INSTALL_PATH')."/maintenance/commandLine.inc"
    : dirname( __FILE__ ) . '/../../../maintenance/commandLine.inc' );
require_once("$IP/maintenance/counter.php");
require_once("./extensions/flowchartwiki/lib.php");
require_once("./extensions/flowchartwiki/linktypes.php");

global $wgParser, $wgTitle;
global $smwgEnableUpdateJobs, $wgServer;
	global $wgDBprefix;
$smwgEnableUpdateJobs = false; // do not fork additional update jobs while running this script

$dbr = &wfGetDB(DB_MASTER);
$dbr->query("delete from ".$wgDBprefix."fchw_relation");
$end = $dbr->selectField('page', 'max(page_id)', false, 'SMW_refreshData' );
$counter = 0;
$options = new ParserOptions();
for ($id = 0; $id <= $end; $id++) {
    $title = Title::newFromID($id);
    if (($title === NULL)) continue;
    $revision = Revision::newFromTitle($title);
    if ($revision === NULL) continue;
    $parserOutput = $wgParser->parse($revision->getText(), $title, $options, true, true, $revision->getID());
    print "Page $title\n";
    $wgTitle = $title;
    $u = new LinksUpdate($title, $parserOutput);
    //$u->doUpdate();
    //fchw_UpdateLinks($u);
    $counter++;
}

print("Done");


