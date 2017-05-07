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

function MijopollsBuildRoute(&$query)
{
    static $items;

    $app = JFactory::getApplication();

    $segments = array();
    $itemid   = null;

    if(isset($query['id']) && strpos($query['id'], ':'))
    {
        list($query['id'], $query['alias']) = explode(':', $query['id'], 2);
    }

    if(!$items)
    {
        $component = JComponentHelper::getComponent('com_mijopolls');
        $menu      = $app->getMenu();
        $items     = $menu->getItems('component_id', $component->id);
    }

    if(is_array($items))
    {
        if(!isset($query['view']) && !isset($query['id']) && isset($query['Itemid']))
        {
            $itemid = (int) $query['Itemid'];
        }

        if(!$itemid)
        {
            foreach ($items as $item)
            {
                if(
                    isset($item->query['view']) && isset($query['view']) &&
                    $item->query['view'] == $query['view'] &&
                    isset($item->query['id']) &&
                    $item->query['id'] == $query['id']
                )
                {
                    $itemid = $item->id;
                }
            }
        }

        if(!$itemid)
        {
            foreach ($items as $item)
            {
                if(isset($query['view']) &&
                    $query['view'] == 'poll' &&
                    isset($item->query['view']) &&
                    $item->query['view'] == 'polls'
                )
                {
                    if(isset($query['id']))
                    {
                        $itemid     = $item->id;
                        $segments[] = isset($query['alias']) ? $query['id'] . ':' . $query['alias'] : $query['id'];
                        break;

                        $url = str_replace('index.php?', '', $item->link);

                        parse_str($url, $vars);
                        JRequest::set($vars, 'get');
                    }
                }
            }
        }
    }

    if(!$itemid)
    {
        if(isset($query['id']))
        {
            if(isset($query['alias'])) $query['id'] .= ':' . $query['alias'];

            $segments[] = 'poll';
            $segments[] = $query['id'];

            unset($query['id']);
            unset($query['alias']);
        }

        unset($query['view']);
    }
    else
    {
        $query['Itemid'] = $itemid;

        unset($query['view']);
        unset($query['id']);
        unset($query['alias']);
    }

    return $segments;
}

function MijopollsParseRoute($segments)
{
    $vars = array();

    $menu = JSite::getMenu();
    $item = $menu->getActive();

    $count = count($segments);

    if(!isset($item))
    {
        $vars['view'] = 'poll';
        $vars['id']   = $segments[$count - 1];

        return $vars;
    }

    $vars['view'] = 'poll';
    $vars['id']   = $segments[$count - 1];
    JRequest::set($vars, 'get');

    return $vars;
}