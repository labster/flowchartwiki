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

if(!defined( 'MEDIAWIKI' ) )
    die();

$wgExtensionCredits['other'][] = array(
    'name' => 'Process step wizard');

#$wgHooks['CategoryPageView'][] = 'ProcessStepLink';
#$wgHooks['EditFormPreloadText'][] = 'ProcessStepWizard';
#$wgHooks['UnknownAction'][] = 'ProcessStepWizard2';
#$wgHooks['EditPage::showEditForm:initial'][] = 'ProcessStepWizard3';

function ProcessStepLink($catpage) {

    global $wgOut, $wgScript;
    $category = $catpage->mTitle->getText();
    $Action = htmlspecialchars( $wgScript );
    $html = <<<ENDFORM
	<p><a href="$Action?action=NewProcessStep&category=$category">Add new process step</a></p>
    
ENDFORM;
    
//	<p><a href="$Action?title=Sdf&action=edit">Add new process step</a></p>
    $wgOut->addHTML($html);
    return true;
}

function ProcessStepWizard(&$textbox, &$title) {
    $textbox = "TEXT BOX";
    return true;
}

function ProcessStepWizard2($action, $article) {
    if ($action != "NewProcessStep")
	return true;
    
    global $wgOut, $wgScript;    
    $Action = htmlspecialchars( $wgScript );
    $Category = "";
    if (isset($_GET['category'])) 
	$Category = $_GET['category'];
    $ModelType = fchw_GetCategoryModelType($Category);
    $ModelStruc = fchw_GetGraphDefinitions($ModelType);
    
    // get modeltype elements
    $Types = "<select name='pagetype'>";
    foreach($ModelStruc as $key => $value) {
	$Types .= "<option>$key</option><br />";
    }
    $Types .= "</select>";

    // Display form
    $html = <<<ENDFORM
	<p><form name="createbox" action="{$Action}" method="get" class="createbox">
	<table width="40%" cellspacing="0" cellpadding="0" border="0">
	<tr><td width="50%" style="height:24px; border-bottom: 1px dashed">Category name</td><td style="height:24px; border-bottom: 1px dashed">$Category [$ModelType]</td></tr>
	<tr><td style="height:24px; border-bottom: 1px dashed">New page name</td><td style="height:24px; border-bottom: 1px dashed; padding-bottom: 4px; padding-top: 4px;"><input class="createboxInput" name="title" type="text" value="" size="30" onblur="addText(this);"/></td></tr>
	<tr><td style="height:24px; border-bottom: 1px dashed">List type of new page</td><td style="height:24px; border-bottom: 1px dashed">$Types</td></tr>
	<tr><td><input type='hidden' name="action" value="edit"><input type='hidden' name="category" value="$Category"><input type='hidden' name="modeltype" value="$ModelType"></td><td style="padding: 4px;""><input type='submit' name="create" class="createboxButton" value="Prepare new page"/></td></tr>
	</table>
	</form></p>
    
ENDFORM;
    
    $wgOut->setPageTitle("New process step wizard");
    $wgOut->addHTML($html);
    return false;
}

function ProcessStepWizard3($editpage) {
    global $wgOut, $wgScript;    
    $Action = htmlspecialchars( $wgScript );
    $Category = "";
    if (isset($_GET['category'])) 
	$Category = $_GET['category'];
    $ModelType = "";
    if (isset($_GET['modeltype'])) 
	$ModelType = $_GET['modeltype'];
    $PageType = "";
    if (isset($_GET['pagetype'])) 
	$PageType = $_GET['pagetype'];
    if ($Category == "") 
	return true;

    $Load = "";
    
    $TitlePageType = Title::newFromText('Template:'.$PageType);
    if ($TitlePageType->exists()) {
	$Load = 'Template:'.$PageType;
    } else {
        $TitleStep = Title::newFromText('Template:Step');
	if ($TitleStep->exists()) {
    	    $Load = 'Template:Step';
	}	
    }
    
    if ($Load != "") {
	$editpage->textbox1 = fchw_PreloadPage($Load);
	$editpage->textbox2 = $editpage->textbox1;
    }
		
    //{{subst:Event|category=fshdfuidfs|level=123123}}		     
    return true;
}















/*
{
		
	$boxtext  = "Create an Article to this category"; 
	$btext = "Submit";
	global $wgOut;
	global $wgScript;	
	$Action = htmlspecialchars( $wgScript );		
	


//$wgOut->addWikiText( "Test");
$temp2=<<<ENDFORM
<!-- Add Article Extension Start - P by BiGreat-->
<script type="text/javascript">
function clearText(thefield){
if (thefield.defaultValue==thefield.value)
thefield.value = ""
} 
function addText(thefield){
	if (thefield.value=="")
	thefield.value = thefield.defaultValue 
}
</script>
<table border="0" align="right" width="423" cellspacing="0" cellpadding="0">
<tr>
<td width="100%" align="right" bgcolor="">
<form name="createbox" action="{$Action}" method="get" class="createbox">
	<input type='hidden' name="action" value="edit">
	<input type='hidden' name="new" value="1">
	<input type='hidden' name="category" value="{$catpage->mTitle->getText()}">

	<input class="createboxInput" name="title" type="text" value="{$boxtext}" size="30" style="color:#666;" onfocus="clearText(this);" onblur="addText(this);"/>	
	<input type='submit' name="create" class="createboxButton" value="{$btext}"/>	
</form>
</td>
</tr>
</table>
<!-- Add Article Extension End - P by BiGreat-->
ENDFORM;
$wgOut->addHTML($temp2);
	return true;
}

*/
