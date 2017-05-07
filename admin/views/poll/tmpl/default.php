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
JHtml::_('jquery.framework');

$doc = JFactory::getDocument();
$doc->addScript('../media/mijopolls/js/jquery.tablednd.js');
$doc->addScript('../media/mijopolls/js/jquery.mijopolls.js');


$row = $this->row;
JFilterOutput::objectHTMLSafe($row, ENT_QUOTES);

?>

<form action="<?php echo JRoute::_('index.php?option=com_mijopolls&controller=poll&layout=edit&id=' . (int) $row->id); ?>"
      method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal">

    <div class="span10">

        <ul class="nav nav-tabs">
            <li class="active">
                <a href="#details" data-toggle="tab"><?php echo JText::_('COM_MIJOPOLLS_DETAILS'); ?></a>
            </li>
            <li>
                <a href="#general" data-toggle="tab"><?php echo JText::_('COM_MIJOPOLLS_PARAMS_GENERAL'); ?></a>
            </li>
            <li>
                <a href="#results" data-toggle="tab"><?php echo JText::_('COM_MIJOPOLLS_RESULTS'); ?></a>
            </li>
        </ul>

        <div class="tab-content">

            <div class="tab-pane active" id="details">

                <div class="row-fluid">
                    <div class="span8">
                        <div class="control-group">
                            <label class="control-label" for="title">
                                <?php echo JText::_('COM_MIJOPOLLS_TITLE'); ?>
                            </label>
                            <div class="controls">
                                <input class="span8" type="text" name="title" id="title"
                                       value="<?php echo $row->title; ?>">
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="alias">
                                <?php echo JText::_('COM_MIJOPOLLS_ALIAS'); ?>
                            </label>
                            <div class="controls">
                                <input class="span8" type="text" name="alias" id="alias"
                                       value="<?php echo $row->alias; ?>">
                            </div>
                        </div>

                        <?php foreach ($this->params->getFieldset('image') as $fimage): ?>
                            <div class="control-group">
                                <label class="control-label" for="alias">
                                    <?php echo $fimage->label; ?>
                                </label>
                                <div class="controls">
                                    <?php echo $fimage->input; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="span4">
                        <div class="control-group">
                            <label class="control-label" for="lag">
                                <?php echo JText::_('COM_MIJOPOLLS_LAG'); ?>
                            </label>
                            <div class="controls">
                                <input class="span2" type="text" name="lag" id="lag"
                                       value="<?php echo $row->lag / 60; ?>">
                                <span class="help-inline muted"><?php echo JText::_('COM_MIJOPOLLS_HOURS_BETWEEN_VOTES'); ?></span>
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label" for="start">
                                <?php echo JText::_('COM_MIJOPOLLS_START_DATE'); ?><br>
                                <span class="muted"><?php echo JText::_('COM_MIJOPOLLS_START_DATE_DESC'); ?></span>
                            </label>
                            <div class="controls">
                                <?php
                                $date       = JFactory::getDate();
                                $end_date   = JFactory::getDate('+1 month');
                                $publish_up = ($row->publish_up == '') ? $date->toSql() : $row->publish_up;

                                echo JHTML::_(
                                    'calendar',
                                    $publish_up,
                                    'publish_up',
                                    'publish_up',
                                    '%Y-%m-%d 00:00:00',
                                    array(
                                        'class' => 'span9'
                                    )
                                );
                                ?>
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label" for="end">
                                <?php echo JText::_('COM_MIJOPOLLS_END_DATE'); ?><br>
                                <span class="muted"><?php echo JText::_('COM_MIJOPOLLS_END_DATE_DESC'); ?></span>
                            </label>
                            <div class="controls">
                                <?php
                                $publish_down = ($row->publish_down == '') ? $end_date->toSql() : $row->publish_down;

                                echo JHTML::_(
                                    'calendar',
                                    $publish_down,
                                    'publish_down',
                                    'publish_down',
                                    '%Y-%m-%d 00:00:00',
                                    array(
                                        'class' => 'span9'
                                    )
                                );
                                ?>
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label">
                                <?php echo JText::_('COM_MIJOPOLLS_PUBLISHED'); ?>
                            </label>
                            <?php echo JHTML::_('select.booleanlist', 'published', 'class="radio inline"', $row->published); ?>
                        </div>
                    </div>
                </div>

                <fieldset class="adminform">
                    <legend><?php echo JText::_('COM_MIJOPOLLS_OPTIONS_DRAG_DROP'); ?></legend>

                    <table class="admintable" id="reorder" style="width:90%;">
                        <tr style=" font-weight:bold;" class="nodrag">
                            <td style="width:40px;">
                                <a href="#" id="options-add<?php if($this->edit) echo '-extra'; ?>">
                                    <img src="../media/mijopolls/images/poll-add.png"
                                         alt="<?php echo JText::_('COM_MIJOPOLLS_OPTION_ADD'); ?>""/>
                                </a>
                                <a href="#" id="options-remove<?php if($this->edit) echo '-extra'; ?>">
                                    <img src="../media/mijopolls/images/poll-remove.png"
                                         alt="<?php echo JText::_('COM_MIJOPOLLS_OPTION_REMOVE'); ?>"/>
                                </a>
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
                                    <textarea class="inputbox checkit span10"
                                              type="text"
                                              name="polloption[<?php echo $this->options[$i]->id; ?>]"
                                              id="polloption<?php echo $this->options[$i]->id; ?>"
                                              rows="3"><?php echo $this->options[$i]->text; ?></textarea>

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
                                    <textarea class="inputbox checkit span10"
                                              type="text" name="polloption[]"
                                              id="polloption<?php echo $i + 1; ?>" rows="3"></textarea>

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
                        <a href="#" id="options-reset" class="btn btn-warning">
                            <?php echo JText::_('COM_MIJOPOLLS_RESET_VOTES'); ?>
                        </a>
                        <span style="color:red; display:none;">
                            <?php echo JText::_('COM_MIJOPOLLS_RESET_VOTES_DESC'); ?>
                        </span>
                    </div>
                    <?php
                }
                ?>

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

        <input type="hidden" name="option" value="com_mijopolls"/>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" id="reset" name="reset" value="0"/>
        <input type="hidden" id="is_there_extra" name="is_there_extra" value="0"/>
        <input type="hidden" name="id" value="<?php echo $row->id; ?>"/>
        <input type="hidden" name="cid[]" value="<?php echo $row->id; ?>"/>
        <?php echo JHTML::_('form.token'); ?>
</form>