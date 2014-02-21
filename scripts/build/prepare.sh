#!/bin/sh

# Installs ZF to library/

cd $(dirname $0);

if [ ! -d "../../storage" ]; then
    echo Creating storage directory
    mkdir ../../storage
    exit;
fi