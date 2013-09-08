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

$wgExtensionFunctions[] = "wfCategoryBrowserExtension";
$wgHooks['ArticleSaveComplete'][] = 'wfCategoryBrowserSaveComplete';
function wfCategoryBrowserSaveComplete(&$article, &$user, $text, $summary,
 $minoredit, $watchthis, $sectionanchor, &$flags, $revision, &$status, $baseRevId) {
    return true;
}

function wfCategoryBrowserExtension() {
    global $wgParser;
    $wgParser->setHook( "CategoryBrowser", "renderCategoryBrowser1" );
    $wgParser->setHook( "CategoryBrowser2", "renderCategoryBrowser2" );
}

// Export {rank=same; "PageName1", "PageName2"} for each level.
function findLevelRanking($FullGraph) {
    global $fchw;
    $output = "";
    $levels = array_keys($fchw['Levels']);
    if ($FullGraph) { $output .= renderCategoryLevel(); }
    foreach($levels as $level) {
        if ($level != 'zzzzzzzz') {
            if ((!$FullGraph) && (!isset($fchw['NearLevels'][$level]))) {
               continue;
            }
            $groupcache = "{ rank = same; ";
            $pagesOnLevel = $fchw['Levels'][$level];
            foreach($pagesOnLevel as $page) {
                $groupcache .= "\"".str_replace("_", " ", fchw_TranslatePageName($page))."\"; ";
            }
            $groupcache .= " }\n";
            $output .= $groupcache;
        }
    }
    if ($FullGraph) { $output .= renderOverflowLevel(); }
    return $output;
}
// if there is a Customizing to include the category as the first item in
// the graph, render it.
function renderCategoryLevel() {
    global $fchw;
    $groupcache = "";
    $pageType="Category";
    if (isset($fchw['GraphDefs']['nodes'][$pageType])) {
        $groupcache = "{ rank = source; ";
        $groupcache .= "\"".$fchw['CurrentCategory']."\"; ";
        $groupcache .= " }\n";
    }
    return $groupcache;
}
// Render all Pages with "{rank=same; "PageX"} plus indivdual Page Info
// for all Pages that have no [[Level::1234]] Tag assigned.
// Multiple Parts:
// 1: Render an invisible "zzzzzzzz" Entry underneath the normal Graph.
// 2: Render the remaining Pages in Rows of $fchw['zLevels'] each.
// 3: Add invisible Links from "last" Row of Graph to "zzzzzzzz" Entry
// 4: Add invisible Links inbetween the remaining Pages
// 5: Add an invisible "zzzzzzzza" Entry underneath and invisible links
//    from the last row of remaining Pages to it.
function renderOverflowLevel() {
    //{ rank = same; "Ende"; } "Ende" [shape=circle,width=.01,height=.01,color=white, label=""]
    global $fchw;
    $params = "style=invis,";
    $groupcache = "";
    if (isset($fchw['Levels']['zzzzzzzz'])) {
        $groupcache .= "/* Overflow */\n";
        $groupcache .= "{ rank = same; \"zzzzzzzz\"; } \"zzzzzzzz\" [shape=circle,width=.01,height=.01,color=white, label=\" \"]\n";
        $overflowPages = $fchw['Levels']['zzzzzzzz'];
        sort($overflowPages);  // have the pages sorted alphabetically
        $numPages = count($overflowPages);
        // distribute those pages into multiple Rows
        // $fchw['zLevels'] = 4 is default and set in flowchartwiki.php,
        // if not configured in LocalSettings.php
        $i = 0;
        $row = 0;
        $overRows = array();
        $overRows[$row]=array();
        while ($i < $numPages) {
            if (count($overRows[$row]) > $fchw['zLevels'] - 1) {
                $row += 1;
                $overRows[$row]=array();
            }
            $overRows[$row][]=$overflowPages[$i];
            $i += 1;
        }
        // Add these rows to the output
        $groupcache .= "/* Overflow Rows */\n";
        $i=0;
        while ($i <= $row) {
            $groupcache .= "{ rank = same; ";
            foreach($overRows[$i] as $page) {
                $groupcache .= "\"".str_replace("_", " ", fchw_TranslatePageName($page))."\"; ";
            }
            $groupcache .= " }\n";
            $i += 1;
        }
        // Add (invisible) links
        // from last row to invisible 'zzzzzzzz'
        $groupcache .= "/* Links from Last row to invisible 'zzzzzzzz' */\n";
        $levelCount = count($fchw['Levels']);
        $levelkeys = array_keys($fchw['Levels']);
        if ($levelCount > 1) { // we do not just have 0:'zzzzzzzz'
            $lastGraphLevel = $fchw['Levels'][$levelkeys[$levelCount - 2]];
            foreach($lastGraphLevel as $page) {
                $groupcache .= "\"".str_replace("_", " ",fchw_TranslatePageName($page))."\"->\"zzzzzzzz\" [ $params ];\n";
            }
        }
        // from invisible 'zzzzzzzz' to the pages in the first row.
        $groupcache .= "/* Links from invisible 'zzzzzzzz' to first overflow-row */\n";
        foreach($overRows[0] as $page) {
            $groupcache .= "\"zzzzzzzz\"->\"".str_replace("_", " ",fchw_TranslatePageName($page))."\" [ $params ];\n";
        }
        // invisible links inside Overflow
        $groupcache .= "/* Invisible Links inside Overflow */\n";
        $i=1;
        while ($i <= $row) {
            $j = 0;
            foreach($overRows[$i] as $page) {
                $groupcache .= "\"".str_replace("_", " ",fchw_TranslatePageName($overRows[$i-1][$j]))."\"->\"".str_replace("_", " ",fchw_TranslatePageName($page))."\" [ $params ];\n";
                $j +=1;
            }
            $i += 1;
        }
        $groupcache .= "/* Sink */\n";
        $groupcache .= "{ rank = sink; \"zzzzzzzza\"; } \"zzzzzzzza\" [shape=circle,width=.01,height=.01,color=white, label=\" \"]\n";
        foreach($overRows[$row] as $page) {
            $groupcache .= "\"".str_replace("_", " ",fchw_TranslatePageName($page))."\"->\"zzzzzzzza\" [ $params ];\n";
        }

        $groupcache .= "/* DONE overflow */\n";
    }
    return $groupcache;
}

