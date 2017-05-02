<?php
/**
 * JUPolls
 *
 * @package          Joomla.Site
 * @subpackage       com_mijopolls
 *
 * @author           Denys Nosov, denys@joomla-ua.org
 * @copyright        2016-2017 (C) Joomla! Ukraine, http://joomla-ua.org. All rights reserved.
 * @license          GNU General Public License version 2 or later; see LICENSE.txt
 * @license          GNU/GPL based on AcePolls www.joomace.net
 */

/**
 * @copyright      2009-2011 Mijosoft LLC, www.mijosoft.com
 * @license        GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @license        GNU/GPL based on AcePolls www.joomace.net
 *
 * @copyright (C)  2009 - 2011 Hristo Genev All rights reserved
 * @license        http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link           http://www.afactory.org
 */

defined('_JEXEC') or die('Restricted access');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

JHTML::_('behavior.calendar');
$row = $this->row;
JFilterOutput::objectHTMLSafe($row, ENT_QUOTES);

?>

<style>
    tr.dragable {
        cursor: move;
        background-color: #f6f6f6;
    }
</style>

<script>
    function submitbutton(pressbutton) {
        var form = document.adminForm;
        <?php if (count($this->options)) : ?>
        var polloption1 = document.getElementById('polloption<?php echo $this->options[0]->id; ?>');
        var polloption2 = document.getElementById('polloption<?php echo $this->options[1]->id; ?>');
        <?php else : ?>
        var polloption1 = document.getElementById('polloption1');
        var polloption2 = document.getElementById('polloption2');
        <?php endif; ?>

        if (pressbutton == 'cancel') {
            submitform(pressbutton);
            return;
        }

        // do field validation
        // check if the publish_up date is smaller than publish_down date
        var publish_up = form.publish_up.value;
        var publish_down = form.publish_down.value;

        publish_up = parseInt(publish_up.replace(/[ :-]/g, ''));
        publish_down = parseInt(publish_down.replace(/[ :-]/g, ''));

        if (publish_up >= publish_down) {
            alert("<?php echo JText::_('Please correct the start or end date of the poll'); ?>");
            form.publish_down.focus();
            return false;
        }

        // check for empty fields
        if (form.title.value == "") {
            alert("<?php echo JText::_('Poll must have a title', true); ?>");
            form.title.focus();
        }
        else if (isNaN(parseInt(form.lag.value))) {
            alert("<?php echo JText::_('Poll must have a non-zero lag time', true); ?>");
            form.lag.focus();
        }
        else if (polloption1.value == "") {
            alert("<?php echo JText::_('Poll must have at least 2 options', true); ?>");
            polloption1.focus();
        }
        else if (polloption2.value == "") {
            alert("<?php echo JText::_('Poll must have at least 2 options', true); ?>");
            polloption2.focus();
        }
        else {
            submitform(pressbutton);
        }
    }
</script>

