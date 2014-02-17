#!/bin/sh

# Installs Mongo adapter for php

echo Installing Mongo adapter for Php

if [[ $(php -m) == *mongo* ]]
then
    echo Php already has Mongo driver
    #exit;
fi  

tmpDir="/tmp/php_mongo/"
mkdir -p $tmpDir

cd $tmpDir/

pmGit="https://github.com/mongodb/mongo-php-driver/archive/master.zip"
wget $pmGit
unzip master.zip
cd mongo-php-driver-master/

phpize
./configure
make
make install

echo "extension=mongo.so" > /etc/php/conf.d/mongo.ini

rm -rf $tmpDir