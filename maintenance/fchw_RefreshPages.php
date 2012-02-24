<?php
require_once ( getenv('MW_INSTALL_PATH') !== false
    ? getenv('MW_INSTALL_PATH')."/maintenance/commandLine.inc"
    : dirname( __FILE__ ) . '/../../../maintenance/commandLine.inc' );
require_once("$IP/maintenance/counter.php");
require_once("./extensions/flowchartwiki/lib.php");
require_once("./extensions/flowchartwiki/linktypes.php");

global $wgParser, $wgTitle;
global $smwgEnableUpdateJobs, $wgServer;
$smwgEnableUpdateJobs = false; // do not fork additional update jobs while running this script

$dbr = &wfGetDB(DB_MASTER);
$dbr->query("delete from fchw_relation");
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


