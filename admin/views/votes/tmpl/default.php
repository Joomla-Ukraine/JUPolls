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
?>

<form action="index.php?option=com_mijopolls&amp;controller=votes&amp;task=view" method="post" name="adminForm"
      id="adminForm">
    <table>
        <tr>
            <td align="left" width="100%">
                <?php echo JText::_('COM_MIJOPOLLS_FILTER_USERS'); ?>:
                <input type="text" name="search" id="search" value="<?php echo $this->lists['search']; ?>"
                       class="text_area" onchange="document.adminForm.submit();"/>
                <button onclick="this.form.submit();"><?php echo JText::_('COM_MIJOPOLLS_GO'); ?></button>
                <button onclick="document.getElementById('search').value='';this.form.getElementById('filter_state').value='';this.form.submit();"><?php echo JText::_('COM_MIJOPOLLS_RESET'); ?></button>
            </td>
            <td>
                <?php echo JText::_('COM_MIJOPOLLS_VIEW_RESULTS_FOR') . ':'; ?>
                <?php echo $this->lists['polls']; ?>
            </td>
        </tr>
    </table>

    <table class="adminlist table table-striped" align="center" width="90%" cellspacing="2" cellpadding="2" border="0">
        <thead>
        <tr>
            <th width="1%"><?php echo JText::_('#'); ?></th>
            <th width="1%"><input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);"/></th>
            <th width="80%"><?php echo JHTML::_('grid.sort', JText::_('COM_MIJOPOLLS_OPTION'), 'o.text', @$this->lists['order_Dir'], @$this->lists['order']); ?></th>
            <th width="18%"><?php echo JHTML::_('grid.sort', JText::_('COM_MIJOPOLLS_DATE'), 'v.date', @$this->lists['order_Dir'], @$this->lists['order']); ?></th>
            <th width="18%"><?php echo JHTML::_('grid.sort', JText::_('COM_MIJOPOLLS_USER'), 'u.name', @$this->lists['order_Dir'], @$this->lists['order']); ?></th>
            <th><?php echo JHTML::_('grid.sort', JText::_('IP'), 'ip', @$this->lists['order_Dir'], @$this->lists['order']); ?></th>
            <th><?php echo JHTML::_('grid.sort', JText::_('HASH'), 'browser', @$this->lists['order_Dir'], @$this->lists['order']); ?></th>

        </tr>
        </thead>
        <?php
        $i = 0;
        foreach ($this->votes as $vote)
        {
            $checkBox = JHTML::_('grid.id', $i++, $vote->id);
            ?>
            <tr class="row<?php echo $i % 2; ?>">
                <td valign="top" height="30"><?php echo $i; ?></td>
                <td valign="top"><?php echo $checkBox; ?></td>
                <td valign="top">
                    <?php
                    $poll = explode("===", $vote->text);
                    echo strip_tags(html_entity_decode(trim($poll[0])));
                    ?>
                </td>
                <td valign="top"><?php echo $vote->date; ?></td>
                <td valign="top"><?php echo $vote->name; ?></td>
                <td valign="top"><?php echo $vote->ip; ?></td>
                <td valign="top"><?php echo $vote->browser; ?></td>

            </tr>

        <?php } ?>
        <tfoot>
        <tr>
            <td colspan="6">
                <?php echo $this->pagination->getListFooter(); ?>
            </td>
        </tr>
        </tfoot>
    </table>

    <input type="hidden" name="option" value="com_mijopolls"/>
    <input type="hidden" name="task" value="view"/>
    <input type="hidden" name="boxchecked" value="0"/>
    <input type="hidden" name="poll_id" value="<?php echo $this->poll_id; ?>"/>
    <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>"/>
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>"/>
    <?php echo JHTML::_('form.token'); ?>
</form>