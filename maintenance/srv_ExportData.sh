#!/bin/sh

../../../php5/bin/php ./maintenance/dumpBackup.php --current --pagelist=./extensions/fchw/maintenance/pagelist_Customizing.txt > ./extensions/fchw/maintenance/import_Customizing.xml
../../../php5/bin/php ./maintenance/dumpBackup.php --current --pagelist=./extensions/fchw/maintenance/pagelist_ProcessList.txt > ./extensions/fchw/maintenance/import_ProcessList.xml
../../../php5/bin/php ./maintenance/dumpBackup.php --current --pagelist=./extensions/fchw/maintenance/pagelist_Flightbooking.txt > ./extensions/fchw/maintenance/import_Flightbooking.xml
../../../php5/bin/php ./maintenance/dumpBackup.php --current --pagelist=./extensions/fchw/maintenance/pagelist_GettingStarted.txt > ./extensions/fchw/maintenance/import_GettingStarted.xml
../../../php5/bin/php ./maintenance/dumpBackup.php --current --pagelist=./extensions/fchw/maintenance/pagelist_ValueStream.txt > ./extensions/fchw/maintenance/import_ValueStream.xml
../../../php5/bin/php ./maintenance/dumpBackup.php --current --pagelist=./extensions/fchw/maintenance/pagelist_ShapeTest.txt > ./extensions/fchw/maintenance/import_ShapeTest.xml

