#!/bin/bash

proj=$1 #Project name, use upper case for the first letter
dbhost=$2 #Database hostname. In case of database cluster use layer name sqld to ensure HA DNS RR failover. More details at: https://docs.jelastic.com/container-dns-hostnames#layer-hostnames
usr=$3 #Database entry point username
pswd=$4 #Database entry point password

cat << EOF > /var/www/webroot/ROOT/index.php
<?php
echo '$proj';
phpinfo();
?>
EOF

echo
echo -e "Open in browser \\033[1;32m\033[1mhttp://${HOSTNAME}/$proj/public\033[0m\\033[0;39m "
echo


