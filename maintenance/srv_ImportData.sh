#!/bin/sh
../../../php5/bin/php ./maintenance/importDump.php < ./extensions/fchw/maintenance/import_Customizing.xml
../../../php5/bin/php ./maintenance/importDump.php < ./extensions/fchw/maintenance/import_ProcessList.xml
../../../php5/bin/php ./maintenance/importDump.php < ./extensions/fchw/maintenance/import_Flightbooking.xml
../../../php5/bin/php ./maintenance/importDump.php < ./extensions/fchw/maintenance/import_GettingStarted.xml
../../../php5/bin/php ./maintenance/importDump.php < ./extensions/fchw/maintenance/import_ValueStream.xml
../../../php5/bin/php ./maintenance/importDump.php < ./extensions/fchw/maintenance/import_ShapeTest.xml
../../../php5/bin/php ./maintenance/rebuildrecentchanges.php
