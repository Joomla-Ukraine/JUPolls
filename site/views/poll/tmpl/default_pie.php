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

$doc = JFactory::getDocument();

$options = $this->options;
$params  = $this->params;

foreach ($options as $vote_array)
{
    $hits = '(0)';
    if($params->get('show_hits'))
    {
        $hits = " (" . $vote_array->hits . ")";
    }

    $poll      = explode("===", $vote_array->text);
    $poll_text = html_entity_decode(strip_tags(trim($poll[0])));

    if($params->get('show_zero_votes', 1))
    {
        $values[] = '[\'' . $poll_text . '\', ' . $hits . ']';
    }
    else
    {
        if($vote_array->percent)
        {
            $values[] = '[\'' . $poll_text . '\', ' . $hits . ']';
        }
    }
}

$json_values = implode(', ', $values) . "\n";

$js = 'google.load("visualization", "1", {packages:["corechart"]});
google.setOnLoadCallback(drawChart);
function drawChart() {
    var data = google.visualization.arrayToDataTable([
        [\'Task\', \'\'],
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

$doc->addScript('//www.google.com/jsapi');
$doc->addScriptDeclaration($js);

?>
<div id="chart_div" style="width: 728px; height: 550px;" class="clear"></div>