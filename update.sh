#!/bin/bash
DIRECTORY=$(dirname $0)
cd $DIRECTORY || exit
cd data/COVID-19 || exit
git pull
cd ..
FILENAME=$(date +'%Y-%m-%d')
rm -f $FILENAME