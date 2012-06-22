<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Data record table tests.
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

class Neatline_NeatlineDataRecordTableTest extends Neatline_Test_AppTestCase
{

    // Testing parameters.
    private static $__testParams = array(
        'title' => 'Test Title',
        'description' => 'Test description.',
        'start_date' => '1564-04-26 14:39:22',
        'end_date' => '1616-04-23 12:45:34',
        'start_visible_date' => '1864-04-26 14:39:22',
        'end_visible_date' => '1916-04-23 12:45:34',
        'vector_color' => '#ffffff',
        'stroke_color' => '#000000',
        'vector_opacity' => 60,
        'stroke_opacity' => 40,
        'stroke_width' => 5,
        'point_radius' => 7,
        'left_percent' => 0,
        'right_percent' => 100,
        'geocoverage' => '[POINT(-1.0, 1.0)]',
        'space_active' => true,
        'time_active' => true
    );

    /**
     * Instantiate the helper class, install the plugins, get the database.
     *
     * @return void.
     */
    public function setUp()
    {

        parent::setUp();

        $this->db = get_db();
        $this->_recordsTable = $this->db->getTable('NeatlineDataRecord');

    }

    /**
     * createOrGetRecord() should return an existing record when one exists.
     *
     * @return void.
     */
    public function testCreateOrGetRecordWithRecord()
    {

        // Create item, exhibit, and record.
        $item = $this->_createItem();
        $neatline = $this->_createNeatline();
        $record1 = new NeatlineDataRecord($item, $neatline);
        $record1->save();
        $record2 = new NeatlineDataRecord(null, $neatline);
        $record2->save();

        // 2 records.
        $this->assertEquals($this->_recordsTable->count(), 2);

        // Get the record and check identity.
        $retrievedRecord = $this->_recordsTable->createOrGetRecord($item, $neatline);
        $this->assertEquals($retrievedRecord->id, $record1->id);

        // Still 2 records.
        $this->assertEquals($this->_recordsTable->count(), 2);

    }

    /**
     * createOrGetRecord() should create a new record when one does not exist.
     *
     * @return void.
     */
    public function testCreateOrGetRecordWithNoRecord()
    {

        // Create item, exhibit, and record.
        $item = $this->_createItem();
        $neatline = $this->_createNeatline();

        // 0 records.
        $this->assertEquals($this->_recordsTable->count(), 0);

        // Get the record and check identity.
        $newRecord = $this->_recordsTable->createOrGetRecord($item, $neatline);
        $this->assertEquals($newRecord->item_id, $item->id);

        // 1 record.
        $this->assertEquals($this->_recordsTable->count(), 1);

    }

    /**
     * getRecordByItemAndExhibit() should return boolean false when there is
     * no record for the given exhibit/item combination.
     *
     * @return void.
     */
    public function testGetRecordByItemAndExhibitWithNoRecord()
    {

        // Create item and exhibit.
        $item = $this->_createItem();
        $neatline = $this->_createNeatline();

        // Try to get a non-existent record.
        $noRecord = $this->_recordsTable->getRecordByItemAndExhibit($item, $neatline);
        $this->assertFalse($noRecord);

    }

    /**
     * getRecordByItemAndExhibit() should return the record when the record exists.
     *
     * @return void.
     */
    public function testGetRecordByItemAndExhibitWithRecord()
    {

        // Create item, exhibit, and record.
        $item = $this->_createItem();
        $neatline = $this->_createNeatline();
        $record = new NeatlineDataRecord($item, $neatline);
        $record->save();

        // Get the record.
        $retrievedRecord = $this->_recordsTable->getRecordByItemAndExhibit($item, $neatline);
        $this->assertEquals($record->id, $retrievedRecord->id);

    }

    /**
     * getRecordByItemAndExhibit() should escape it's parameters, just in case.
     *
     * @return void
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public function testGetRecordByItemAndExhibitEscape()
    {
        $object = (object) array( 'id' => '0; syntax error;' );
        $record = $this->_recordsTable->getRecordByItemAndExhibit(
            $object, $object
        );
        $this->assertFalse($record);
    }

    /**
     * getRecordByExhibitAndSlug() should return boolean false when there is.
     * no record for the given exhibit/slug combination.
     *
     * @return void.
     */
    public function testGetRecordByExhibitAndSlugWithNoRecord()
    {

        // Create item, exhibit, and record.
        $item = $this->_createItem();
        $neatline = $this->_createNeatline();
        $record = new NeatlineDataRecord($item, $neatline);
        $record->save();

        // Get the record.
        $noRecord = $this->_recordsTable->getRecordByExhibitAndSlug($neatline, 'test-slug');
        $this->assertFalse($noRecord);

    }

    /**
     * getRecordByExhibitAndSlug() should return the record when one exists.
     *
     * @return void.
     */
    public function testGetRecordByExhibitAndSlugWithRecord()
    {

        // Create item, exhibit, and record.
        $item = $this->_createItem();
        $neatline = $this->_createNeatline();
        $record = new NeatlineDataRecord($item, $neatline);
        $record->slug = 'test-slug';
        $record->save();

        // Get the record.
        $retrievedRecord = $this->_recordsTable->getRecordByExhibitAndSlug($neatline, 'test-slug');
        $this->assertEquals($retrievedRecord->id, $record->id);

    }

    /**
     * getRecordByExhibitAndSlug() should escape the parameters.
     *
     * @return void
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public function testGetRecordByExhibitAndSlugEscape()
    {
        $obj1  = (object) array( 'id' => 1 );
        $obj2  = (object) array( 'id' => '0; syntax error;' );
        $slug1 = 'slug-1';
        $slug2 = '"; syntax error;';

        $this->assertFalse(
            $this->_recordsTable->getRecordByExhibitAndSlug($obj1, $slug1)
        );
        $this->assertFalse(
            $this->_recordsTable->getRecordByExhibitAndSlug($obj2, $slug1)
        );
        $this->assertFalse(
            $this->_recordsTable->getRecordByExhibitAndSlug($obj1, $slug2)
        );
        $this->assertFalse(
            $this->_recordsTable->getRecordByExhibitAndSlug($obj2, $slug2)
        );
    }

    /**
     * slugIsAvailable() should always return true when an empty string is passed.
     *
     * @return void.
     */
    public function testSlugIsAvailableWithEmptyString()
    {

        // Create item and exhibit.
        $item = $this->_createItem();
        $exhibit = $this->_createNeatline();

        // Create two records.
        $record1 = new NeatlineDataRecord($item, $exhibit);
        $record1->slug = 'test-slug';
        $record1->save();
        $record2 = new NeatlineDataRecord($item, $exhibit);
        $record2->slug = '';
        $record2->save();

        $this->assertTrue(
            $this->_recordsTable->slugIsAvailable($record1, $exhibit, '')
        );

    }

    /**
     * slugIsAvailable() should return false when there is a non-self duplicate.
     *
     * @return void.
     */
    public function testSlugIsAvailableWithNonSelfDuplicate()
    {

        // Create item and exhibit.
        $item = $this->_createItem();
        $exhibit = $this->_createNeatline();

        // Create two records.
        $record1 = new NeatlineDataRecord($item, $exhibit);
        $record1->slug = 'test-slug';
        $record1->save();
        $record2 = new NeatlineDataRecord($item, $exhibit);
        $record2->slug = 'another-slug';
        $record2->save();

        $this->assertFalse(
            $this->_recordsTable->slugIsAvailable($record1, $exhibit, 'another-slug')
        );

    }

    /**
     * slugIsAvailable() should return true when the duplicate is self.
     *
     * @return void.
     */
    public function testSlugIsAvailableWithSelfDuplicate()
    {

        // Create item and exhibit.
        $item = $this->_createItem();
        $exhibit = $this->_createNeatline();

        // Create two records.
        $record1 = new NeatlineDataRecord($item, $exhibit);
        $record1->slug = 'test-slug';
        $record1->save();
        $record2 = new NeatlineDataRecord($item, $exhibit);
        $record2->slug = 'another-slug';
        $record2->save();

        $this->assertTrue(
            $this->_recordsTable->slugIsAvailable($record1, $exhibit, 'test-slug')
        );

    }

    /**
     * getRecordsForSelect() should return an array of id => title with each
     * of the records in the exhibit.
     *
     * @return void.
     */
    public function testGetRecordsForSelectWithNoSelfRecord()
    {

        // Create exhibit.
        $exhibit = $this->_createNeatline();
        $record1 = new NeatlineDataRecord(null, $exhibit);
        $record1->title = 'Title 1';
        $record1->save();
        $record2 = new NeatlineDataRecord(null, $exhibit);
        $record2->title = 'Title 2';
        $record2->save();

        // Build array.
        $idToTitle = $this->_recordsTable->getRecordsForSelect($exhibit);

        // Check structure.
        $this->assertEquals(
            $idToTitle,
            array(
                $record1->id => 'Title 1',
                $record2->id => 'Title 2',
            )
        );

    }

    /**
     * When a self record is passed, getRecordsForSelect() should return an
     * array of id => title with each of the records in the exhibit not including
     * the self record.
     *
     * @return void.
     */
    public function testGetRecordsForSelectWithSelfRecord()
    {

        // Create exhibit.
        $exhibit = $this->_createNeatline();
        $record1 = new NeatlineDataRecord(null, $exhibit);
        $record1->title = 'Title 1';
        $record1->save();
        $record2 = new NeatlineDataRecord(null, $exhibit);
        $record2->title = 'Title 2';
        $record2->save();
        $record3 = new NeatlineDataRecord(null, $exhibit);
        $record3->title = 'Title 3';
        $record3->save();

        // Build array.
        $idToTitle = $this->_recordsTable->getRecordsForSelect($exhibit, $record2);

        // Check structure.
        $this->assertEquals(
            $idToTitle,
            array(
                $record1->id => 'Title 1',
                $record3->id => 'Title 3'
            )
        );

    }

    /**
     * saveRecordStatus() should create a new record when there when there
     * is no existing record.
     *
     * @return void.
     */
    public function testSaveRecordStatusWithNoRecord()
    {

        // Create item, exhibit, and record.
        $item = $this->_createItem();
        $neatline = $this->_createNeatline();

        // At the start, no records.
        $this->assertEquals($this->_recordsTable->count(), 0);

        // Save form data for a non-existent record.
        $record = $this->_recordsTable->saveRecordStatus($item, $neatline, 'items', true);

        // After the save, there should be 1 record.
        $this->assertEquals($this->_recordsTable->count(), 1);

        // Check that the parameter was set.
        $this->assertEquals($record->items_active, 1);
        $record->delete();

        // At the start, no records.
        $this->assertEquals($this->_recordsTable->count(), 0);

        // Save form data for a non-existent record.
        $record = $this->_recordsTable->saveRecordStatus($item, $neatline, 'space', true);

        // After the save, there should be 1 record.
        $this->assertEquals($this->_recordsTable->count(), 1);

        // Check that the parameter was set.
        $this->assertEquals($record->space_active, 1);
        $record->delete();

        // At the start, no records.
        $this->assertEquals($this->_recordsTable->count(), 0);

        // Save form data for a non-existent record.
        $record = $this->_recordsTable->saveRecordStatus($item, $neatline, 'time', true);

        // After the save, there should be 1 record.
        $this->assertEquals($this->_recordsTable->count(), 1);

        // Check that the parameter was set.
        $this->assertEquals($record->time_active, 1);
        $record->delete();


    }

    /**
     * saveRecordStatus() should update an existing record if one exists.
     *
     * @return void.
     */
    public function testSaveRecordStatusWithRecord()
    {

        // Create item, exhibit, and record.
        $item = $this->_createItem();
        $neatline = $this->_createNeatline();
        $record = new NeatlineDataRecord($item, $neatline);
        $record->save();

        // At the start, no records.
        $this->assertEquals($this->_recordsTable->count(), 1);

        // Save form data for a non-existent record.
        $record = $this->_recordsTable->saveRecordStatus($item, $neatline, 'items', true);

        // After the save, there should be 1 record.
        $this->assertEquals($this->_recordsTable->count(), 1);
        $this->assertEquals($record->items_active, 1);

        // At the start, no records.
        $this->assertEquals($this->_recordsTable->count(), 1);

        // Save form data for a non-existent record.
        $record = $this->_recordsTable->saveRecordStatus($item, $neatline, 'space', true);

        // After the save, there should be 1 record.
        $this->assertEquals($this->_recordsTable->count(), 1);
        $this->assertEquals($record->space_active, 1);

        // At the start, no records.
        $this->assertEquals($this->_recordsTable->count(), 1);

        // Save form data for a non-existent record.
        $record = $this->_recordsTable->saveRecordStatus($item, $neatline, 'time', true);
        $this->assertEquals($this->_recordsTable->count(), 1);

        // Check that the parameter was set.
        $this->assertEquals($record->time_active, 1);

    }

    /**
     * If there is a record for an item, getRecordStatus() should return the
     * space or time active status.
     *
     * @return void.
     */
    public function testGetRecordStatusWithExistingRecord()
    {

        // Create item, exhibit, and record.
        $item = $this->_createItem();
        $neatline = $this->_createNeatline();
        $record = new NeatlineDataRecord($item, $neatline);

        // Populate statuses with trues.
        $record->space_active = 1;
        $record->time_active = 1;
        $record->items_active = 1;
        $record->save();

        // Get.
        $spaceStatus = $this->_recordsTable->getRecordStatus($item, $neatline, 'space');
        $timeStatus = $this->_recordsTable->getRecordStatus($item, $neatline, 'time');
        $itemsStatus = $this->_recordsTable->getRecordStatus($item, $neatline, 'items');

        // Check.
        $this->assertTrue($spaceStatus);
        $this->assertTrue($timeStatus);
        $this->assertTrue($itemsStatus);

        // Populate statuses with falses.
        $record->space_active = 0;
        $record->time_active = 0;
        $record->items_active = 0;
        $record->save();

        // Get.
        $spaceStatus = $this->_recordsTable->getRecordStatus($item, $neatline, 'space');
        $timeStatus = $this->_recordsTable->getRecordStatus($item, $neatline, 'time');
        $itemsStatus = $this->_recordsTable->getRecordStatus($item, $neatline, 'items');

        // Check.
        $this->assertFalse($spaceStatus);
        $this->assertFalse($timeStatus);
        $this->assertFalse($itemsStatus);

    }

    /**
     * If there is not a record for an item, getRecordStatus() should return false.
     *
     * @return void.
     */
    public function testGetRecordStatusWithNoExistingRecord()
    {

        // Create item, exhibit, and record.
        $item = $this->_createItem();
        $neatline = $this->_createNeatline();

        // Get.
        $spaceStatus = $this->_recordsTable->getRecordStatus($item, $neatline, 'space');
        $timeStatus = $this->_recordsTable->getRecordStatus($item, $neatline, 'time');
        $itemsStatus = $this->_recordsTable->getRecordStatus($item, $neatline, 'items');

        // Check.
        $this->assertFalse($spaceStatus);
        $this->assertFalse($timeStatus);
        $this->assertFalse($itemsStatus);

    }

    /**
     * The getRecordsByExhibit() should return all data records associated with a
     * given Neatline exhibit.
     *
     * @return void.
     */
    public function testGetRecordsByExhibitWithRecords()
    {

        // Create two items and an exhibit.
        $item1 = $this->_createItem();
        $item2 = $this->_createItem();
        $neatline = $this->_createNeatline();

        // Create two records.
        $record1 = new NeatlineDataRecord($item1, $neatline);
        $record1->save();
        $record2 = new NeatlineDataRecord($item2, $neatline);
        $record2->save();
        $record3 = new NeatlineDataRecord(null, $neatline);
        $record3->save();

        // Get the records and check result.
        $records = $this->_recordsTable->getRecordsByExhibit($neatline);
        $this->assertEquals(count($records), 3);
        $this->assertEquals($records[0]->id, $record1->id);
        $this->assertEquals($records[1]->id, $record2->id);
        $this->assertEquals($records[2]->id, $record3->id);

    }

    /**
     * When there are no records for an exhibit, getRecordsByExhibit() should
     * return false.
     *
     * @return void.
     */
    public function testGetRecordsByExhibitWithNoRecords()
    {

        // Create two items and an exhibit.
        $neatline = $this->_createNeatline();

        // Get the records and check result.
        $records = $this->_recordsTable->getRecordsByExhibit($neatline);
        $this->assertFalse($records);

    }

    /**
     * getRecordsByExhibit() should escape SQL parameters.
     *
     * @return void
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public function testGetRecordsByExhibitEscape()
    {
        $nl = (object) array( 'id' => '; syntax error;' );
        $this->assertFalse($this->_recordsTable->getRecordsByExhibit($nl));
    }

    /**
     * When there are records for an exhibit that do not have a parent item,
     * getNeatlineRecordsByExhibit() should return the records.
     *
     * @return void.
     */
    public function testGetNeatlineRecordsByExhibitWithRecords()
    {

        // Create an exhibit.
        $neatline = $this->_createNeatline();
        $item = $this->_createItem();

        // Create two records, one with a parent item and one without.
        $record1 = new NeatlineDataRecord(null, $neatline);
        $record1->save();
        $record2 = new NeatlineDataRecord($item, $neatline);
        $record2->save();

        // Get the records and check result.
        $records = $this->_recordsTable->getNeatlineRecordsByExhibit($neatline);
        $this->assertEquals(count($records), 1);
        $this->assertEquals($records[0]->id, $record1->id);

    }

    /**
     * When there are no item-null records for an exhibit, getNeatlineRecordsByExhibit()
     * should return false.
     *
     * @return void.
     */
    public function testGetNeatlineRecordsByExhibitWithNoRecords()
    {

        // Create two items and an exhibit.
        $neatline = $this->_createNeatline();
        $item = $this->_createItem();

        // Create two records, one with a parent item and one without.
        $record = new NeatlineDataRecord($item, $neatline);
        $record->save();

        // Get the records and check result.
        $records = $this->_recordsTable->getNeatlineRecordsByExhibit($neatline);
        $this->assertFalse($records);

    }

