#!/bin/sh

# Installs Mongo adapter for ZF to library/

if [ -d "../../library/Shanty" ]; then
    echo Shanty Mongo is already installed
    exit;
fi

echo Installing Shanty Mongo

tmpDir=/tmp/sm_install/

mkdir -p $tmpDir

smGit=https://github.com/coen-hyde/Shanty-Mongo/archive/master.zip
wget -q $smGit -O $tmpDir"sm.zip"

unzip -q $tmpDir"sm.zip" -d $tmpDir

cp -R "$tmpDir"Shanty-Mongo-master/library/Shanty ../../library/
rm -rf $tmpDir;

echo 'Library contents now'

ls -lsa ../../library