// Render all the individual Pages (Page Data, Shape,Color)
// of the "Non-Levell-ZZZZZZ" Pages.
function findPages($FullGraph) {
    global $fchw, $wgScriptPath, $wgScriptExtension;
    $output = "";
    $path = $wgScriptPath."/index".$wgScriptExtension;
    $pages = $fchw['Pages'];
    if ($FullGraph) { $output .= renderCategoryPage(); }
    foreach($pages as $page) {
        if ((!$FullGraph) && (!isset($fchw['NearLevels'][$page->level]))) {
            continue;
        }
        $params = "";
        $params .= "URL=\"".(( isset($fchw['Categories'][$page->pageName])) ? "$path/Category:" : "").str_replace("_", " ",$page->pageName)."\",";
        $color  = "";
        if (isset($fchw['GraphDefs']['nodes'][$page->pageType])) {
           $params .= "shape=".$fchw['GraphDefs']['nodes'][$page->pageType]['Shape'].",";
           $color .= "color=".$fchw['GraphDefs']['nodes'][$page->pageType]['BackColor'].", fontcolor=".$fchw['GraphDefs']['nodes'][$page->pageType]['FontColor'].", style=filled,";
        }
        // SELECT CURRENT PAGE
        if (str_replace("_", " ", $page->pageName) == $fchw['CurrentPage2'])
           $color = "color=black, fontcolor=white, style=filled, ";
        $params .= $color;
        $output .= "\"".str_replace("_", " ", $page->getTranslatedName())."\" [".$params."];\n";
    }
    return $output;
}

// Do the Page-Rendering for the Category-Page.
function renderCategoryPage() {
    global $fchw, $wgScriptPath, $wgScriptExtension;
    $path = $wgScriptPath."/index".$wgScriptExtension;
    $output = "";
    $pageType="Category";
    if (isset($fchw['GraphDefs']['nodes'][$pageType])) {
        $params = "";
        $params .= "URL=\"".(( isset($fchw['Categories'][$fchw['CurrentCategory']])) ? "$path/Category:" : "").str_replace("_", " ",$fchw['CurrentCategory'])."\",";
        $color  = "";
        if (isset($fchw['GraphDefs']['nodes'][$pageType])) {
           $params .= "shape=".$fchw['GraphDefs']['nodes'][$pageType]['Shape'].",";
           $color .= "color=".$fchw['GraphDefs']['nodes'][$pageType]['BackColor'].", fontcolor=".$fchw['GraphDefs']['nodes'][$pageType]['FontColor'].", style=filled,";
        }
        // SELECT CURRENT PAGE
        if (str_replace("_", " ", $fchw['CurrentCategory']) == $fchw['CurrentPage2'])
           $color = "color=black, fontcolor=white, style=filled, ";
        $params .= $color;
        $output .= "\"".str_replace("_", " ", $fchw['CurrentCategory'])."\" [".$params."];\n";
    }
    return $output;
}

// Render all the Links inside the Graph
function findLinks($FullGraph) {
    global $fchw;
    $output = "";
    if ($FullGraph) { $output .= renderCategoryLinks(); }
    $pages = $fchw['Pages'];
    foreach($pages as $page) {
        $links = $page->links;
        if (isset($links)) {  // do we have some links to other pages on this page?
            foreach($links as $link) {
                if (!$FullGraph) {
                  if (((!isset($fchw['NearLevels'][$pages[$link->linkFrom]->level]))) ||
                      ((!isset($fchw['NearLevels'][$pages[$link->linkTo]->level]))))
                  continue;
                }
                $params = "";
                if (isset($fchw['GraphDefs']['arrows'][$link->linkType])) {
                   $params .= "color=\"".$fchw['GraphDefs']['arrows'][$link->linkType]['Color']."\", arrowhead=".$fchw['GraphDefs']['arrows'][$link->linkType]['Shape'].", style=\"".$fchw['GraphDefs']['arrows'][$link->linkType]['Style']."\", label=\"".$fchw['GraphDefs']['arrows'][$link->linkType]['Label']."\"";
                }
                $output .= "\"".str_replace("_", " ",fchw_TranslatePageName($link->linkFrom))."\"->\"".str_replace("_", " ",fchw_TranslatePageName($link->linkTo))."\" [ $params ];\n";
            }
        }
    }
    return $output;
}
// Render the (invisible) Links from the Category-Page to the pages on the
// first Level.
function renderCategoryLinks() {
    global $fchw;
    $pages = $fchw['Pages'];
    $output = "";
    $pageType="Category";
    if (isset($fchw['GraphDefs']['nodes'][$pageType])) {
        $params = "style=invis,";
        $levels = array_keys($fchw['Levels']);
        $pagesOnLevel = $fchw['Levels'][$levels[0]]; // we invisibly link to the pages on the first level.
        if (isset($pagesOnLevel)) {
            foreach($pagesOnLevel as $page) {
                $output .= "\"".str_replace("_", " ",$fchw['CurrentCategory'])."\"->\"".str_replace("_", " ",$pages[$page]->getTranslatedName())."\" [ $params ];\n";
            }
        }
    }
    return $output;
}

// Tag: <CategoryBrowser /> or <CategoryBrowser>CategoryName</CategoryBrowser>
// CategoryBrowser renders the whole graph.
function renderCategoryBrowser1($input, $params, $parser) {
    return renderCategoryBrowser($input, $params, $parser, 1);
}

