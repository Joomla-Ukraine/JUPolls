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

$lang_file = JFactory::getLanguage();
$lang_file->load('com_mijopolls', JPATH_SITE);

require_once(__DIR__ . '/helper.php');

$menu    = JSite::getMenu();
$items   = $menu->getItems('link', 'index.php?option=com_mijopolls&view=poll');
$itemid  = isset($items[0]) ? '&Itemid=' . $items[0]->id : '';
$details = "";

$poll_id = $params->get('id');

if(!$poll_id)
{
    $ids = modMijopollsHelper::getActivePolls();

    if(count($ids) > 1)
    {
        $poll_id = $ids[array_rand($ids)];
    }
    else
    {
        $poll_id = $ids;
    }
}

if($poll_id > 0)
{
    $results = modMijopollsHelper::getResults($poll_id);
}
else
{
    echo '<div class="panel panel-default panel-flat"><div class="panel-body"><b class="text-grey">Опитування відсутні!</b><br><a href="/polls">Переглянути архів »»</a></div></div>';

    return;
}

JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_mijopolls/tables');
$poll = JTable::getInstance('Poll', 'Table');

if(!$poll->load($poll_id)) return;

$pollParams = new JRegistry($poll->params);
$params     = clone($params);
$params->merge($pollParams);

$slug = ($poll->alias == '') ? $poll->id : $poll->id . ":" . $poll->alias;

$voted = modMijopollsHelper::alreadyVoted($poll_id);

$user      = JFactory::getUser();
$userVoted = modMijopollsHelper::userVoted($user->id, $poll_id);
$guest     = $user->guest;

$ipVoted = modMijopollsHelper::ipVoted($poll_id);

$display_poll = 0;

$mainframe = JFactory::getApplication();
$date      = JFactory::getDate();

$now          = JHtml::date($date->toSql(), 'Y-m-d H:i:s');
$now          = strtotime($now);
$publish_up   = strtotime($poll->publish_up);
$publish_down = strtotime($poll->publish_down);

if(($now > $publish_up) && ($now < $publish_down))
{
    $display_submit = 1;

    // if only registered users can vote
    if($params->get('only_registered'))
    {
        //if the user is not a guest
        if(!$guest)
        {
            //if only one vote is allowed per logged user
            if($params->get('one_vote_per_user'))
            {
                //check if user has voted
                if($userVoted)
                {
                    //display the poll with disabled options
                    $display_submit = 0;
                    $msg            = JText::_("MOD_MIJOPOLLS_ALREADY_VOTED");
                    $details        = JText::_("MOD_MIJOPOLLS_ONLY_ONE_VOTE_PER_USER");
                    //user has not voted yet
                }
                else
                {
                    //display the poll
                    $display_poll   = 1;
                    $display_submit = 1;
                    $msg            = "";
                }
                // if loggedin user are allowed to vote unlimited times
            }
            else
            {
                // Check the cookie
                if($voted)
                {
                    $display_poll   = 0;
                    $display_submit = 0;
                    $msg            = JText::_("MOD_MIJOPOLLS_ALREADY_VOTED");
                    $details        = JText::sprintf("MOD_MIJOPOLLS_ONLY_ONE_VOTE_PER_HOUR", $poll->lag / 60);

                    //hm check the ip please but only if allowed to do that
                }
                elseif($params->get('ip_check'))
                {
                    if($ipVoted)
                    {
                        //display the poll with disabled options
                        $display_poll   = 0;
                        $display_submit = 0;
                        $msg            = JText::_("MOD_MIJOPOLLS_ALREADY_VOTED");
                        $details        = JText::_("MOD_MIJOPOLLS_ONLY_ONE_VOTE_PER_IP");
                        //if user's ip has not been logged
                    }
                    //if user has not voted
                }
                else
                {
                    //display the poll
                    $display_poll   = 1;
                    $display_submit = 1;
                    $msg            = "";
                }
            }
            //if the user has not logged in
        }
        else
        {
            $display_poll   = 1;
            $display_submit = 0;

            $return = JRequest::getURI();
            $return = base64_encode($return);
            $link   = 'index.php?option=com_users&view=login&return=' . $return;

            $msg = JText::sprintf('MOD_MIJOPOLLS_PLEASE_REGISTER_TO_VOTE', '<a href="' . $link . '">', '</a>');
        }
    }
    else
    {
        if($voted)
        {
            $display_poll   = 0;
            $display_submit = 0;
            $msg            = JText::_("MOD_MIJOPOLLS_ALREADY_VOTED");
            $details        = JText::sprintf("MOD_MIJOPOLLS_ONLY_ONE_VOTE_PER_HOUR", $poll->lag / 60);
        }
        else
        {
            if($params->get('ip_check'))
            {
                if($ipVoted)
                {
                    $display_poll   = 0;
                    $display_submit = 0;
                    $msg            = JText::_("MOD_MIJOPOLLS_ALREADY_VOTED");
                    $details        = JText::_("MOD_MIJOPOLLS_ONLY_ONE_VOTE_PER_IP");
                }
                else
                {
                    $display_poll   = 1;
                    $display_submit = 1;
                    $msg            = "";
                }
            }
            else
            {
                $display_poll   = 1;
                $display_submit = 1;
                $msg            = "";
            }
        }
    }
}
else
{
    $display_submit = 0;
    $msg            = JText::_("MOD_MIJOPOLLS_VOTING_HAS_NOT_STARTED");
    $publish_up     = JFactory::getDate($poll->publish_up);
    $details        = JText::_("MOD_MIJOPOLLS_IT_WILL_START_ON") . ": " . $publish_up->format($params->get('msg_date_format'));
}

if($now > $publish_down)
{
    $display_poll = 0;
    $msg          = JText::_("MOD_MIJOPOLLS_VOTING_HAS_ENDED");
    $publish_down = JFactory::getDate($poll->publish_down);
    $details      = JText::_("MOD_MIJOPOLLS_ON") . ": " . $publish_down->format($params->get('msg_date_format'));
}

$disabled = ($display_submit) ? '' : 'disabled="disabled"';


if($poll && $poll->id)
{
    $layout  = JModuleHelper::getLayoutPath('mod_mijopolls');
    $tabcnt  = 0;
    $options = modMijopollsHelper::getPollOptions($poll_id);
    $itemid  = modMijopollsHelper::getItemid($poll_id);
    require($layout);
}