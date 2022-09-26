#!/bin/bash

yum -y install https://mirrors.rpmfusion.org/free/el/rpmfusion-free-release-7.noarch.rpm

yum-config-manager --enable remi

yum -y install ImageMagick7-heic
