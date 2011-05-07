Zap - A PHP application toolkit
===============================

Zap is a fork of the Swat library from silverorange.  It aims to address a few
architectural issues in Swat that cause poor performance on complex interfaces
and to provide tighter integration with Zend Framework.

Zap allows you to build web application user interfaces with simple reusable 
components.  It handles client-side dependencies, markup generation, messaging
and validation, making many tedious web development tasks more straightforward.


Status and Plans
----------------

The fork is in the very early stages.  The focus right now is on adjusting
the coding style of Swat to reflect the Zend Framework standards and providing
unit tests to provide safety during major refactoring of the library.

Zap will change the way that the SwatMessage and SwatHtmlHeadEntry APIs function
to avoid the slow recursive calls currently needed to track messages attached
to widgets and client-side CSS and JS dependencies.

`Zend_Cache` will be integrated throughout the codebase to allow for more 
cohesive caching of slow tasks like escaping large numbers of flydown entries
or rendering complex data tables.

Once the PHP fork is stable and a broad range of widgets are fully functional,
we will look into porting the current YUI 2 based Javascript that ships with
Swat.


The Initial Proof of Concept
----------------------------

For the initial proof of concept, we're working to convert SwatForm, SwatFrame,
SwatEntry, and their dependencies.


Contributing
------------

Contributions to the project are definitely welcome.  Please provide unit tests
for all files you've ported and check your contributions with the included Zap
`PHP_CodeSniffer` standard.
