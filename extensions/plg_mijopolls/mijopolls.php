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

jimport('joomla.html.parameter');

class plgContentMijopolls extends JPlugin
{

    function onContentPrepare($context, &$row, &$params, $limitstart)
    {
        $regex = '/{mijopolls\s*.*?}/i';
        preg_match_all($regex, $row->text, $matches);
        $count = count($matches[0]);

        // plugin only processes if there are any instances of the plugin in the text
        if($count)
        {
            self::_processMatches($row, $matches, $count, $regex);
        }

        return true;
    }

    function _processMatches(&$row, &$matches, $count, $regex)
    {
        for ($i = 0; $i < $count; $i++)
        {
            $id = str_replace('mijopolls', '', $matches[0][$i]);
            $id = str_replace('{', '', $id);
            $id = str_replace('}', '', $id);
            $id = trim($id);

            $module  = JModuleHelper::getModule('mod_mijopolls');
            $content = self::_renderModule($module, array(), $id);

            $row->introtext = str_replace($matches[0][$i], $content, $row->introtext);
            $row->fulltext = str_replace($matches[0][$i], $content, $row->fulltext);
            $row->text = str_replace($matches[0][$i], $content, $row->text);
        }

        $row->introtext = preg_replace($regex, '', $row->introtext);
        $row->fulltext = preg_replace($regex, '', $row->fulltext);
        $row->text = preg_replace($regex, '', $row->text);
    }

    function _renderModule($module, $attribs = array(), $id)
    {
        static $chrome;
        $mainframe = JFactory::getApplication();
        $option    = JRequest::getCmd('option');

        $scope            = $mainframe->scope; //record the scope
        $mainframe->scope = $module->module;  //set scope to component name

        // Get module parameters
        $params = new JRegistry($module->params);
        $params->set('id', $id);

        // Get module path
        $module->module = preg_replace('/[^A-Z0-9_\.-]/i', '', $module->module);
        $path           = JPATH_BASE . '/modules/' . DS . $module->module . '/' . $module->module . '.php';

        // Load the module
        if(!$module->user && file_exists($path) && empty($module->content))
        {
            $lang = JFactory::getLanguage();
            $lang->load($module->module);

            $content = '';
            ob_start();
            require $path;
            $module->content = ob_get_contents() . $content;
            ob_end_clean();
        }

        // Load the module chrome functions
        if(!$chrome)
        {
            $chrome = array();
        }

        require_once(JPATH_BASE . '/templates/system/html/modules.php');
        $chromePath = JPATH_BASE . '/templates/' . $mainframe->getTemplate() . '/html/modules.php';
        if(!isset($chrome[$chromePath]))
        {
            if(file_exists($chromePath))
            {
                require_once($chromePath);
            }
            $chrome[$chromePath] = true;
        }

        //make sure a style is set
        if(!isset($attribs['style']))
        {
            $attribs['style'] = 'none';
        }

        //dynamically add outline style
        if(JRequest::getBool('tp'))
        {
            $attribs['style'] .= ' outline';
        }

        foreach (explode(' ', $attribs['style']) as $style)
        {
            $chromeMethod = 'modChrome_' . $style;

            // Apply chrome and render module
            if(function_exists($chromeMethod))
            {
                $module->style = $attribs['style'];

                ob_start();
                $chromeMethod($module, $params, $attribs);
                $module->content = ob_get_contents();
                ob_end_clean();
            }
        }

        $mainframe->scope = $scope; //revert the scope

        return $module->content;
    }
}