#!/bin/sh

#sass --style expanded --watch assets/styles/sass:assets/styles --debug-info
sass --style compressed --watch assets/styles/sass:assets/styles
#sass --style expanded --watch assets/styles/sass:assets/styles --debug-info

exit 0