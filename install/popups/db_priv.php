<?php

##################################################
#
# Copyright (c) 2004-2011 OIC Group, Inc.
# Written and Designed by James Hunt
#
# This file is part of Exponent
#
# Exponent is free software; you can redistribute
# it and/or modify it under the terms of the GNU
# General Public License as published by the Free
# Software Foundation; either version 2 of the
# License, or (at your option) any later version.
#
# GPL: http://www.gnu.org/licenses/gpl.txt
#
##################################################

?>
<b><?php echo gt('Database User Privileges'); ?></b>
<div class="bodytext">
<?php echo gt('When Exponent connects to the database, it needs to be able to run the following types of queries:'); ?>
<br /><br />

<tt><?php echo gt('CREATE TABLE'); ?></tt><br />
&nbsp;&nbsp;-&nbsp;<?php echo gt('These queries create new table structures inside the database.  Exponent needs this when you install it for the first time.  CREATE TABLE queries are also run after new modules are uploaded to the site.'); ?>
<br /><br />

<tt><?php echo gt('ALTER TABLE'); ?></tt><br />
&nbsp;&nbsp;-&nbsp;<?php echo gt('If you upgrade any module in Exponent, these queries will be run to change table structures in the database.'); ?>
<br /><br />

<tt><?php echo gt('DROP TABLE'); ?></tt><br />
&nbsp;&nbsp;-&nbsp;<?php echo gt('These queries are executed on the database whenever an administrator trims it to remove tables that are no longer used.'); ?>
<br /><br />

<tt><?php echo gt('SELECT'); ?></tt><br />
&nbsp;&nbsp;-&nbsp;<?php echo gt('Queries of this type are very important to the basic operation of Exponent.  All data stored in the database is read back through the use of SELECT queries.'); ?>
<br /><br />

<tt><?php echo gt('INSERT'); ?></tt><br />
&nbsp;&nbsp;-&nbsp;<?php echo gt('Whenever new content is added to the site, new permissions are assigned, users and/or groups are created and configuration data is saved, Exponent runs INSERT queries.'); ?>
<br /><br />

<tt><?php echo gt('UPDATE'); ?></tt><br />
&nbsp;&nbsp;-&nbsp;<?php echo gt('When content or configurations are updated, Exponent modifies the data in its tables by issuing UPDATE queries.'); ?>
<br /><br />

<tt><?php echo gt('DELETE'); ?></tt><br />
&nbsp;&nbsp;-&nbsp;<?php echo gt('These queries remove content and configuration from the tables in the site database.  They are also executed whenever users and groups are removed, and permissions are revoked.'); ?>
</div>