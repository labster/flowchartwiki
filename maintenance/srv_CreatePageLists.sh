#!/bin/sh

../../../php5/bin/php ./extensions/fchw/maintenance/fchw_CreatePagelistFromCategory.php ProcessList > ./extensions/fchw/maintenance/pagelist_ProcessList.txt
../../../php5/bin/php ./extensions/fchw/maintenance/fchw_CreatePagelistFromCategory.php Flightbooking > ./extensions/fchw/maintenance/pagelist_Flightbooking.txt
../../../php5/bin/php ./extensions/fchw/maintenance/fchw_CreatePagelistFromCategory.php GettingStarted > ./extensions/fchw/maintenance/pagelist_GettingStarted.txt
../../../php5/bin/php ./extensions/fchw/maintenance/fchw_CreatePagelistFromCategory.php ValueStream > ./extensions/fchw/maintenance/pagelist_ValueStream.txt
../../../php5/bin/php ./extensions/fchw/maintenance/fchw_CreatePagelistFromCategory.php ShapeTest > ./extensions/fchw/maintenance/pagelist_ShapeTest.txt
