![SeAT](http://i.imgur.com/aPPOxSK.png)
# UKOC - Mining Taxes

## This repository is based on the SeAT plugin example
//Todo: documentation :)


## Scheduled job development

To run a local version of the scheduled job(s), simply add a "path repository" to your /var/www/seat/composer.json pointing to the repo folder on your machine.

if you never done it before, open the composer.json and add this before the last closing bracket:
*,"repositories": [
{
	"type": "path",
	"url": "**mylocal/repofolder/**"
}]*

When you have updated the composer.json you can install the plugin by running *composer require "ukoc/socialistmining @dev"*, you should get a message that it was "symlinked".

You can then run *php artisan esi:CompressedOrePriceHistory:update* to trigger the job manually.

**Note** that I have experienced issues where i had to restart to get code changes reflected on the site, or even delete the var/www/seat/vendor/ukoc-mining folder and reinstall plugin with *composer install*.