    /**
     * getNeatlineRecordsByExhibit() should escape SQL parameters.
     *
     * @return void
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public function testGetNeatlineRecordsByExhibitEscape()
    {
        $nl = (object) array( 'id' => '; syntax error;' );
        $this->assertFalse($this->_recordsTable->getNeatlineRecordsByExhibit($nl));
    }

    /**
     * When there are records for an exhibit that do not have a parent item,
     * searchNeatlineRecordsByExhibit() should return the records with title
     * substrings that match the passed search parameter.
     *
     * @return void.
     */
    public function testSearchNeatlineRecordsByExhibitWithRecords()
    {

        // Create an exhibit.
        $neatline = $this->_createNeatline();

        // Create two records, one with a parent item and one without.
        $record1 = new NeatlineDataRecord(null, $neatline);
        $record1->title = 'test title';
        $record1->save();
        $record2 = new NeatlineDataRecord(null, $neatline);
        $record2->title = 'no match';
        $record2->save();

        // Should get 1 record.
        $records = $this->_recordsTable->searchNeatlineRecordsByExhibit($neatline, 'test');
        $this->assertEquals(count($records), 1);

        // Check identity.
        $this->assertEquals($records[0]->id, $record1->id);

    }

    /**
     * When there are no item-null records for an exhibit searchNeatlineRecordsByExhibit()
     * should return false.
     *
     * @return void.
     */
    public function testSearchNeatlineRecordsByExhibitWithNoRecords()
    {

        // Create an exhibit.
        $neatline = $this->_createNeatline();

        // False when no records.
        $records = $this->_recordsTable->searchNeatlineRecordsByExhibit($neatline, 'test');
        $this->assertFalse($records);

    }

    /**
     * When there are no item-null records for an exhibit that match the passed search
     * string, searchNeatlineRecordsByExhibit() should return false.
     *
     * @return void.
     */
    public function testSearchNeatlineRecordsByExhibitWithNoMatchingRecords()
    {

        // Create an exhibit.
        $neatline = $this->_createNeatline();

        // Create two records, one with a parent item and one without.
        $record1 = new NeatlineDataRecord(null, $neatline);
        $record1->title = 'no match';
        $record1->save();
        $record2 = new NeatlineDataRecord(null, $neatline);
        $record2->title = 'another no match';
        $record2->save();

        // Should return false for no matches.
        $records = $this->_recordsTable->searchNeatlineRecordsByExhibit($neatline, 'test');
        $this->assertFalse($records);

    }

    /**
     * searchNeatlineRecordsByExhibit() should escape SQL parameters.
     *
     * @return void
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public function testSearchNeatlineRecordsByExhibitEscape()
    {
        $obj1    = (object) array( 'id' => 1 );
        $obj2    = (object) array( 'id' => '; syntax error;');
        $search1 = 'target';
        $search2 = '"; syntax error;';

        $this->assertFalse(
            $this->_recordsTable->searchNeatlineRecordsByExhibit($obj1, $search1)
        );
        $this->assertFalse(
            $this->_recordsTable->searchNeatlineRecordsByExhibit($obj2, $search1)
        );
        $this->assertFalse(
            $this->_recordsTable->searchNeatlineRecordsByExhibit($obj1, $search2)
        );
        $this->assertFalse(
            $this->_recordsTable->searchNeatlineRecordsByExhibit($obj2, $search2)
        );
    }

    /**
     * getActiveRecordsByExhibit() should return all data records associated
     * with a given Neatline exhibit that have an active space or time record.
     *
     * @return void.
     */
    public function testGetActiveRecordsByExhibit()
    {

        // Create two items and an exhibit.
        $item1 = $this->_createItem();
        $item2 = $this->_createItem();
        $neatline = $this->_createNeatline();

        // Create two records with inactive status settings.
        $record1 = new NeatlineDataRecord($item1, $neatline);
        $record1->save();
        $record2 = new NeatlineDataRecord($item2, $neatline);
        $record2->save();

        // Should return false.
        $records = $this->_recordsTable->getActiveRecordsByExhibit($neatline);
        $this->assertFalse($records);

        $record1->delete();
        $record2->delete();

        // Create two records, one with an active space status.
        $record1 = new NeatlineDataRecord($item1, $neatline);
        $record1->save();
        $record2 = new NeatlineDataRecord($item2, $neatline);
        $record2->space_active = 1;
        $record2->save();

        // Get the records and check result.
        $records = $this->_recordsTable->getActiveRecordsByExhibit($neatline);
        $this->assertEquals(count($records), 1);
        $this->assertEquals($records[0]->id, $record2->id);

        $record1->delete();
        $record2->delete();

        // Create two records, one with an active space status.
        $record1 = new NeatlineDataRecord($item1, $neatline);
        $record1->save();
        $record2 = new NeatlineDataRecord($item2, $neatline);
        $record2->time_active = 1;
        $record2->save();

        // Get the records and check result.
        $records = $this->_recordsTable->getActiveRecordsByExhibit($neatline);
        $this->assertEquals(count($records), 1);
        $this->assertEquals($records[0]->id, $record2->id);

    }

