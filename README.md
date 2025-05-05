Contactlist
==========================

This file is part of the local_contactlist plugin for Moodle - <http://moodle.org/>

*Author:* Angela Baier, Thomas Wedekind

*Copyright:* 2020 [University of Vienna](https://www.univie.ac.at/)

*License:* [GNU GPL v3 or later](http://www.gnu.org/copyleft/gpl.html)


Description
-----------

The contactlist plugin allows students to decide if they want to share their contact information
with their fellow students in accordance with the GDPR for each course they are registered in.

* The default setting:

  Students have the option to set their contactlist visibility for the whole platform in their 
  profile settings page. The default visibility setting is "no", so students have to consciously
  opt-in if they want to share their contact information with their fellow students. 

* Course level setting:

  In order to provide maximum flexibility, students also have the option to set their contact information
  visibility in each course individually, overwriting the default setting for the respective course.


Usage
-----

If the list of participants on the platform is only visible to teachers for data protection reasons,
students have no easy way to contact other participants of the course.

Therefore the teacher has activated the contactlist in the course so that students e.g. can get in contact with each other
for work on issues in groups or for exchange.
Each participant - students as well as teachers - has now the option to decide for themselves whether
to appear in the contactlist for this course or not.


Installation
------------

* Copy the module code directly to the *moodleroot/local/contactlist* directory.

* Log into Moodle as administrator.

* Open the administration area (*http://your-moodle-site/admin*) to start the installation
  automatically.


Privacy API
-----------

The plugin fully implements the Moodle Privacy API.


Bug Reports / Support
---------------------

We try our best to deliver bug-free plugins, but we can not test the plugin for every platform,
database, PHP and Moodle version. If you find any bug please report it on
[GitHub](https://github.com/elearning-univie/moodle-local_contactlist/issues/). Please
provide a detailed bug description, including the plugin and Moodle version and, if applicable, a
screenshot.

You may also file a request for enhancement on GitHub. If we consider the request generally useful
and if it can be implemented with reasonable effort we might implement it in a future version.

You may also post general questions on the plugin on GitHub, but note that we do not have the
resources to provide detailed support.


License
-------

This plugin is free software: you can redistribute it and/or modify it under the terms of the GNU
General Public License as published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

The plugin is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
General Public License for more details.

You should have received a copy of the GNU General Public License with Moodle. If not, see
<http://www.gnu.org/licenses/>.


Good luck and have fun!
