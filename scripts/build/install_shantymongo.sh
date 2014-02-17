#!/bin/sh

# Installs Mongo adapter for ZF to library/

cd $(dirname $0);

if [ -d "../../library/Shanty" ]; then
    echo Shanty Mongo is already installed
    exit;
fi

echo Installing Shanty Mongo

tmpDir=/tmp/sm_install/

mkdir -p $tmpDir

echo Downloading...
smGit=https://github.com/coen-hyde/Shanty-Mongo/archive/master.zip
wget -q $smGit -O $tmpDir"sm.zip"

echo Unzipping...
unzip -q $tmpDir"sm.zip" -d $tmpDir

echo Installing...
cp -R "$tmpDir"Shanty-Mongo-master/library/Shanty ../../library/Shanty
rm -rf $tmpDir;

echo 'Library contents now'

ls -ls ../../library