    /**
     * getActiveRecordsByExhibit() order items by the display_order column.
     *
     * @return void.
     */
    public function testGetActiveRecordsByExhibitOrdering()
    {

        // Create an exhibit, items, and records.
        $neatline = $this->_createNeatline();
        $item1 = $this->_createItem();
        $item2 = $this->_createItem();
        $item3 = $this->_createItem();
        $item4 = $this->_createItem();
        $record1 = new NeatlineDataRecord($item1, $neatline);
        $record2 = new NeatlineDataRecord($item2, $neatline);
        $record3 = new NeatlineDataRecord($item3, $neatline);
        $record4 = new NeatlineDataRecord($item4, $neatline);
        $record1->items_active = 1;
        $record2->items_active = 1;
        $record3->items_active = 1;
        $record4->items_active = 1;
        $record1->save();
        $record2->save();
        $record3->save();
        $record4->save();

        // Get the items.
        $records = $this->_recordsTable->getActiveRecordsByExhibit($neatline);

        // By default, the records should be ordered by item id.
        $this->assertEquals($records[0]->item_id, $item1->id);
        $this->assertEquals($records[1]->item_id, $item2->id);
        $this->assertEquals($records[2]->item_id, $item3->id);
        $this->assertEquals($records[3]->item_id, $item4->id);

        $order = array(
            $record1->id => 3,
            $record2->id => 2,
            $record3->id => 1,
            $record4->id => 0
        );

        // Push a new order.
        $this->_recordsTable->saveOrder($neatline, $order);

        // Get the items.
        $records = $this->_recordsTable->getActiveRecordsByExhibit($neatline);

        // The records should be reordered.
        $this->assertEquals($records[0]->item_id, $item4->id);
        $this->assertEquals($records[1]->item_id, $item3->id);
        $this->assertEquals($records[2]->item_id, $item2->id);
        $this->assertEquals($records[3]->item_id, $item1->id);

    }

    /**
     * getActiveRecordsByExhibit() should escape SQL parameters.
     *
     * @return void
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public function testGetActiveRecordsByExhibitEscape()
    {
        $exhibit = (object) array( 'id' => '; syntax error;' );
        $this->assertFalse(
            $this->_recordsTable->getActiveRecordsByExhibit($exhibit)
        );
    }

    /**
     * getItemsRecordsByExhibit() should return all data records associated
     * with a given Neatline exhibit that are active in the items viewport.
     *
     * @return void.
     */
    public function testGetItemsRecordsByExhibit()
    {

        // Create two items and an exhibit.
        $item1 = $this->_createItem();
        $item2 = $this->_createItem();
        $neatline = $this->_createNeatline();

        // Create two records with inactive status settings.
        $record1 = new NeatlineDataRecord($item1, $neatline);
        $record1->save();
        $record2 = new NeatlineDataRecord($item2, $neatline);
        $record2->save();

        // Should return false.
        $records = $this->_recordsTable->getItemsRecordsByExhibit($neatline);
        $this->assertFalse($records);

        $record1->delete();
        $record2->delete();

        // Create two records, one with an active time status.
        $record1 = new NeatlineDataRecord($item1, $neatline);
        $record1->save();
        $record2 = new NeatlineDataRecord($item2, $neatline);
        $record2->items_active = 1;
        $record2->save();

        // Get the records and check result.
        $records = $this->_recordsTable->getItemsRecordsByExhibit($neatline);
        $this->assertEquals(count($records), 1);
        $this->assertEquals($records[0]->id, $record2->id);

    }

    /**
     * getItemsRecordsByExhibit() order records by the display_order column.
     *
     * @return void.
     */
    public function testGetItemsRecordsByExhibitOrdering()
    {

        // Create an exhibit, items, and records.
        $neatline = $this->_createNeatline();
        $item1 = $this->_createItem();
        $item2 = $this->_createItem();
        $item3 = $this->_createItem();
        $item4 = $this->_createItem();
        $record1 = new NeatlineDataRecord($item1, $neatline);
        $record2 = new NeatlineDataRecord($item2, $neatline);
        $record3 = new NeatlineDataRecord($item3, $neatline);
        $record4 = new NeatlineDataRecord($item4, $neatline);
        $record1->items_active = 1;
        $record2->items_active = 1;
        $record3->items_active = 1;
        $record4->items_active = 1;
        $record1->save();
        $record2->save();
        $record3->save();
        $record4->save();

        // Get the items.
        $records = $this->_recordsTable->getItemsRecordsByExhibit($neatline);

        // By default, the records should be ordered by item id.
        $this->assertEquals($records[0]->item_id, $item1->id);
        $this->assertEquals($records[1]->item_id, $item2->id);
        $this->assertEquals($records[2]->item_id, $item3->id);
        $this->assertEquals($records[3]->item_id, $item4->id);

        $order = array(
            $record1->id => 3,
            $record2->id => 2,
            $record3->id => 1,
            $record4->id => 0
        );

        // Push a new order.
        $this->_recordsTable->saveOrder($neatline, $order);

        // Get the items.
        $records = $this->_recordsTable->getItemsRecordsByExhibit($neatline);

        // The records should be reordered.
        $this->assertEquals($records[0]->item_id, $item4->id);
        $this->assertEquals($records[1]->item_id, $item3->id);
        $this->assertEquals($records[2]->item_id, $item2->id);
        $this->assertEquals($records[3]->item_id, $item1->id);

    }

