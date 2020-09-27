@echo off

set packageDir=%~dp0..
set appDir=%packageDir%/../../..
set phpunit=%appDir%/vendor/phpunit/phpunit/phpunit

cd %packageDir%
php %phpunit%
pause
