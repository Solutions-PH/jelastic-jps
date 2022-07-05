#!/bin/bash

MB_PATH_DEFAULT="/opt/metabase"
MB_VERSION_DEFAULT="v0.43.4"

echo "Hello, "$USER".  This script will Configure. Install & run metabase on port localhost:3000"
echo -n "Enter your directory to install metabase e.g,.. '/opt/metabase' and press [ENTER] : "

MB_PATH="/opt/metabase"
echo "selected directory will be $MB_PATH"

echo -n "Enter your version of metabase that is needed See https://github.com/metabase/metabase/releases  e.g,.. 'v0.35.0' and press [ENTER]: "
read $MB_VERSION\n
MB_VERSION="${MB_VERSION:-$MB_VERSION_DEFAULT}"
echo "selected version will be $MB_VERSION"

apt-get update -qy
apt-get install curl openjdk-11-jdk -qy

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