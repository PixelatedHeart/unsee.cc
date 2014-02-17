#!/bin/sh

# Installs ZF to library/

cd $(dirname $0);

if [ -d "../../library/Zend" ]; then
    echo Zend Framework is already installed
    exit;
fi

echo Installing Zend Framework

tmpDir=/tmp/zf_install/

mkdir -p $tmpDir

echo Downloading...
zfGit=https://github.com/zendframework/zf1/archive/master.zip
wget -q $zfGit -O $tmpDir"zf.zip"

echo Unzipping...
unzip -q $tmpDir"zf.zip" -d $tmpDir

echo Installing...
cp -R "$tmpDir"zf1-master/library/Zend ../../library/Zend
rm -rf $tmpDir

echo 'Library contents now is'

ls -ls ../../library