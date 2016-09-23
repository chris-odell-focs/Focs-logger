<?php

namespace Focslo {

    /**
     * Copyright 2016  Foxdell Codesmiths (www.foxdellcodesmiths.com)
     * This program is free software; you can redistribute it and/or modify
     * it under the terms of the GNU General Public License, version 2, as
     * published by the Free Software Foundation.
     * This program is distributed in the hope that it will be useful,
     * but WITHOUT ANY WARRANTY; without even the implied warranty of
     * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     * GNU General Public License for more details.
     * You should have received a copy of the GNU General Public License
     * along with this program; if not, write to the Free Software
     * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
     */

    /**
     * A simple logging layout
     *
     * @version 1.0.0
     */
    class Simple_Layout extends Focs_Layout implements IFocs_Layout {

        public function format( $message, $level, $exception, $date_time ) {

            return date( 'Y-m-d G:i:s', strtotime( $date_time ) )." $level $message";
        }

        public function has_required_params() {

            //no parameters required so always return true
            return true;
        }

    }
}