// Tag: <CategoryBrowser2 /> or <CategoryBrowser2>CategoryName</CategoryBrowser2>
// CategoryBrowser2 renders two images,
// 1: The whole graph
// 2: Just the 2 rows above and below the current row.
function renderCategoryBrowser2($input, $params, $parser) {
    return renderCategoryBrowser($input, $params, $parser, 2);
}
// Render the Image, calls most of the functions above.
function renderCategoryBrowser($input, $params, $parser, $Mode) {
    global $fchw, $wgTitle, $wgUploadDirectory, $wgParser;
    $html = "";
    $fchw['Categories'] = fchw_GetCategories();
    if (is_object($wgTitle))
        $myTitle = $wgTitle->mPrefixedText;
      else
        $myTitle = $wgTitle;
    $fchw['CurrentCategory'] = fchw_GetCurrentCategory($myTitle);
    $GraphFileName = $myTitle;
    if ($wgTitle->mNamespace == NS_CATEGORY) {
       $fchw['CurrentCategory'] = substr($GraphFileName, strpos($GraphFileName, ":")+1);
    }
    if ($input) {
        $fchw['CurrentCategory'] = $input;
        $GraphFileName = $myTitle."_".$input;
    }
    $fchw['CurrentPage']        = $wgParser->getTitle()->mTextform;
    $fchw['CurrentPage2']    = $fchw['CurrentPage'];
    if (!(strpos($fchw['CurrentPage'], ":") === FALSE)) {
       $fchw['CurrentPage2'] = substr($fchw['CurrentPage'], strpos($fchw['CurrentPage'], ":")+1);
    }
    if ($fchw['CurrentPage'] != $myTitle) {
       $GraphFileName .= "_".$fchw['CurrentPage'];
    }
    $fchw['CurrentCategory'] = str_replace(" ","_",$fchw['CurrentCategory']);
    $fchw['Pages']       = fchw_LoadPages(); // of fchw['currentCategory']
    $fchw['CurrentLevel']     = fchw_GetCurrentLevel();
    $fchw['Levels']         = fchw_GetLevels();
    $fchw['NearLevels']     = fchw_GetNearLevels(fchw_GetLevels(false), $fchw['CurrentLevel']);
    $fchw['GraphDefs']         = fchw_GetGraphDefinitions(fchw_GetCategoryModelType($fchw['CurrentCategory']));
    // Calculate Height of Graph: How many "Rows"="Levels" do we have?
    $GraphHeight = 0;
    // Will the Category be shown?
    if (isset($fchw['GraphDefs']['nodes']["Category"])) {
        $GraphHeight += 1;
    }
    // Do we have Pages without a "[[Level::1000]]" assigned?
    if (isset($fchw['Levels']['zzzzzzzz'])) {
        $GraphHeight += intval(count($fchw['Levels']['zzzzzzzz']) / $fchw['zLevels']) + 1 ;
        $GraphHeight += count($fchw['Levels']) - 1 ;
    } else {
        $GraphHeight += count($fchw['Levels']) ;
    }
    //$GraphHeight *= 1.5;

    if (($fchw['CurrentLevel'] == "") || ($Mode == 1)) {
        $output  = "digraph G { size =\"7,$GraphHeight\"; concentrate=true; ".findLevelRanking(true).findPages(true).findLinks(true)."}";
        //$output  = "digraph G { concentrate=true; ".findLevelRanking(true).findPages(true).findLinks(true)."}";
        $html    .= Graphviz($GraphFileName, $output);
        //$html    .= "<pre>$output</pre>";
    } else {
        $html    .= "<table width='100%' border='0' cellpadding='0' cellspacing='0'><tr><td width='10' style='padding-right: 16px' valign='top'>";
        $output  = "digraph G { size =\"3,$GraphHeight\"; concentrate=true; ".findLevelRanking(true).findPages(true).findLinks(true)."}";
        $html    .= Graphviz($GraphFileName."_process", $output);
        //$html    .= "<pre>$output</pre>";
        $html    .= "</td><td valign='top'>";
        //$output  = "digraph G { size =\"5,$GraphHeight\"; concentrate=true; ".findLevelRanking(false).findPages(false).findLinks(false)."}";
        $output  = "digraph G { size =\"5,6\"; concentrate=true; ".findLevelRanking(false).findPages(false).findLinks(false)."}";
        $html    .= Graphviz($GraphFileName, $output);
        //$html    .= "<pre>$output</pre>";
        $html    .= "</td></tr></table>";
    }
    return $html;
    //return $output;
}

