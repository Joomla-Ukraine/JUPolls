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

$db       = JFactory::getDBO();
$user     = JFactory::getUser();
$date     = JFactory::getDate();
$document = JFactory::getDocument();
$pathway  = $this->mainframe->getPathway();

$poll_id = JRequest::getInt('id', 0);
$params  = $this->params;

$poll = JTable::getInstance('Poll', 'Table');
$poll->load($poll_id);

if($poll->id > 0 && $poll->published != 1)
{
    JError::raiseError(403, JText::_('Access Forbidden'));

    return;
}

$document->setTitle($params->get('page_title'));

$pathway->addItem($poll->title, '');

$params->def('show_page_title', 1);
$params->def('page_title', $poll->title);

if($poll->id > 0)
{
    if(empty($poll->title))
    {
        $poll->id    = 0;
        $poll->title = JText::_('COM_MIJOPOLLS_SELECT_POLL');
    }

    $_db     = JFactory::getDBO();
    $poll_id = $poll->id;

    $query = "SELECT o.*, COUNT(v.id) AS hits,
    	(SELECT COUNT(id) FROM #__mijopolls_votes WHERE poll_id=" . $poll_id . ") AS voters"
        . " FROM #__mijopolls_options AS o"
        . " LEFT JOIN #__mijopolls_votes AS v"
        . " ON (o.id = v.option_id AND v.poll_id = " . $poll_id . ")"
        . " WHERE o.poll_id = " . $poll_id
        . " AND o.text <> ''"
        . " GROUP BY o.id "
        . " ORDER BY o.ordering ";

    $_db->setQuery($query);

    if($votes = $_db->loadObjectList())
    {
        $options = $votes;
    }
}
else
{
    $options = array();
}

//get the number of voters
$voters = isset($options[0]) ? $options[0]->voters : 0;

$num_of_options = count($options);

for ($i = 0; $i < $num_of_options; $i++)
{
    $vote =& $options[$i];

    if($voters > 0)
    {
        $vote->percent = round(100 * $vote->hits / $voters, 1);
    }
    else
    {
        if($params->get('show_what') == 1)
        {
            $vote->percent = round(100 / $num_of_options, 1);
        }
        else
        {
            $vote->percent = 0;
        }
    }

}

$title_lenght = $params->get('title_lenght');

foreach ($options as $vote_array)
{
    if($params->get('show_hits'))
    {
        $hits = " (" . $vote_array->hits . ")";
    }
    else
    {
        $hits = '';
    }

    $poll      = explode("===", $vote_array->text);
    $poll_text = strip_tags(trim($poll[0]));

    if($params->get('show_zero_votes'))
    {
        $text     = JString::substr(html_entity_decode($poll_text, ENT_QUOTES, "utf-8"), 0, $title_lenght) . $hits;
        $values[] = '[\'' . $poll_text . '\', ' . $vote_array->hits . ']';
    }
    else
    {
        if($vote_array->percent)
        {
            $text     = JString::substr(html_entity_decode($poll_text, ENT_QUOTES, "utf-8"), 0, $title_lenght) . $hits;
            $values[] = '[\'' . $poll_text . '\', ' . $vote_array->hits . ']';
        }
    }
}

$json_values = implode(', ', $values) . "\n";

$js = 'google.load("visualization", "1", {packages:["corechart"]});
google.setOnLoadCallback(drawChart);
function drawChart() {
    var data = google.visualization.arrayToDataTable([
        [\'Task\', \'' . $titler . '\'],
        ' . $json_values . '
    ]);

    var options = {
        title: \'\',
        is3D: true,
        width: 728,
        chartArea:{left:5,top:0,width:"728",height:"550"}
    };

    var chart = new google.visualization.PieChart(document.getElementById(\'chart_div\'));
    chart.draw(data, options);
}';

$document = JFactory::getDocument();

$document->addScript('//www.google.com/jsapi');
$document->addScriptDeclaration($js);

?>
<div id="chart_div" style="width: 728px; height: 550px;" class="clear"></div>