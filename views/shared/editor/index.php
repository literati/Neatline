<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * The core Neatline editor. Wraps the markup for the editing interface and then
 * calls the central _neatline.php partial, which is shared with public views.
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
?>

<?php
    neatline_queueExhibitCss($neatlineexhibit);
?>

<?php echo $this->partial('editor/_editor_header.php', array(
    'exhibit' => $neatlineexhibit,
    'title' => __('Neatline Editor: %s', $neatlineexhibit->name)
)); ?>

<!-- The top bar. -->
<?php echo $this->partial('editor/_topbar.php', array(
    'neatline' => $neatlineexhibit,
    'layers' => $layers
)); ?>

<!-- The item browser. -->
<?php echo $this->partial('editor/_item_browser.php'); ?>

<!-- The core Neatline partial. -->
<?php echo $this->partial('neatline/_neatline.php', array(
    'exhibit' => $neatlineexhibit
)); ?>

<!-- Templates for tooltips and geometry editing. -->
<?php echo $this->partial('editor/_edit_geometry.php'); ?>

<!-- Custom footer. -->
<?php echo $this->partial('editor/_editor_footer.php'); ?>
