#!/bin/sh
cd ..
phpdoc -d application,public -dn PHPBiff -dc Application -i library,tests -t doc/api/
