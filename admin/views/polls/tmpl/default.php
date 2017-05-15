<?php
/**
 * JUPolls
 *
 * @package          Joomla.Site
 * @subpackage       com_jupolls
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

?>

<form action="index.php?option=com_jupolls&amp;controller=polls&amp;task=view" method="post" name="adminForm"
      id="adminForm">
    <div id="filter-bar" class="btn-toolbar">
        <div class="filter-search btn-group pull-left">
            <label for="filter_search" class="element-invisible"><?php echo JText::_('Search in title'); ?></label>
            <input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_(' '); ?>"
                   value="<?php echo $this->lists['search']; ?>" title="<?php echo JText::_('Search in title'); ?>"/>
        </div>
        <div class="btn-group pull-left">
            <button type="submit" class="btn hasTooltip" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i
                        class="icon-search"></i></button>
            <button type="button" class="btn hasTooltip" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"
                    onclick="document.id('filter_search').value='';this.form.submit();"><i class="icon-remove"></i>
            </button>
        </div>
        <div class="btn-group pull-right">
            <?php echo $this->lists['state']; ?>
        </div>
    </div>

    <table class="adminlist table table-striped">
        <thead>
        <tr>
            <th width="5">
                <?php echo JText::_('#'); ?>
            </th>
            <th width="20">
                <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);"/>
            </th>
            <th class="title">
                <?php echo JHTML::_('grid.sort', JText::_('COM_JUPOLLS_TITLE'), 'm.title', @$this->lists['order_Dir'], @$this->lists['order']); ?>
            </th>
            <th width="5%" align="center">
                <?php echo JHTML::_('grid.sort', JText::_('COM_JUPOLLS_PUBLISHED'), 'm.published', @$this->lists['order_Dir'], @$this->lists['order']); ?>
            </th>
            <th width="10%" align="center">
                <?php echo JHTML::_('grid.sort', JText::_('COM_JUPOLLS_START_DATE'), 'm.publish_up', @$this->lists['order_Dir'], @$this->lists['order']); ?>
            </th>
            <th width="10%" align="center">
                <?php echo JHTML::_('grid.sort', JText::_('COM_JUPOLLS_END_DATE'), 'publish_down', @$this->lists['order_Dir'], @$this->lists['order']); ?>
            </th>
            <th width="5%" align="center">
                <?php echo JHTML::_('grid.sort', JText::_('COM_JUPOLLS_VOTES'), 'votes', @$this->lists['order_Dir'], @$this->lists['order']); ?>
            </th>
            <th width="5%" align="center">
                <?php echo JHTML::_('grid.sort', JText::_('COM_JUPOLLS_OPTIONS'), 'numoptions', @$this->lists['order_Dir'], @$this->lists['order']); ?>
            </th>
            <th width="5%" align="center">
                <?php echo JHTML::_('grid.sort', JText::_('COM_JUPOLLS_LAG'), 'm.lag', @$this->lists['order_Dir'], @$this->lists['order']); ?>
            </th>
            <th width="1%" nowrap="nowrap">
                <?php echo JHTML::_('grid.sort', JText::_('ID'), 'm.id', @$this->lists['order_Dir'], @$this->lists['order']); ?>
            </th>
        </tr>
        </thead>
        <tbody>
        <?php
        $k = 0;
        $n = count($this->items);

        for ($i = 0; $i < $n; $i++)
        {
            $row = $this->items[$i];

            $link = JRoute::_('index.php?option=com_jupolls&controller=poll&task=edit&cid[]=' . $row->id);

            $checked   = JHTML::_('grid.checkedout', $row, $i);
            $published = JHTML::_('grid.published', $row, $i);
            ?>
            <tr class="<?php echo "row$k"; ?>">
                <td>
                    <?php echo $this->pagination->getRowOffset($i); ?>
                </td>
                <td>
                    <?php echo $checked; ?>
                </td>
                <td>
                    <?php if(JTable::isCheckedOut($this->user->get('id'), $row->checked_out))
                    {
                        echo $row->title;
                    }
                    else
                    {
                        ?>
                        <span class="editlinktip hasTip"
                              title="<?php echo JText::_('COM_JUPOLLS_EDIT_POLL'); ?>::<?php echo $row->title; ?>">
					<a href="<?php echo $link; ?>">
						<?php echo $row->title; ?></a></span>
                        <?php
                    }
                    ?>
                </td>
                <td align="center">
                    <?php echo $published; ?>
                </td>
                <td align="center">
                    <?php echo $row->publish_up; ?>
                </td>
                <td align="center">
                    <?php echo $row->publish_down; ?>
                </td>
                <td align="center">
                    <a href="index.php?option=com_jupolls&controller=votes&task=view&total=<?php echo $row->votes; ?>&id=<?php echo $row->id; ?>"><?php echo $row->votes; ?></a>
                </td>
                <td align="center">
                    <?php echo $row->options; ?>
                </td>
                <td align="center">
                    <?php echo $row->lag / 60; ?>
                </td>
                <td align="center">
                    <?php echo $row->id; ?>
                </td>
            </tr>
            <?php
            $k = 1 - $k;
        }
        ?>
        </tbody>
        <tfoot>
        <tr>
            <td colspan="10">
                <?php echo $this->pagination->getListFooter(); ?>
            </td>
        </tr>
        </tfoot>
    </table>

    <input type="hidden" name="option" value="com_jupolls"/>
    <input type="hidden" name="task" value="view"/>
    <input type="hidden" name="boxchecked" value="0"/>
    <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>"/>
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>"/>
    <?php echo JHTML::_('form.token'); ?>
</form>