    /**
     * getItemsRecordsByExhibit() should escape SQL parameters.
     *
     * @return void
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public function testGetItemsRecordsByExhibitEscape()
    {
        $exhibit = (object) array( 'id' => '; syntax error;' );
        $this->assertFalse(
            $this->_recordsTable->getItemsRecordsByExhibit($exhibit)
        );
    }

    /**
     * The buildMapJson() method should construct well-formed JSON string with
     * the correct attributes and geocoverage fields populated for each record.
     *
     * @return void.
     */
    public function testBuildMapJson()
    {

        // Create an exhibit and items.
        $neatline = $this->_createNeatline();
        $item1 = $this->_createItem();
        $item2 = $this->_createItem();

        // Create two records.
        $record1 = new NeatlineDataRecord($item1, $neatline);
        $record2 = new NeatlineDataRecord($item2, $neatline);

        // Populate map-relevant attributes.
        $record1->title = 'Item 1 Title';
        $record2->title = 'Item 2 Title';
        $record1->description = 'Item 1 description.';
        $record2->description = 'Item 2 description.';
        $record1->show_bubble = 1;
        $record2->show_bubble = 1;
        $record1->slug = 'slug-1';
        $record2->slug = 'slug-2';
        $record1->vector_color = '#ffffff';
        $record2->vector_color = '#000000';
        $record1->stroke_color = '#ffffff';
        $record2->stroke_color = '#000000';
        $record1->highlight_color = '#ffffff';
        $record2->highlight_color = '#000000';
        $record1->vector_opacity = 60;
        $record2->vector_opacity = 40;
        $record1->select_opacity = 60;
        $record2->select_opacity = 40;
        $record1->stroke_opacity = 60;
        $record2->stroke_opacity = 40;
        $record1->graphic_opacity = 60;
        $record2->graphic_opacity = 40;
        $record1->stroke_width = 3;
        $record2->stroke_width = 2;
        $record1->point_radius = 3;
        $record2->point_radius = 2;
        $record1->point_image = 'http://test1.org';
        $record2->point_image = 'http://test2.org';
        $record1->geocoverage = 'POINT(1,0)';
        $record2->geocoverage = 'POINT(0,1)';
        $record1->space_active = 1;
        $record2->space_active = 1;
        $record1->map_bounds = 'CENTER(1)';
        $record2->map_bounds = 'CENTER(2)';
        $record1->map_zoom = 4;
        $record2->map_zoom = 5;
        $record1->start_visible_date = '1864-04-26 14:39:22';
        $record2->start_visible_date = '1964-04-26 14:39:22';
        $record1->end_visible_date = '1916-04-23 12:45:34';
        $record2->end_visible_date = '2016-04-23 12:45:34';
        $record1->save();
        $record2->save();

        // Build the JSON.
        $json = json_decode($this->_recordsTable->buildMapJson($neatline));

        $this->assertEquals(
            $json,
            array(
                (object) array(
                    'id' => $record1->id,
                    'item_id' => $item1->id,
                    'title' => 'Item 1 Title',
                    'description' => 'Item 1 description.',
                    'slug' => 'slug-1',
                    'vector_color' => '#ffffff',
                    'stroke_color' => '#ffffff',
                    'highlight_color' => '#ffffff',
                    'vector_opacity' => 60,
                    'select_opacity' => 60,
                    'stroke_opacity' => 60,
                    'graphic_opacity' => 60,
                    'stroke_width' => 3,
                    'point_radius' => 3,
                    'point_image' => 'http://test1.org',
                    'center' => 'CENTER(1)',
                    'zoom' => 4,
                    'wkt' => 'POINT(1,0)',
                    'start_visible_date' => '1864-04-26 14:39:22',
                    'end_visible_date' => '1916-04-23 12:45:34',
                    'show_bubble' => 1,
                    'wmsAddress' => null,
                    'layers' => null,
                    '_native_styles' => (object) array(
                        'vector_color' => '#ffffff',
                        'vector_opacity' => 60,
                        'stroke_color' => '#ffffff',
                        'stroke_opacity' => 60,
                        'stroke_width' => 3,
                        'graphic_opacity' => 60,
                        'point_radius' => 3
                    )
                ),
                (object) array(
                    'id' => $record2->id,
                    'item_id' => $item2->id,
                    'title' => 'Item 2 Title',
                    'description' => 'Item 2 description.',
                    'slug' => 'slug-2',
                    'vector_color' => '#000000',
                    'stroke_color' => '#000000',
                    'highlight_color' => '#000000',
                    'vector_opacity' => 40,
                    'select_opacity' => 40,
                    'stroke_opacity' => 40,
                    'graphic_opacity' => 40,
                    'stroke_width' => 2,
                    'point_radius' => 2,
                    'point_image' => 'http://test2.org',
                    'center' => 'CENTER(2)',
                    'zoom' => 5,
                    'wkt' => 'POINT(0,1)',
                    'start_visible_date' => '1964-04-26 14:39:22',
                    'end_visible_date' => '2016-04-23 12:45:34',
                    'show_bubble' => 1,
                    'wmsAddress' => null,
                    'layers' => null,
                    '_native_styles' => (object) array(
                        'vector_color' => '#000000',
                        'vector_opacity' => 40,
                        'stroke_color' => '#000000',
                        'stroke_opacity' => 40,
                        'stroke_width' => 2,
                        'graphic_opacity' => 40,
                        'point_radius' => 2
                    )
                )
            )
        );

    }

    /**
     * If item-specific focus/style data has not been set, buildMapJson() should
     * return null for the bouds and zoom fields.
     *
     * @return void.
     */
    public function testBuildMapJsonWithUnsetFocusAndStylingData()
    {

        // Create an exhibit and items.
        $neatline = $this->_createNeatline();
        $item1 = $this->_createItem();
        $item2 = $this->_createItem();

        // Create two records.
        $record1 = new NeatlineDataRecord($item1, $neatline);
        $record2 = new NeatlineDataRecord($item2, $neatline);

        // Populate map-relevant attributes.
        $record1->title = 'Item 1 Title';
        $record2->title = 'Item 2 Title';
        $record1->geocoverage = 'POINT(1,0)';
        $record2->geocoverage = 'POINT(0,1)';
        $record1->space_active = 1;
        $record2->space_active = 1;
        $record1->save();
        $record2->save();

        // Build the JSON.
        $json = json_decode($this->_recordsTable->buildMapJson($neatline));
        $this->assertNull($json[0]->zoom);
        $this->assertNull($json[0]->center);
        $this->assertNull($json[1]->zoom);
        $this->assertNull($json[1]->center);

    }

    /**
     * The buildMapJson() method should exclude items without active
     * space status trackers.
     *
     * @return void.
     */
    public function testBuildMapJsonWithInactiveStatus()
    {

        // Create an exhibit and items.
        $neatline = $this->_createNeatline();
        $item = $this->_createItem();

        // Create record.
        $record = new NeatlineDataRecord($item, $neatline);

        // Populate map-relevant attributes.
        $record->title = 'Title';
        $record->vector_color = '#ffffff';
        $record->geocoverage = 'POINT(1,0)';
        $record->space_active = 0;
        $record->save();

        // Build the JSON.
        $json = $this->_recordsTable->buildMapJson($neatline);

        // Check format.
        $this->assertEquals(
            $json,
            '[]'
        );

    }

