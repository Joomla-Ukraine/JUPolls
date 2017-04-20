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

class plgSearchMijopollssearch extends JPlugin
{

    function __construct(& $subject, $config)
    {
        parent::__construct($subject, $config);
    }

    function onContentSearchAreas()
    {
        static $areas = array('mijopolls' => 'Polls');

        return $areas;
    }

    function onContentSearch($text, $phrase = '', $ordering = '', $areas = null)
    {
        if(is_array($areas))
        {
            if(!array_intersect($areas, array_keys(self::onContentSearchAreas())))
            {
                return array();
            }
        }

        $limit = $this->params->get('search_limit', 50);

        $text = trim($text);
        if($text == '')
        {
            return array();
        }

        $text = $db->Quote('%' . $db->getEscaped($text, true) . '%', false);

        $db    = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('id, title, alias, publish_up AS created');
        $query->from('#__mijopolls_polls');
        $query->where('(title LIKE ' . $text . ' OR alias LIKE ' . $text . ')');
        $query->where('published = 1');
        $query->group('id');
        $query->order('title');
        $db->setQuery($query, 0, $limit);
        /*
        $query	= 'SELECT id, title, alias, publish_up AS created'
        . ' FROM #__mijopolls_polls'
        . ' WHERE (title LIKE '.$text.' OR alias LIKE '.$text.') AND published = 1'
        . ' GROUP BY id'
        . ' ORDER BY title'
        ;
        $db->setQuery($query, 0, $limit);
        */
        $rows = $db->loadObjectList();

        if(empty($rows))
        {
            return array();
        }

        foreach ($rows as $key => $row)
        {
            $rows[$key]->href = 'index.php?option=com_mijopolls&amp;view=poll&amp;id=' . $row->id . ":" . $row->alias . self::getItemid($row->id);
        }

        return $rows;
    }

    function getItemid($poll_id)
    {
        $component = JComponentHelper::getComponent('com_mijopolls');
        $menus     = JApplication::getMenu('site', array());
        $items     = $menus->getItems('component_id', $component->id);
        $match     = false;
        $item_id   = '';

        if(isset($items))
        {
            foreach ($items as $item)
            {
                if((@$item->query['view'] == 'poll') && (@$item->query['id'] == $poll_id))
                {
                    $itemid = $item->id;
                    $match  = true;
                    break;
                }
            }
        }

        if($match)
        {
            $item_id = '&Itemid=' . $itemid;
        }

        return $item_id;
    }
}