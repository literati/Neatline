

/*
 * Item browser widget in the Neatline editor.
 *
 * _functionName methods are "protected," __functionName methods are
 * "private."
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


    $.widget('neatline.itembrowser', {

        options: {

            // Markup hooks.
            topbar_id: 'topbar',
            search_wrapper_id: 'search-wrapper',
            search_box_id: 'search-box',
            items_list_container_id: 'items-list-container',
            items_list_header_id: 'items-list-header',

            // Durations and CSS constants.
            item_list_highlight_duration: 10,
            drag_handle_width: 4,

            colors: {
                item_list_highlight: '#f2f3fa'
            }

        },

        _create: function() {

            // Getters.
            this._window = $(window);
            this._body = $('body');
            this.topBar = $('#' + this.options.topbar_id);
            this.searchWrapper = $('#' + this.options.search_wrapper_id);
            this.searchBox = $('#' + this.options.search_box_id);
            this.itemsList = $('#' + this.options.items_list_container_id);
            this.itemsListHeader = $('#' + this.options.items_list_header_id);

            // Disable text selection on the document. This is aggressive
            // and controversial, but it solves lots of annoyances.
            this._disableSelect();

            // Get the os scrollbar width.
            this.__getScrollBarWidth();

            // Position the container, add window resize listener.
            this._positionDivs();
            this._addWindowResizeListener();

            // Construct the drag handle on the items stack.
            this._buildDragHandle();

            // Set starting filtering parameters.
            this._searchString = '';
            this._tagFilter = null;
            this._collectionFilter = null;

            // Add listener to the search box.
            this._glossSearchBox();

            // Fire starting ajax request.
            this._getItems();

        },

        _disableSelect: function() {

            // Turn off text selection on the whole container div.
            this._window.css('MozUserSelect', 'none');
            this._window.bind('selectstart mousedown', function() {
                return false;
            });

        },

        _positionDivs: function() {

            // Update dimensions and set new height.
            this._getDimensions();

            // Set the height of the main container.
            this.element.css({
                'height': this.windowHeight - this.topBarHeight - 1,
                'top': this.topBarHeight
            });

            // Set the height of the header.
            this.itemsListHeader.css({
                'top': this.topBarHeight,
                'width': this.containerWidth - this.scrollbarWidth
            });

        },

        _addWindowResizeListener: function() {

            var self = this;

            this._window.bind('resize', function() {
                self._positionDivs();
            });

        },

        _buildDragHandle: function() {

            var self = this;

            // Construct, size, and position the handle div.
            this.dragHandle = $('<div id="drag-handle"></div>');
            this.dragHandle.css({
                'width': this.options.drag_handle_width,
                'height': this.windowHeight - this.topBarHeight - 1,
                'top': this.topBarHeight,
                'left': this.containerWidth
            });

            // Append.
            this._body.append(this.dragHandle);

            // Construct the drag tooltip.
            this.tip = $('<div class="twipsy fade right in">\
                            <div class="twipsy-arrow"></div>\
                            <div class="twipsy-inner">Click to drag.</div>\
                        </div>');

            // Hide the tip by default.
            this.tip.css('display', 'none');

            // Add events.
            this.dragHandle.bind({

                'mouseenter': function() {
                    self.dragHandle.trigger('mousemove');
                },

                'mousemove': function(e) {

                    // Get pointer coordinates.
                    var offsetX = e.pageX;
                    var offsetY = e.pageY;

                    // Position and show tip.
                    self.tip.css({
                        'display': 'block',
                        'top': offsetY - 16,
                        'left': offsetX + 15
                    });

                    // Append.
                    self._body.append(self.tip);

                },

                'mouseleave': function() {

                },

                'mousedown': function(event) {
                    self._doWidthDrag(event);
                }

            });

        },

        _doWidthDrag: function(trigger_event_object) {

            var self = this;

            // Get the starting pointer coordinates.
            var startingX = trigger_event_object.pageX;

            // Get the starting width of the container.
            var startingContainerWidth = this.containerWidth;

            this._window.bind({

                'mousemove': function(e) {

                    // Fix the cursor as resize during the drag.
                    self._window.css('cursor', 'col-resize');

                    // Get the relative offset and new width.
                    var offsetX = e.pageX - startingX;
                    var newWidth = startingContainerWidth + offsetX;

                    // Resize the container and header.
                    self.element.css('width', newWidth);
                    self.itemsListHeader.css('width', newWidth - self.scrollbarWidth);

                    // Reposition the dragger.
                    self.dragHandle.css('left', newWidth);

                },

                'mouseup': function() {

                    // Unbind the events added for the drag.
                    self._window.unbind('mousemove mouseup');

                    // Set the cursor back to auto.
                    self._body.css('cursor', 'auto');

                }

            });

        },

        _getDimensions: function() {

            this.containerWidth = this.element.width();
            this.containerHeight = this.element.height();

            this.windowWidth = this._window.width();
            this.windowHeight = this._window.height();

            this.topBarHeight = this.topBar.height();

        },

        _glossSearchBox: function() {

            var self = this;

            this.searchBox.bind({

                'keyup': function() {
                    self._searchString = self.searchBox.val();
                    self._getItems();
                }

            });

        },

        _getItems: function() {

            var self = this;

            // Core ajax call to get items.
            $.ajax({

                url: 'items',
                dataType: 'html',

                data: {
                    search: this._searchString
                },

                success: function(data) {
                    self.itemsList.html(data);
                    self._positionDivs();
                    self._glossItems();
                }

            });

        },

        _glossItems: function() {

            var self = this;

            // Get the new items.
            this.items = $('#' + this.options.items_list_container_id + ' .item-row');

            // Gloss each of them.
            $.each(this.items, function(i, item) {

                var item = $(item);
                item.bind({

                    'mouseenter': function() {
                        item.addClass('highlight');
                    },

                    'mouseleave': function() {
                        item.removeClass('highlight');
                    }

                });

            });

        },

        __getScrollBarWidth: function() {

            this.scrollbarWidth = 0;

			if ($.browser.msie) {

				var $textarea1 = $('<textarea cols="10" rows="2"></textarea>')
						.css({ position: 'absolute', top: -1000, left: -1000 }).appendTo('body'),
					$textarea2 = $('<textarea cols="10" rows="2" style="overflow: hidden;"></textarea>')
						.css({ position: 'absolute', top: -1000, left: -1000 }).appendTo('body');

				this.scrollbarWidth = $textarea1.width() - $textarea2.width();
				$textarea1.add($textarea2).remove();

			}

            else {

				var $div = $('<div />')
					.css({ width: 100, height: 100, overflow: 'auto', position: 'absolute', top: -1000, left: -1000 })
					.prependTo('body').append('<div />').find('div')
						.css({ width: '100%', height: 200 });

				this.scrollbarWidth = 100 - $div.width();
				$div.parent().remove();

			}

        }

    });


})( jQuery );


// Usage.
jQuery(document).ready(function($) {

    $('#item-browser').itembrowser();

});