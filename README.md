zM Upload is a class used to ease the implemention of handling file
uploads in WordPress.

Demo
=
[Video Demo](http://www.youtube.com/watch?v=Jpz21O3mPJE&feature=plcp)
[Integrated with Uploadify](http://bmxraceevents.com/events/new)

Usage
=

<pre>
// Some where in php land
include_once( 'zm-upload/MediaUpload.php' );

if ( $_POST ) {
    $tmp = new MediaUpload;
    $attachment_id = $tmp->saveUpload( $field_name='my_file' );
}
</pre>


Enjoy.

Zane M. Kolnik
June, 2012
http://zanematthew.com/

License:

  Copyright 2012 Zane M. Kolnik

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA