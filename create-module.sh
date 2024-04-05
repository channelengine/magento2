#!/bin/bash

mkdir -p ./export
# Remove existing zip
rm -f ./export/channelengine-magento2.zip
# Zip all non-hidden files
zip -r ./export/channelengine-magento2.zip . -x ".*"
