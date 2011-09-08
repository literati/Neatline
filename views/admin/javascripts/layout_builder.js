/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/*
 * JavaScript for the layout builder application in the add view.
 *
 * PHP version 5
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at http://www.apache.org/licenses/LICENSE-2.0 Unless required by
 * applicable law or agreed to in writing, software distributed under the
 * License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS
 * OF ANY KIND, either express or implied. See the License for the specific
 * language governing permissions and limitations under the License.
 *
 * @package     omeka
 * @subpackage  neatline
 * @author      Scholars' Lab <>
 * @author      Bethany Nowviskie <bethany@virginia.edu>
 * @author      Adam Soroka <ajs6f@virginia.edu>
 * @author      David McClure <david.mcclure@virginia.edu>
 * @copyright   2011 The Board and Visitors of the University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache 2 License
 */

(function($, undefined) {

    $.widget('neatline.layoutbuilder', {

        options: {
            dragbox_id: 'drag-box',
            options_id: 'options',
            top_block_percentage: 60,
            undated_items_width: 150
        },

        _create: function() {

            // Getters for options and dragbox divs.
            this.buttons = $('#' + this.options.options_id);
            this.dragbox = $('#' + this.options.dragbox_id);

            // Get fixed pixel values for heights.
            this._getPxConstants();

            // Create draggers.
            this._createDraggers();

            // Squash text selection and gloss the buttons.
            this._disableSelect();
            this._createButtons();

            // Set tracker variables for element presence.
            this._is_map = false;
            this._is_timeline = false;
            this._is_undated_items = false;

            // Set tracker variables for element position.
            this._top_element = 'map'; // 'map' or 'timeline'
            this._undated_items_position = 'right'; // 'right' or 'left'
            this._undated_items_height = 'partial'; // 'partial' or 'full'

            // By default, add all elements.
            this._toggleMap();
            this._toggleTimeline();
            this._toggleUndatedItems();

            // Gloss.
            this._addDragEvents();

        },

        _getPxConstants: function() {

            this._dragbox_height = this.dragbox.height();

            this._dragbox_width = this.dragbox.width();

            this._top_block_height = this._dragbox_height *
                (this.options.top_block_percentage/100);

            this._bottom_block_height = this._dragbox_height -
                this._top_block_height;

            this._undated_items_left_offset = this._dragbox_width -
                this.options.undated_items_width;

        },

        _disableSelect: function() {

            // Turn off text selection on the whole container div.
            this.element.css('MozUserSelect', 'none');
            this.element.bind('selectstart mousedown', function() {
                return false;
            });

            // Fix the pointer style in the drag box.
            this.dragbox.css('cursor', 'default');

        },

        _createButtons: function() {

            // Instantiate buttons, define callbacks.

            $('#toggle-map').togglebutton({
                pressed_by_default: true,
                press: $.proxy(this._toggleMap, this),
                unpress: $.proxy(this._toggleMap, this)
            });

            $('#toggle-timeline').togglebutton({
                pressed_by_default: true,
                press: function() { },
                unpress: function() { }
            });

            $('#toggle-undated-items').togglebutton({
                pressed_by_default: true,
                press: function() { },
                unpress: function() { }
            });

        },

        _createDraggers: function() {

            // Create the map.
            this.map_drag = this.__createMapDiv();

            // Create the timeline.
            this.timeline_drag = this.__createTimelineDiv();

            // Create the undated items.
            this.undated_items_drag = this.__createUndatedItemsDiv();

            // Inject.
            this.dragbox.append(
                this.map_drag,
                this.timeline_drag,
                this.undated_items_drag);

        },

        _addDragEvents: function() {



        },

        _repositionDraggers: function() {

            // Map.
            this.map_drag.css({
                'height': this.__getMapHeight(),
                'width': this.__getMapWidth(),
                'top': this.__getMapTopOffset(),
                'left': this.__getMapLeftOffset()
            });

            // Timeline.
            this.timeline_drag.css({
                'height': this.__getTimelineHeight(),
                'width': this.__getTimelineWidth(),
                'top': this.__getTimelineTopOffset(),
                'left': this.__getTimelineLeftOffset()
            });

            // Undated items.
            this.undated_items_drag.css({
                'height': this.__getUndatedItemsHeight(),
                'width': this.__getUndatedItemsWidth(),
                'top': this.__getUndatedItemsTopOffset(),
                'left': this.__getUndatedItemsLeftOffset()
            });

            // Center tags.
            this._centerAllTags();

        },

        _centerAllTags: function() {

            this._position_tag(this.map_drag);
            this._position_tag(this.timeline_drag);
            this._position_tag(this.undated_items_drag);

        },

        _toggleMap: function() {

            switch(this._is_map) {

                case true:

                    this._is_map = false;

                    // Display none the map.
                    this.map_drag.css('display', 'none');

                break;

                case false:

                    this._is_map = true;

                    // Show the div and reposition tag.
                    this.map_drag.css('display', 'block');

                break;

            }

            // Recalculate all positions for all divs.
            this._repositionDraggers();

        },

        _toggleTimeline: function() {

            switch(this._is_timeline) {

                case true:
                    // remove.
                    this._is_timeline = false;
                break;

                case false:

                    this._is_timeline = true;

                    // If the map is present..
                    if (this._is_map) {

                        // If the map is on top..
                        if (this._top_element == 'map') {

                            // Shrink the height of the map and reposition tag.
                            this.map_drag.css('height', this._top_block_height + 'px');
                            this._position_tag(this.map_drag);

                            // Set css.
                            this.timeline_drag.css({
                                'height': this._bottom_block_height + 'px',
                                'width': this._dragbox_width + 'px',
                                'top': this._top_block_height + 'px'
                            });

                        }

                        // If the timeline is on top..
                        else {

                            // Shrink the height of the map, adjust top offset.
                            var new_map_top_offset = this.__getMapTopOffset();
                            var new_timeline_top_offset = this.__getTimelineTopOffset();

                            // Set css.
                            this.map_drag.css({
                                'height': this._bottom_block_height + 'px',
                                'top': new_map_top_offset + 'px'
                            });

                            this.timeline_drag.css({
                                'height': this._top_block_height + 'px',
                                'width': this._dragbox_width + 'px',
                                'top': '0px'
                            });

                            // Reposition tags.
                            this._position_tag(this.map_drag);
                            this._position_tag(this.timeline_drag);

                        }

                        // Show div and position tag.
                        this.timeline_drag.css('display', 'block');
                        this._position_tag(this.timeline_drag);

                    }

                    // if no map
                    else {

                        this.timeline_drag.css('height', '100%');

                        // Show div and position tag.
                        this.timeline_drag.css('display', 'block');
                        this._position_tag(this.timeline_drag);

                    }

                break;

            }

        },

        _toggleUndatedItems: function() {

            switch(this._is_undated_items) {

                case true:
                    // remove.
                    this._is_undated_items = false;
                break;

                case false:

                    this._is_undated_items = true;

                    // Calculate and set height.
                    var height = this.__getUndatedItemsHeight();
                    this.undated_items_drag.css('height', height + 'px');

                    // Set width property.
                    this.undated_items_drag.css('width', this.options.undated_items_width + 'px');

                    // Calculate and set left offset.
                    var left_offset = this.__getUndatedItemsLeftOffset();
                    this.undated_items_drag.css('left', left_offset + 'px');

                    // Calculate and set top offset.
                    var top_offset = this.__getUndatedItemsTopOffset();
                    this.undated_items_drag.css('top', top_offset + 'px');

                    // Append and reposition label.
                    this.dragbox.append(this.undated_items_drag);
                    this._position_tag(this.undated_items_drag);

                    // Squeeze the map and timeline divs as necessary.
                    var new_map_width = this.__getMapWidth();
                    var new_timeline_width = this.__getTimelineWidth();

                    this.map_drag.css('width', new_map_width + 'px');
                    this.timeline_drag.css('width', new_timeline_width + 'px');

                    // Adjust left offsets for map and timeline divs as necessary.
                    var new_map_offset = this.__getMapLeftOffset();
                    var new_timeline_offset = this.__getTimelineLeftOffset();

                    this.map_drag.css('left', new_map_offset + 'px');
                    this.timeline_drag.css('left', new_timeline_offset + 'px');

                    // Show the div.
                    this.undated_items_drag.css('display', 'block');

                    // Reposition tags.
                    this._position_tag(this.map_drag);
                    this._position_tag(this.timeline_drag);
                    this._position_tag(this.undated_items_drag);

                break;

            }

        },

        _position_tag: function(draggable) {

            var tag = draggable.find('.drag-tag');
            var draggable_height = draggable.height();
            var tag_height = tag.height();

            tag.css('top', (draggable_height/2)-(tag_height/2) + 'px');

        },

        _destroy: function() {

        },


        /*
         * Helpers.
         */

        __createMapDiv: function() {

            return $('<div id="drag-map" class="draggable">\
                        <span class="drag-tag">Map</span>\
                      </div>');

        },

        __createTimelineDiv: function() {

            return $('<div id="drag-timeline" class="draggable">\
                        <span class="drag-tag">Timeline</span>\
                      </div>');

        },

        __createUndatedItemsDiv: function() {

            return $('<div id="drag-undated-items" class="draggable">\
                        <span class="drag-tag">Undated Items</span>\
                      </div>');

        },

        __getUndatedItemsHeight: function() {

            var height = null;

            if (this._undated_items_height == 'full') {
                height = this._dragbox_height;
            }

            else {

                if (this._top_element == 'map') {
                    height = this._bottom_block_height;
                }

                else {
                    height = this._top_block_height;
                }

            }

            return height;

        },

        __getUndatedItemsWidth: function() {

            return this.options.undated_items_width;

        },

        __getUndatedItemsLeftOffset: function() {

            var left_offset = null;

            if (this._undated_items_position == 'left') {
                left_offset = 0;
            }

            else {
                left_offset = this._undated_items_left_offset;
            }

            return left_offset;

        },

        __getUndatedItemsTopOffset: function() {

            var top_offset = null;

            if (this._undated_items_height == 'full') {
                top_offset = 0;
            }

            else {

                if (this._top_element == 'map') {
                    top_offset = this._top_block_height;
                }

                else {
                    top_offset = 0;
                }

            }

            return top_offset;

        },

        __getMapWidth: function() {

            var width = this._dragbox_width;

            if (this._undated_items_height == 'full' && this._is_undated_items) {
                width -= this.options.undated_items_width;
            }

            return width;

        },

        __getMapHeight: function() {

            var height = this._top_block_height;

            if (this._top_element == 'timeline') {
                height = this._bottom_block_height;
            }

            return height;

        },

        __getMapLeftOffset: function() {

            var offset = 0;

            if (this._undated_items_height == 'full'
                && this._is_undated_items
                && this._undated_items_position == 'left') {

                    offset = this.options.undated_items_width;

            }

            return offset;

        },

        __getMapTopOffset: function() {

            var offset = 0;

            if (this._top_element == 'timeline') {
                offset = this._top_block_height;
            }

            return offset;

        },

        __getTimelineWidth: function() {

            var width = this._dragbox_width;

            if (this._is_undated_items) {
                width -= this.options.undated_items_width;
            }

            return width;

        },

        __getTimelineHeight: function() {

            var height = this._bottom_block_height;

            if (this._is_map) {
                if (this._top_element == 'timeline') {
                    height = this._top_block_height;
                }
            }

            else {
                height = this._dragbox_height;
            }

            return height;

        },

        __getTimelineLeftOffset: function() {

            var offset = 0;

            if (this._is_undated_items && this._undated_items_position == 'left') {
                offset = this.options.undated_items_width;
            }

            return offset;

        },

        __getTimelineTopOffset: function() {

            var offset = 0;

            if (this._top_element == 'map' && this._is_map) {
                offset = this._top_block_height;
            }

            return offset;

        }

    });

})( jQuery );


// Usage.
jQuery(document).ready(function($) {

    $('#layout-builder').layoutbuilder();

});