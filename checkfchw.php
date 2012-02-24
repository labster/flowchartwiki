<?php
# Alert the user that this is not a valid entry point to MediaWiki if they try to access the special pages file directly.
if (!defined('MEDIAWIKI')) {
        echo <<<EOT
Extension is not installed
EOT;
        exit( 1 );
}
 
$dir = dirname(__FILE__) . '/';

$wgAutoloadClasses['CheckFchw'] = $dir . 'checkfchw_body.php'; # Tell MediaWiki to load the extension body.
$wgExtensionMessagesFiles['checkfchw'] = $dir . 'checkfchw.i18n.php';
$wgSpecialPages['CheckFchw'] = 'CheckFchw'; # Let MediaWiki know about your new special page.
$wgHooks['LanguageGetSpecialPageAliases'][] = 'checkfchwLocalizedPageName'; # Add any aliases for the special page.
 
function checkfchwLocalizedPageName(&$specialPageArray, $code) {
  # The localized title of the special page is among the messages of the extension:
  wfLoadExtensionMessages('checkfchw');
  $text = wfMsg('checkfchw');
 
  # Convert from title in text form to DBKey and put it into the alias array:
  $title = Title::newFromText($text); 
  return true;
}


