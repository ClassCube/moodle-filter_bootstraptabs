<?php

/*
 * Copyright (C) 2016 Ryan Nutt <ryan@classcube.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */


defined( 'MOODLE_INTERNAL' ) || die();

class filter_bootstraptabs extends \moodle_text_filter {

    /**
     * Counter to keep track of each individual tab set so that this 
     * will work with multiples.
     * 
     * @var int
     */
    private $current_tabset = 0;

    public function filter( $text, array $options = array() ) {
        $text = preg_replace_callback( '/\[(tabs|pills)\]([\s\S]*?)\[\/\1\]/', function($matches) {
            return $this->tab_callback( $matches );
        }, $text );
        return $text;
    }

    private function tab_callback( $matches ) {
        $class_name = "nav-tabs";
        if ( $matches[ 1 ] == 'tabs' ) {
            $class_name = 'nav-tabs';
        }
        else if ( $matches[ 1 ] == 'pills' ) {
            $class_name = 'nav-pills';
        }
        else {
            /* Neither, so just return it back */
            return $matches[ 0 ];
        }

        /* Get the tab titles */
        $tab_info = preg_split( '/\[[tab|pill].*?title=["\']?(.*?)["\']?\s*?\]/', $matches[ 2 ], -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );

        /* Need to clean up any html between the opening [tabs|pills] and the 
         * first tab.
         */
        if ( empty( trim( strip_tags( $tab_info[ 0 ] ) ) ) ) {
            unset( $tab_info[ 0 ] );

            /* Reindex the array so it starts at 0 again */
            $tab_info = array_values( $tab_info );
        }


        $html = '';

        /* First step, build the tab bar  */
        $html .= '<ul class="nav ' . $class_name . '">';


        $divs = '';
        for ( $i = 0; $i < count( $tab_info ); $i += 2 ) {
            /* The first tab needs to be flagged as active to start */
            $html .= '<li role="presentation"' . ($i == 0 ? ' class="active"' : '') . '><a href="#" data-cc-tab="' . $i . '" data-cc-set="' . $this->current_tabset . '">' . $tab_info[ $i ] . '</a></li>';

            $divs .= '<div ' . ($i == 0 ? '' : ' style="display:none;" ') . ' data-cc-set="' . $this->current_tabset . '" data-cc-tab="' . $i . '">';
            $divs .= $tab_info[ $i + 1 ];
            $divs .= '</div>';
        }

        $html .= '</ul>';
        $html .= $divs;

        if ( $this->current_tabset == 0 ) {
            ?>
            <script type="text/javascript">
                document.addEventListener('DOMContentLoaded', function () {
                    require(['jquery'], function ($) {
                        $('a[data-cc-tab]').click(function() {
                            var tab = $(this).data('cc-tab');
                            var set = $(this).data('cc-set');
                            
                            $('a[data-cc-set="' + set + '"]').parents('li').removeClass('active'); 
                            $(this).parents('li').addClass('active'); 
                            
                            $('div[data-cc-set="' + set + '"]').hide();
                            $('div[data-cc-set="' + set + '"][data-cc-tab="' + tab + '"]').show(); 
                            
                            return false; 
                        });
                    });
                }, false);
            </script>
            <?php

        }

        $this->current_tabset++;

        return $html;
    }

}
