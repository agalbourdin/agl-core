AGL
===

**DEV**

* Website: <http://www.agl.io/>

## Installation

Clone or download this repository in the directory of your choice to install AGL.<br>
One instance of AGL can run an unlimited number of applications.

## Additional modules

Additional modules can be installed *via* Composer.<br>
AGL modules uses a special Composer type : **agl-module**

A module directory will be available on AGL website.

To install a module, simply add a line to the *composer.json* **require** section, for example:

	"agl-encoder": "*"

And then run:

	php composer.phar install

## Create applications

Applications can be created by cloning or downloading **agl-php/app-default** or **agl-php/app-html5** (will be available soon).

To automate this task, you can use the scripts available in [**agl-php/shell**](https://github.com/agl-php/shell "View repository")

## Documentation

AGL is still in development, a documentation will be available soon, it will present you how to use AGL, install, use and create additional modules, and create applications.