<form action="<?php echo JRoute::_('index.php?option=com_mijopolls&controller=poll&layout=edit&id=' . (int) $row->id); ?>"
      method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal">
    <div class="span10 form-horizontal">
        <fieldset>
            <ul class="nav nav-tabs">
                <li class="active"><a href="#details"
                                      data-toggle="tab"><?php echo JText::_('COM_MIJOPOLLS_DETAILS'); ?></a></li>
                <li><a href="#general" data-toggle="tab"><?php echo JText::_('COM_MIJOPOLLS_PARAMS_GENERAL'); ?></a>
                </li>
                <li><a href="#results" data-toggle="tab"><?php echo JText::_('COM_MIJOPOLLS_RESULTS'); ?></a></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane active" id="details">
                    <table class="table table-condensed table-striped">
                        <tr>
                            <td width="110" class="key">
                                <label for="title">
                                    <?php echo JText::_('COM_MIJOPOLLS_TITLE'); ?>:
                                </label>
                            </td>
                            <td>
                                <input class="inputbox" type="text" name="title" id="title" style="width:95%;"
                                       value="<?php echo $row->title; ?>"/>
                            </td>
                        </tr>
                        <tr>
                            <td width="110" class="key">
                                <label for="alias">
                                    <?php echo JText::_('COM_MIJOPOLLS_ALIAS'); ?>:
                                </label>
                            </td>
                            <td width="500">
                                <input class="inputbox" type="text" name="alias" id="alias" size="60"
                                       value="<?php echo $row->alias; ?>"/>
                            </td>
                        </tr>
                        <?php foreach ($this->params->getFieldset('image') as $fimage): ?>
                            <tr>
                                <td width="110" class="key">
                                    <label>
                                        <?php echo $fimage->label; ?>
                                    </label>
                                </td>
                                <td width="500">
                                    <?php echo $fimage->input; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td class="key">
                                <label for="lag">
                                    <?php echo JText::_('COM_MIJOPOLLS_LAG'); ?>:
                                </label>
                            </td>
                            <td width="500">
                                <input class="inputbox" type="text" name="lag" id="lag" size="5"
                                       value="<?php echo $row->lag / 60; ?>"/>
                                <?php echo JText::_('COM_MIJOPOLLS_HOURS_BETWEEN_VOTES'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <label for="start">
                                    <?php echo JText::_('COM_MIJOPOLLS_START_DATE'); ?>:
                                </label>
                            </td>
                            <td width="500">
                                <?php
                                //get the time now if we are creating new poll
                                $date = JFactory::getDate();
                                //$date->setTimezone($this->mainframe->getCfg('offset'));
                                $end_date = JFactory::getDate('+1 month');

                                $publish_up = ($row->publish_up == '') ? $date->toSql() : $row->publish_up;

                                echo JHTML::_('calendar', $publish_up, 'publish_up', 'publish_up', '%Y-%m-%d 00:00:00', array('class' => 'inputbox', 'size' => '30')); ?>
                                <?php echo JText::_('COM_MIJOPOLLS_START_DATE_DESC'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="key">
                                <label for="end">
                                    <?php echo JText::_('COM_MIJOPOLLS_END_DATE'); ?>:
                                </label>
                            </td>
                            <td width="500">
                                <?php
                                $publish_down = ($row->publish_down == '') ? $end_date->toSql() : $row->publish_down;

                                echo JHTML::_('calendar', $publish_down, 'publish_down', 'publish_down', '%Y-%m-%d 00:00:00', array('class' => 'inputbox', 'size' => '30')); ?>
                                <?php echo JText::_('COM_MIJOPOLLS_END_DATE_DESC'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td width="120" class="key">
                                <?php echo JText::_('COM_MIJOPOLLS_PUBLISHED'); ?>:
                            </td>
                            <td>
                                <fieldset class="radio">
                                    <?php echo JHTML::_('select.booleanlist', 'published', 'class="inputbox"', $row->published); ?>
                                </fieldset>
                            </td>
                        </tr>
                    </table>

                    <div class="clr"></div>
                    <div>
                        <?php
                        JHtml::_('jquery.framework');

                        $doc = JFactory::getDocument();
                        $doc->addScript('../media/mijopolls/js/jquery.tablednd.js');
                        $doc->addScript('../media/mijopolls/js/jquery.mijopolls.js');
                        ?>

                        <fieldset class="adminform">
                            <legend><?php echo JText::_('COM_MIJOPOLLS_OPTIONS_DRAG_DROP'); ?></legend>

                            <table class="admintable" id="reorder" style="width:90%;">
                                <tr style=" font-weight:bold;" class="nodrag">
                                    <td style="width:40px;">
                                        <a href="#" id="options-add<?php if($this->edit) echo '-extra'; ?>">
                                            <img src="../media/mijopolls/images/poll-add.png"
                                                 style=" margin-right:3px; border:none;"
                                                 alt="<?php echo JText::_('COM_MIJOPOLLS_OPTION_ADD'); ?>"
                                                 title="<?php echo JText::_('COM_MIJOPOLLS_OPTION_ADD'); ?>"/></a>
                                        <a href="#" id="options-remove<?php if($this->edit) echo '-extra'; ?>">
                                            <img src="../media/mijopolls/images/poll-remove.png"
                                                 style="margin-right:3px; border:none;"
                                                 alt="<?php echo JText::_('COM_MIJOPOLLS_OPTION_REMOVE'); ?>"
                                                 title="<?php echo JText::_('COM_MIJOPOLLS_OPTION_REMOVE'); ?>"/></a>
                                    </td>
                                    <td>
                                        <b><?php echo JText::_('COM_MIJOPOLLS_OPTION'); ?></b>
                                    </td>

                                    <td>
                                        <?php echo JText::_('COM_MIJOPOLLS_VOTES'); ?>
                                    </td>
                                </tr>
                                <?php
                                $n = count($this->options);
                                for ($i = 0; $i < $n; $i++)
                                {
                                    ?>
                                    <tr class="dragable" id="<?php echo $i + 1; ?>">
                                        <td align="center"><b><?php echo $i + 1; ?></b></td>
                                        <td>
                                            <textarea class="inputbox checkit" type="text"
                                                      name="polloption[<?php echo $this->options[$i]->id; ?>]"
                                                      id="polloption<?php echo $this->options[$i]->id; ?>" rows="3"
                                                      style="width:90%;"><?php echo $this->options[$i]->text; ?></textarea>
                                            <input type="hidden" name="ordering[<?php echo $this->options[$i]->id; ?>]"
                                                   id="ordering<?php echo $this->options[$i]->id; ?>"
                                                   value="<?php echo $this->options[$i]->ordering; ?>" size="1"
                                                   class="ordering"/>
                                        </td>
                                        <td align="center">
                                            <div class="vote">
                                                <?php echo $this->options[$i]->hits; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
                                }

                                for (; $i < 2; $i++)
                                { ?>
                                    <tr class="dragable" id="<?php echo $i + 1; ?>">
                                        <td align="center"><b><?php echo $i + 1; ?></b></td>
                                        <td>
                                            <textarea class="inputbox checkit" type="text" name="polloption[]"
                                                      id="polloption<?php echo $i + 1; ?>" rows="3"
                                                      style="width:90%;"></textarea>
                                            <input type="hidden" name="ordering[]" id="ordering<?= $i; ?>"
                                                   value="<?= $i; ?>" class="ordering"/>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </table>
                        </fieldset>

                        <?php
                        if($this->edit)
                        { ?>
                            <hr/>
                            <div id="options-reset-box">
                                <a href="#" id="options-reset" class="btn">
                                    <?php echo JText::_('COM_MIJOPOLLS_RESET_VOTES'); ?></a>
                                <span style="color:red; display:none;">
								<?php echo JText::_('COM_MIJOPOLLS_RESET_VOTES_DESC'); ?></span>
                            </div>
                            <?php
                        }
                        ?>
                    </div>

                    <div class="clr"></div>
                </div>

                <div class="tab-pane" id="general">
                    <?php foreach ($this->params->getFieldset('general') as $field): ?>
                        <div class="control-group">
                            <div class="control-label">
                                <?php echo $field->label; ?>
                            </div>
                            <div class="controls">
                                <?php echo $field->input; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="tab-pane" id="results">
                    <?php foreach ($this->params->getFieldset('results') as $field): ?>
                        <div class="control-group">
                            <div class="control-label">
                                <?php echo $field->label; ?>
                            </div>
                            <div class="controls">
                                <?php echo $field->input; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </fieldset>
    </div>

    <input type="hidden" name="option" value="com_mijopolls"/>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" id="reset" name="reset" value="0"/>
    <input type="hidden" id="is_there_extra" name="is_there_extra" value="0"/>
    <input type="hidden" name="id" value="<?php echo $row->id; ?>"/>
    <input type="hidden" name="cid[]" value="<?php echo $row->id; ?>"/>
    <?php echo JHTML::_('form.token'); ?>
</form>