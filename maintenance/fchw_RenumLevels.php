<?php
require_once ( getenv('MW_INSTALL_PATH') !== false
    ? getenv('MW_INSTALL_PATH')."/maintenance/commandLine.inc"
    : dirname( __FILE__ ) . '/../../../maintenance/commandLine.inc' );
require_once("$IP/maintenance/counter.php");
require_once("$IP/extensions/flowchartwiki/lib.php");
require_once("$IP/extensions/flowchartwiki/linktypes.php");

global $wgParser, $wgTitle;
global $smwgEnableUpdateJobs, $wgServer;
$smwgEnableUpdateJobs = false; // do not fork additional update jobs while running this script

if ($argc != 4) {
    print("Bad syntax: Use fchw_RenumLevels.php CategoryName StartWithNumber Step\n");
    die();
} else {
    $Category = $argv[0];
    $StartWith = $argv[1];
    $Step = $argv[2];
}

print("Current set: $Category $StartWith $Step\n");

print("Function is under contruction !!");
exit;

// read levels
$dbr =& wfGetDB( DB_SLAVE );
$data = null;
$relations = $dbr->tableName( 'fchw_relation' );
$Categorylinks = $dbr->tableName( 'categorylinks' );
$sql = "SELECT from_id, from_title, relation, to_title as level FROM $relations ".
		          " LEFT OUTER JOIN ${Categorylinks} ON (${Categorylinks}.cl_from = from_id) ".
		          " WHERE $Categorylinks.cl_to = '$Category' and relation='Level' ".
		          " group by from_title, relation, to_title order by to_title, from_title LIMIT 500";
$res = $dbr->query( $sql );
$count = $dbr->numRows( $res );
if ( $count > 0 ) {
    while( $row = $dbr->fetchObject( $res ) ) {
//		    	    $output .= "\"".$row->from_title."\" -- ".$row->from_id." -- ".$row->level."; <br />";
        $data[] = array("id" => $row->from_id, "title" => $row->from_title, "level" => $row->level);
    }
}
$dbr->freeResult( $res );
		    
// data
$LastLevel = -1;
foreach ($data as $Key => $Value) {
    if (($LastLevel != -1) && ($LastLevel != $Value['level']))
        $StartWith += $Step;
	$data[$Key]['newlevel'] = $StartWith;
    	$LastLevel = $Value['level'];
	//$output .= $Value['level']." ==> ".$data[$Key]['newlevel']."<br />";
}
		    
// renumber pages
foreach ($data as $Key => $Value) {
    print($Value['id']." ==> ".$Value['level']." ==> ".$Value['newlevel']."\n");
//        		$revtext = Revision::getRevisionText( $s );
//			$u = new SearchUpdate( $s->page_id, $s->page_title, $revtext );
//			$u->doUpdate();
}


print("Done\n");


