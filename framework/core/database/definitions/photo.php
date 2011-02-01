<?php

##################################################
#
# Copyright (c) 2004-2006 OIC Group, Inc.
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

if (!defined('EXPONENT')) exit('');

return array(
    'id'=>array(
        DB_FIELD_TYPE=>DB_DEF_ID,
        DB_PRIMARY=>true,
        DB_INCREMENT=>true),
    'title'=>array(
        DB_FIELD_TYPE=>DB_DEF_STRING,
        DB_FIELD_LEN=>200),
    'link'=>array(
        DB_FIELD_TYPE=>DB_DEF_STRING,
        DB_FIELD_LEN=>200),
    'sef_url'=>array(
        DB_FIELD_TYPE=>DB_DEF_STRING,
        DB_FIELD_LEN=>200),
    'meta_title'=>array(
        DB_FIELD_TYPE=>DB_DEF_STRING,
        DB_FIELD_LEN=>255),
    'meta_keywords'=>array(
        DB_FIELD_TYPE=>DB_DEF_STRING,
        DB_FIELD_LEN=>10000),
    'meta_description'=>array(
        DB_FIELD_TYPE=>DB_DEF_STRING,
        DB_FIELD_LEN=>10000),
    'body'=>array(
        DB_FIELD_TYPE=>DB_DEF_STRING,
        DB_FIELD_LEN=>100000),
    'alt'=>array(
        DB_FIELD_TYPE=>DB_DEF_STRING,
        DB_FIELD_LEN=>120),
    'poster'=>array(
        DB_FIELD_TYPE=>DB_DEF_INTEGER),
    'rank'=>array(
        DB_FIELD_TYPE=>DB_DEF_INTEGER),
    'featured'=>array(
        DB_FIELD_TYPE=>DB_DEF_INTEGER),
    'created_at'=>array(
        DB_FIELD_TYPE=>DB_DEF_TIMESTAMP),
    'edited_at'=>array(
        DB_FIELD_TYPE=>DB_DEF_TIMESTAMP),
    'location_data'=>array(
        DB_FIELD_TYPE=>DB_DEF_STRING,
        DB_FIELD_LEN=>250,
        DB_INDEX=>10)
);

?>