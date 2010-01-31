#!/bin/sh
cd ..
phpdoc -d model,modules -f *.php -t doc/api/
