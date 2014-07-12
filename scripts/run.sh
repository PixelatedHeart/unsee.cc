#!/bin/sh

cd $(dirname $0)/../;
git fetch && git reset --hard origin/master;
rm application/configs/env.php;
killall node;
nohup node scripts/chat.js > scripts/output.log &