    /**
     * The buildMapJson() method should return an empty object when there are
     * no data records for an exhibit.
     *
     * @return void.
     */
    public function testBuildMapJsonWithNoRecords()
    {

        // Create an exhibit.
        $neatline = $this->_createNeatline();

        // Build the JSON.
        $json = $this->_recordsTable->buildMapJson($neatline);

        // Check format.
        $this->assertEquals(
            $json,
            '[]'
        );

    }

    /**
     * The buildTimelineJson() method should construct well-formed JSON string with
     * the correct attributes populated for each record.
     *
     * @return void.
     */
    public function testBuildTimelineJson()
    {

        // Create an exhibit and items.
        $neatline = $this->_createNeatline();
        $item1 = $this->_createItem();
        $item2 = $this->_createItem();

        // Create two records.
        $record1 = new NeatlineDataRecord($item1, $neatline);
        $record2 = new NeatlineDataRecord($item2, $neatline);

        // Populate map-relevant attributes.
        $record1->title = 'Item 1 Title';
        $record2->title = 'Item 2 Title';
        $record1->slug = 'slug-1';
        $record2->slug = 'slug-2';
        $record1->description = 'Item 1 description.';
        $record2->description = 'Item 2 description.';
        $record1->start_date = '1564-04-26 14:39:22';
        $record2->start_date = '1564-04-26 14:39:22';
        $record1->end_date = '1616-04-23 12:45:34';
        $record2->end_date = '1616-04-23 12:45:34';
        $record1->vector_color = '#ffffff';
        $record2->vector_color = '#000000';
        $record1->left_percent = 0;
        $record2->left_percent = 0;
        $record1->right_percent = 100;
        $record1->right_percent = 100;
        $record1->time_active = 1;
        $record2->time_active = 1;
        $record1->save();
        $record2->save();

        // Build the JSON.
        $json = $this->_recordsTable->buildTimelineJson($neatline);

        // Check format.
        $this->assertContains(
            '"dateTimeFormat":"iso8601"',
            $json
        );

        $this->assertContains(
            '"events":',
            $json
        );

        $this->assertContains(
            '"eventID":' . $record1->id,
            $json
        );

        $this->assertContains(
            '"title":"' . $record1->title . '"',
            $json
        );

        $this->assertContains(
            '"slug":"' . $record1->slug . '"',
            $json
        );

        $this->assertContains(
            '"description":"' . $record1->description . '"',
            $json
        );

        $this->assertContains(
            '"color":"' . $record1->vector_color . '"',
            $json
        );

        $this->assertContains(
            '"left_ambiguity":' . $record1->left_percent,
            $json
        );

        $this->assertContains(
            '"right_ambiguity":' . $record1->right_percent,
            $json
        );

        $this->assertContains(
            '"start":"1564-04-26 14:39:22"',
            $json
        );

        $this->assertContains(
            '"end":"1616-04-23 12:45:34"',
            $json
        );

        $this->assertContains(
            '"dateTimeFormat":"iso8601"',
            $json
        );

        $this->assertContains(
            '"events":',
            $json
        );

        $this->assertContains(
            '"eventID":' . $record2->id,
            $json
        );

        $this->assertContains(
            '"title":"' . $record2->title . '"',
            $json
        );

        $this->assertContains(
            '"slug":"' . $record2->slug . '"',
            $json
        );

        $this->assertContains(
            '"description":"' . $record2->description . '"',
            $json
        );

        $this->assertContains(
            '"color":"' . $record2->vector_color . '"',
            $json
        );

        $this->assertContains(
            '"left_ambiguity":' . $record2->left_percent,
            $json
        );

        $this->assertContains(
            '"right_ambiguity":' . $record2->right_percent,
            $json
        );

        $this->assertContains(
            '"start":"1564-04-26 14:39:22"',
            $json
        );

        $this->assertContains(
            '"end":"1616-04-23 12:45:34"',
            $json
        );

    }

    /**
     * The buildTimelineJson() method should exclude items without active
     * space status trackers.
     *
     * @return void.
     */
    public function testBuildTimelineJsonWithInactiveStatus()
    {

        // Create an exhibit and items.
        $neatline = $this->_createNeatline();
        $item = $this->_createItem();

        // Create two records.
        $record = new NeatlineDataRecord($item, $neatline);

        // Populate map-relevant attributes.
        $record->title = 'Item Title';
        $record->description = 'Item description.';
        $record->start_date = '1564-04-26 14:39:22';
        $record->end_date = '1616-04-23 12:45:34';
        $record->vector_color = '#ffffff';
        $record->left_percent = 0;
        $record->right_percent = 100;
        $record->time_active = 0;
        $record->save();

        // Build the JSON.
        $json = $this->_recordsTable->buildTimelineJson($neatline);

        // Check format.
        $this->assertContains('"dateTimeFormat":"iso8601"', $json);
        $this->assertContains('"events":[]', $json);

    }

    /**
     * The buildTimelineJson() method should default in the exhibit color
     * default for the 'color' attribute in the JSON.
     *
     * @return void.
     */
    public function testBuildTimelineJsonWithNullVectorColorAndExhibitDefault()
    {

        // Create an exhibit and items.
        $neatline = $this->_createNeatline();
        $item = $this->_createItem();

        // Set an exhibit default.
        $neatline->default_vector_color = '#ffffff';
        $neatline->save();

        // Create record.
        $record = new NeatlineDataRecord($item, $neatline);

        // Populate map-relevant attributes.
        $record->title = 'Item Title';
        $record->start_date = '1564-04-26 14:39:22';
        $record->end_date = '1616-04-23 12:45:34';
        $record->left_percent = 0;
        $record->right_percent = 100;
        $record->time_active = 1;
        $record->save();

        // Build the JSON.
        $json = $this->_recordsTable->buildTimelineJson($neatline);

        $this->assertContains(
            '"color":"#ffffff"',
            $json
        );

    }

