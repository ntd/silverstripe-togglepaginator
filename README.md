SilverStripe Toggle-Paginator
=============================

A module that provides a _GridField_ component for temporarily switch
the pagination on or off. This can be especially useful when used in
conjunction with other modules, e.g. I am currently using it with
[gridfieldmultiselect](https://github.com/markguinn/silverstripe-gridfieldmultiselect)
to execute special actions to a filtered subset of records.

Installation
------------

You can install _silverstripe-togglepaginator_ by hand by dropping the
directory tree into your SilverStripe root. No flush or dev/build is
required.

If you use [composer](https://getcomposer.org/), execute:

    composer require entidi/silverstripe-togglepaginator dev-master

Usage
-----

Just add the component to the grid field you want disable the pagination
on, e.g.:

    $grid->getConfig()->addComponent(new GridFieldTogglePaginator());

This by default will add a button on the top right corner of every grid
field instance (this can be overriden by providing a different target
fragment while calling the constructor). This button will toggle between
pagination enabled and pagination disabled.

Author
------

This project has been developed by [ntd](mailto:ntd@entidi.it). Its
[home page](http://silverstripe.entidi.com/) is shared by other
[SilverStripe](http://www.silverstripe.org/) modules and themes.

To check out the code, report issues or propose enhancements, go to the
[dedicated tracker](http://dev.entidi.com/p/silverstripe-togglepaginator).
Alternatively, you can do the same things by leveraging the official
[github repository](https://github.com/ntd/silverstripe-togglepaginator).
