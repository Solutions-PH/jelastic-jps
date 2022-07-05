#!/bin/bash

MB_PATH="/opt/metabase"
MB_VERSION="v0.43.4"

echo "Hello, "$USER".  This script will Configure. Install & run metabase on port localhost:3000"
echo -n "Enter your directory to install metabase e.g,.. '/opt/metabase' and press [ENTER] : "

echo "selected directory will be $MB_PATH"

echo -n "Enter your version of metabase that is needed See https://github.com/metabase/metabase/releases  e.g,.. 'v0.35.0' and press [ENTER]: "

echo "selected version will be $MB_VERSION"

apt-get update -qy
apt-get install curl nginx openjdk-11-jdk -qy

mkdir $MB_PATH

groupadd -r metabase
useradd -r -s /bin/false -g metabase metabase
chown -R metabase:metabase $MB_PATH
touch /var/log/metabase.log
chown metabase:metabase /var/log/metabase.log
touch /etc/default/metabase
chmod 640 /etc/default/metabase

cd $MB_PATH
curl -LO https://downloads.metabase.com/$MB_VERSION/metabase.jar

touch /etc/systemd/system/metabase.service

echo "[Unit]
Description=Metabase server
After=syslog.target
After=network.target

[Service]

Environment=MB_DB_TYPE=mysql
Environment=MB_DB_DBNAME=metabase
Environment=MB_DB_PORT=3306
Environment=MB_DB_USER=jelastic-7095521
Environment=MB_DB_PASS=f41FzKmSOTdZMNkCjdU2
Environment=MB_DB_HOST=10.100.7.16
WorkingDirectory=$MB_PATH
ExecStart=/usr/bin/java -jar $MB_PATH/metabase.jar
EnvironmentFile=/etc/default/metabase
User=metabase
Type=simple
StandardOutput=syslog
StandardError=syslog
SyslogIdentifier=metabase
SuccessExitStatus=143
TimeoutStopSec=120
Restart=always

[Install]
WantedBy=multi-user.target" > /etc/systemd/system/metabase.service

touch /etc/rsyslog.d/metabase.conf

echo "if $programname == 'metabase' then /var/log/metabase.log
& stop" > /etc/rsyslog.d/metabase.conf

systemctl restart rsyslog.service

systemctl daemon-reload

systemctl start metabase.service

systemctl enable metabase.service

cd /etc/nginx/sites-enabled/default && rm default && wget https://raw.githubusercontent.com/Solutions-PH/jelastic-jps/main/ph-metabase/nginx/default.conf