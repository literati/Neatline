<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Index controller integration tests.
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
 * @copyright   2011 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache 2 License
 */

/**
 * This tests for functionality in the Plugin object itself.
 **/
class NeatlinePluginTest extends Neatline_Test_AppTestCase
{

    /**
     * Set up the helper class, plugin, etc.
     *
     * @return void
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public function setUp()
    {
        parent::setUp();

        $this->db = get_db();
        $this->_dataTable = $this->db->getTable('NeatlineDataRecord');
    }

    /**
     * This tests the before_delete_record hook.
     *
     * @return void
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public function testBeforeDeleteRecord()
    {
        $item     = $this->_createItem();
        $neatline = $this->_createNeatline();
        $record   = new NeatlineDataRecord($item, $neatline);
        $record->save();

        $item->delete();

        $r2 = $this->_dataTable->find($record->id);
        $this->assertNull($r2);
    }

    /**
     * Tests for default Neatline options on installation.
     */
    public function testSetOptions()
    {
        $mapStyles = array(
            'vector_color',
            'stroke_color',
            'highlight_color',
            'vector_opacity',
            'select_opacity',
            'stroke_opacity',
            'graphic_opacity',
            'stroke_width',
            'point_radius',
            'h_percent',
            'v_percent',
            'timeline_zoom',
            'context_band_unit',
            'context_band_height'
          );

        // Check for option presence, and that it's equal to relevant value in INI.
        foreach ($mapStyles as $style) {
            $this->assertEquals(get_plugin_ini('Neatline', 'default_'.$style), get_option($style));
        }
    }
}

