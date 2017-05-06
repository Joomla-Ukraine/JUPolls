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

jimport('joomla.application.component.controller');

class MijopollsController extends JControllerLegacy
{
    /**
     * @param bool $cachable
     * @param bool $urlparams
     *
     * @return JControllerLegacy
     *
     * @since 1.5
     */
    public function display($cachable = false, $urlparams = false)
    {
        $cachable = false;
        $vName    = $this->input->get('view', 'polls');

        $this->input->set('view', $vName);

        return parent::display($cachable, array('Itemid' => 'INT'));
    }

    public function vote()
    {
        JSession::checkToken() or jexit('Invalid Token');

        $app       = JFactory::getApplication();
        $poll_id   = $app->input->getInt('id', 0);
        $option_id = $app->input->getInt('voteid', 0);
        $poll      = JTable::getInstance('Poll', 'Table');

        if(!$poll->load($poll_id) || $poll->published != 1)
        {
            $app->enqueueMessage(JText::_('ALERTNOTAUTH 3'), 'error');

            return;
        }

        $model = $this->getModel('Poll');

        $cookieName   = JUtility::getHash($app->getName() . 'poll' . $poll_id);
        $voted_cookie = JRequest::getVar($cookieName, '0', 'COOKIE', 'INT');
        $voted_ip     = $model->ipVoted($poll, $poll_id);

        $params = new JRegistry($poll->params);

        if($params->get('ip_check') and
            ($voted_cookie or $voted_ip or !$option_id)
        )
        {
            if($voted_cookie || $voted_ip)
            {
                $app->enqueueMessage(JText::_('COM_MIJOPOLLS_ALREADY_VOTED'), 'error');

                return;
            }

            if(!$option_id)
            {
                $app->enqueueMessage(JText::_('COM_MIJOPOLLS_NO_SELECTED'), 'error');

                return;
            }
        }
        else
        {
            if($model->vote($poll_id, $option_id))
            {
                setcookie($cookieName, '1', time() + 60 * $poll->lag);
            }

            if(JFactory::getUser()->id != 0)
            {
                JPluginHelper::importPlugin('mijopolls');
                $dispatcher = JDispatcher::getInstance();
                $dispatcher->trigger('onAfterVote', array($poll, $option_id));
            }
        }

        $menu   = $app->getMenu();
        $items  = $menu->getItems('link', 'index.php?option=com_mijopolls');
        $itemid = isset($items[0]) ? '&Itemid=' . $items[0]->id : '';

        $this->setRedirect(JRoute::_('index.php?option=com_mijopolls&view=poll&id=' . $poll_id . ':' . $poll->alias . $itemid, false));
    }
}