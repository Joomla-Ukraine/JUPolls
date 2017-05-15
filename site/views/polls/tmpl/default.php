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

defined('_JEXEC') or die('Restricted access'); ?>

<h1 class="head page-header">
    <?php echo $this->params->get('page_title'); ?>
</h1>

<form action="<?php echo JRoute::_(JFactory::getURI()->toString()); ?>" method="post" name="adminForm">
    <?php if($this->params->get('show_filter_box')) : ?>
        <div id="filter-bar" class="container-fluid row">
            <div class="btn-toolbar">
                <div class="col-md-6 span6">
                    <div class="btn-group filter-search input-group pull-left ">
                        <label for="filter_search" class="element-invisible input-group-addon">
                            <?php echo JText::_('Search in title'); ?>
                        </label>
                        <input type="text" name="filter_search" id="filter_search" class="form-control"
                               placeholder="<?php echo JText::_(' '); ?>"
                               value="<?php echo $this->lists['search']; ?>"/>
                    </div>
                </div>
                <div class="col-md-4 span4">
                    <div class="btn-group pull-left input-group ">
                        <button type="submit" class="btn btn-default"><i class="icon-search"></i><i
                                    class="fa fa-search"></i></button>
                        <button type="button" class="btn btn-default"
                                onclick="document.id('filter_search').value='';this.form.submit();"><i
                                    class="icon-remove"></i><i class="fa fa-close-md"></i></button>
                    </div>
                </div>
                <div class="pull-right">
                    <?php echo str_replace('class="', 'class="form-control ', $this->pagination->getLimitBox()); ?>
                </div>
            </div>
        </div>
        <hr/>
    <?php endif; ?>
    <table class="table table-striped">
        <thead>
        <tr>
            <th>
                <?php echo JText::_('COM_JUPOLLS_TITLE'); ?>
            </th>
            <?php if($this->params->get('show_start_date')) : ?>
                <th width="18%">
                    <?php echo JText::_('COM_JUPOLLS_START'); ?>
                </th>
            <?php endif; ?>
            <?php if($this->params->get('show_end_date')) : ?>
                <th width="18%">
                    <?php echo JText::_('COM_JUPOLLS_END'); ?>
                </th>
            <?php endif; ?>
            <?php if($this->params->get('show_status')) : ?>
                <th width="5%">
                    <?php echo JText::_('COM_JUPOLLS_STATUS'); ?>
                </th>
            <?php endif; ?>
            <?php if($this->params->get('show_num_voters')) : ?>
                <th width="5%">
                    <?php echo JText::_('COM_JUPOLLS_VOTES'); ?>
                </th>
            <?php endif; ?>
            <?php if($this->params->get('show_num_options')): ?>
                <th width="5%">
                    <?php echo JText::_('COM_JUPOLLS_OPTIONS'); ?>
                </th>
            <?php endif; ?>
        </tr>
        </thead>
        <tbody>
        <?php
        $k = 0;
        $n = count($this->items);
        for ($i = 0; $i < $n; $i++)
        {
            $row = $this->items[$i];

            $component  = JComponentHelper::getComponent('com_jupolls');
            $menus      = JApplication::getMenu('site', array());
            $menu_items = $menus->getItems('component_id', $component->id);
            $itemid     = null;

            if(isset($menu_items))
            {
                foreach ($menu_items as $item)
                {
                    if((@$item->query['view'] == 'poll') && (@$item->query['id'] == $row->id))
                    {
                        $itemid = '&Itemid=' . $item->id;

                        break;
                    }
                }
            }

            $link = JRoute::_('index.php?option=com_jupolls&view=poll&id=' . $row->slug . $itemid);
            ?>
            <tr>
                <td>
                    <a href="<?php echo $link; ?>"><?php echo $row->title; ?></a>
                </td>
                <?php if($this->params->get('show_start_date')) : ?>
                    <td>
                        <?php echo JHtml::date($row->publish_up, $this->params->get('date_format')); ?>
                    </td>
                <?php endif; ?>
                <?php if($this->params->get('show_end_date')) : ?>
                    <td>
                        <?php echo JHtml::date($row->publish_down, $this->params->get('date_format')); ?>
                    </td>
                <?php endif; ?>
                <?php if($this->params->get('show_status')) : ?>
                    <td>
                        <?php if($this->params->get('show_status_as')) : ?>
                            <img src="<?php echo JURI::base(); ?>media/jupolls/images/poll-<?php echo $row->status; ?>.gif"/>
                        <?php else: ?>
                            <?php echo JText::_('COM_JUPOLLS_' . $row->status); ?>
                        <?php endif; ?>
                    </td>
                <?php endif; ?>
                <?php if($this->params->get('show_num_voters')) : ?>
                    <td>
                        <span class="label label-primary"><?php echo $row->voters; ?></span>
                    </td>
                <?php endif; ?>
                <?php if($this->params->get('show_num_options')) : ?>
                    <td>
                        <span class="label label-info"><?php echo $row->numoptions; ?></span>
                    </td>
                <?php endif; ?>
            </tr>
            <?php $k = 1 - $k;
        }
        ?>
        </tbody>
    </table>

    <?php echo $this->pagination->getPagesLinks(); ?>
    <div class="pagecounter">
        <?php echo $this->pagination->getPagesCounter(); ?>
    </div>

    <input type="hidden" name="option" value="com_jupolls"/>
    <input type="hidden" name="view" value="polls"/>
    <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>"/>
    <input type="hidden" name="filter_order_Dir" value=""/>
    <?php echo JHTML::_('form.token'); ?>
</form>