    /**
     * The buildTimelineJson() method should default in the system color
     * default for the 'color' attribute in the JSON.
     *
     * @return void.
     */
    public function testBuildTimelineJsonWithNullVectorColorAndSystemDefault()
    {

        // Create an exhibit and items.
        $neatline = $this->_createNeatline();
        $item = $this->_createItem();

        // Set a system default.
        set_option('vector_color', '#000000');

        // Create record.
        $record = new NeatlineDataRecord($item, $neatline);

        // Populate map-relevant attributes.
        $record->title = 'Item Title';
        $record->start_date = '1564-04-26 14:39:22';
        $record->end_date = '1616-04-23 12:45:34';
        $record->left_percent = 0;
        $record->right_percent = 100;
        $record->time_active = 1;
        $record->save();

        // Build the JSON.
        $json = $this->_recordsTable->buildTimelineJson($neatline);

        $this->assertContains(
            '"color":"#000000"',
            $json
        );

    }

    /**
     * The buildTimelineJson() method should return an empty object when there are
     * no data records for an exhibit.
     *
     * @return void.
     */
    public function testBuildTimelineJsonWithNoRecords()
    {

        // Create an exhibit.
        $neatline = $this->_createNeatline();

        // Build the JSON.
        $json = $this->_recordsTable->buildTimelineJson($neatline);

        // Check format.
        $this->assertContains('"dateTimeFormat":"iso8601"', $json);
        $this->assertContains('"events":[]', $json);

    }

    /**
     * The buildTimelineJson() method should default in default DC values when there
     * are not record-specific values.
     *
     * @return void.
     */
    public function testBuildTimelineJsonDcDefaults()
    {

        // Create an exhibit and item.
        $exhibit = $this->_createNeatline();
        $item = $this->_createItem();

        // Create title and date element texts.
        $this->_createElementText(
            $item,
            'Dublin Core',
            'Title',
            'Test Title');

        $this->_createElementText(
            $item,
            'Dublin Core',
            'Date',
            '1564-04-26 14:39:22/1616-04-23 12:45:34');

        // Create record.
        $record = new NeatlineDataRecord($item, $exhibit);
        $record->time_active = 1;
        $record->save();

        // Build the JSON.
        $json = $this->_recordsTable->buildTimelineJson($exhibit);

        $this->assertContains(
            '"title":"Test Title"',
            $json
        );

        $this->assertContains(
            '"start":"1564-04-26 14:39:22"',
            $json
        );

        $this->assertContains(
            '"end":"1616-04-23 12:45:34"',
            $json
        );


    }

    /**
     * The buildItemsJson() method should construct well-formed JSON string with
     * the id, title, slug, description, and visibility dates.
     *
     * @return void.
     */
    public function testBuildItemsJson()
    {

        // Create an exhibit and items.
        $neatline = $this->_createNeatline();
        $item1 = $this->_createItem();
        $item2 = $this->_createItem();

        // Create two records.
        $record1 = new NeatlineDataRecord($item1, $neatline);
        $record2 = new NeatlineDataRecord($item2, $neatline);

        // Populate map-relevant attributes.
        $record1->title = 'Item 1 Title';
        $record2->title = 'Item 2 Title';
        $record1->slug = 'slug-1';
        $record2->slug = 'slug-2';
        $record1->description = 'Item 1 description.';
        $record2->description = 'Item 2 description.';
        $record1->start_visible_date = '1864-04-26 14:39:22';
        $record2->start_visible_date = '1964-04-26 14:39:22';
        $record1->end_visible_date = '1916-04-23 12:45:34';
        $record2->end_visible_date = '2016-04-23 12:45:34';
        $record1->items_active = 1;
        $record2->items_active = 1;
        $record1->save();
        $record2->save();

        // Build the JSON.
        $json = json_decode($this->_recordsTable->buildItemsJson($neatline));

        $this->assertEquals(
            $json,
            array(
                (object) array(
                    'id' => $record1->id,
                    'title' => 'Item 1 Title',
                    'slug' => 'slug-1',
                    'description' => 'Item 1 description.',
                    'start_visible_date' => '1864-04-26 14:39:22',
                    'end_visible_date' => '1916-04-23 12:45:34'
                ),
                (object) array(
                    'id' => $record2->id,
                    'title' => 'Item 2 Title',
                    'slug' => 'slug-2',
                    'description' => 'Item 2 description.',
                    'start_visible_date' => '1964-04-26 14:39:22',
                    'end_visible_date' => '2016-04-23 12:45:34'
                )
            )
        );

    }

    /**
     * The saveOrder() method should commit a new record ordering.
     *
     * @return void.
     */
    public function testSaveOrder()
    {

        // Create an exhibit, items, and records.
        $neatline = $this->_createNeatline();
        $item1 = $this->_createItem();
        $item2 = $this->_createItem();
        $item3 = $this->_createItem();
        $item4 = $this->_createItem();
        $record1 = new NeatlineDataRecord($item1, $neatline);
        $record2 = new NeatlineDataRecord($item2, $neatline);
        $record3 = new NeatlineDataRecord($item3, $neatline);
        $record4 = new NeatlineDataRecord($item4, $neatline);
        $record1->items_active = 1;
        $record2->items_active = 1;
        $record3->items_active = 1;
        $record4->items_active = 1;
        $record1->save();
        $record2->save();
        $record3->save();
        $record4->save();

        // By default, the display_order columns should be null.
        $this->assertNull($record1->display_order);
        $this->assertNull($record2->display_order);
        $this->assertNull($record3->display_order);
        $this->assertNull($record4->display_order);

        $order = array(
            $record1->id => 3,
            $record2->id => 2,
            $record3->id => 1,
            $record4->id => 0
        );

        // Push a new order, reget the items.
        $this->_recordsTable->saveOrder($neatline, $order);
        $record1 = $this->_recordsTable->getRecordByItemAndExhibit($item1, $neatline);
        $record2 = $this->_recordsTable->getRecordByItemAndExhibit($item2, $neatline);
        $record3 = $this->_recordsTable->getRecordByItemAndExhibit($item3, $neatline);
        $record4 = $this->_recordsTable->getRecordByItemAndExhibit($item4, $neatline);

        // Check the values.
        $this->assertEquals($record1->display_order, 3);
        $this->assertEquals($record2->display_order, 2);
        $this->assertEquals($record3->display_order, 1);
        $this->assertEquals($record4->display_order, 0);

    }

}
