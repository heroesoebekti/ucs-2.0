<?php
/**
 * Collection general report
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com
 *
 * Copyright (C) 2008 Arie Nugraha (dicarve@yahoo.com), 2017 Heru Subekti (heroe.soebekti@gmail.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

/* Reporting section */

// key to authenticate
define('INDEX_AUTH', '1');

if (!defined('UCS_BASE_DIR')) {
    // main system configuration
    require '../../../ucsysconfig.inc.php';
    // start the session
    require UCS_BASE_DIR.'admin/default/session.inc.php';
}

require UCS_BASE_DIR.'admin/default/session_check.inc.php';
require SIMBIO_BASE_DIR.'simbio_FILE/simbio_directory.inc.php';
require SIMBIO_BASE_DIR.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO_BASE_DIR.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO_BASE_DIR.'simbio_DB/simbio_dbop.inc.php';

/* collection statistic */
$table = new simbio_table();
$table->table_attr = 'align="center" class="border" cellpadding="5" cellspacing="0"';

// total number of titles
$stat_query = $dbs->query('SELECT COUNT(biblio_id) FROM biblio');
$stat_data = $stat_query->fetch_row();
$collection_stat['<h2>'.__('Total Titles').'</h2>'] = '<h2>'.$stat_data[0].'</h2>';

$limit = 5;

// total titles by GMD/medium
$stat_query = $dbs->query('SELECT gmd_name, COUNT(biblio_id) AS total_titles
    FROM `biblio` AS b
    INNER JOIN mst_gmd AS gmd ON b.gmd_id = gmd.gmd_id
    GROUP BY b.gmd_id HAVING total_titles>0 ORDER BY COUNT(biblio_id) DESC');
$stat_data = '<ul>';
//$stat_data = '<div class="chartLink"><a class="notAJAX openPopUp" href="'.MODULES_WEB_ROOT_DIR.'reporting/charts_report.php?chart=total_title_gmd" width="700" height="470" title="'.__('Total Titles By Medium/GMD').'">'.__('Show in chart/plot').'</a></div>';
while ($data = $stat_query->fetch_row()) {
    $stat_data .= '<li><strong>'.$data[0].'</strong> : '.$data[1].'</li>';

}
$stat_data .= '</ul>';
$collection_stat[__('Total Titles By Medium/GMD')] = $stat_data;


// total titles by GMD/medium
$stat_query = $dbs->query('SELECT language_name, COUNT(biblio_id) 
	FROM mst_language AS ml 
	LEFT JOIN biblio AS b ON ml.language_id = b.language_id
    GROUP BY ml.language_id ORDER BY COUNT(b.biblio_id) DESC');
$stat_data = '<ul>';
//$stat_data .= '<div class="chartLink"><a class="notAJAX openPopUp" href="'.MODULES_WEB_ROOT_DIR.'reporting/charts_report.php?chart=total_title_gmd" width="700" height="470" title="'.__('Total Titles By Medium/GMD').'">'.__('Show in chart/plot').'</a></div>';
while ($data = $stat_query->fetch_row()) {
    $stat_data .= '<li><strong>'.$data[0].'</strong> : '.$data[1].'</li>';
}
$stat_data .= '</ul>';
$collection_stat[__('10 Top Language')] = $stat_data;


// total titles by publisher
$stat_query = $dbs->query('SELECT publisher_name, COUNT(biblio_id) 
	FROM mst_publisher AS mp 
	LEFT JOIN biblio AS b ON mp.publisher_id = b.publisher_id
    GROUP BY mp.publisher_id ORDER BY COUNT(b.biblio_id) DESC LIMIT 0,'.$limit);
$stat_data = '<ul>';
//$stat_data .= '<div class="chartLink"><a class="notAJAX openPopUp" href="'.MODULES_WEB_ROOT_DIR.'reporting/charts_report.php?chart=total_title_gmd" width="700" height="470" title="'.__('Total Titles By Medium/GMD').'">'.__('Show in chart/plot').'</a></div>';

while ($data = $stat_query->fetch_row()) {
    $stat_data .= '<ul><strong>'.$data[0].'</strong> : '.$data[1].'</ul>';
}
$stat_data .= '</ul>';
$collection_stat[__('10 Top Publisher')] = $stat_data;

// total titles by publisher
$stat_query = $dbs->query('SELECT publish_year, COUNT(biblio_id) 
	FROM  biblio AS b 
    GROUP BY b.publish_year ORDER BY COUNT(b.biblio_id) DESC LIMIT 0,'.$limit);
//$stat_data .= '<div class="chartLink"><a class="notAJAX openPopUp" href="'.MODULES_WEB_ROOT_DIR.'reporting/charts_report.php?chart=total_title_gmd" width="700" height="470" title="'.__('Total Titles By Medium/GMD').'">'.__('Show in chart/plot').'</a></div>';
$stat_data = '<ul>';
while ($data = $stat_query->fetch_row()) {
    $stat_data .= '<li><strong>'.$data[0].'</strong> : '.$data[1].'</li>';
}
$stat_data .= '</ul>';
$collection_stat[__('10 Top Publish Year')] = $stat_data;

// total titles by author
$stat_query = $dbs->query('SELECT ma.author_name, COUNT(ba.author_id) 
	FROM  biblio AS b LEFT JOIN biblio_author ba ON b.biblio_id=ba.biblio_id
	LEFT JOIN mst_author AS ma ON ma.author_id=ba.author_id
    GROUP BY ma.author_id ORDER BY COUNT(ba.author_id) DESC LIMIT 0,'.$limit);
//$stat_data .= '<div class="chartLink"><a class="notAJAX openPopUp" href="'.MODULES_WEB_ROOT_DIR.'reporting/charts_report.php?chart=total_title_gmd" width="700" height="470" title="'.__('Total Titles By Medium/GMD').'">'.__('Show in chart/plot').'</a></div>';
$stat_data = '<ul>';
while ($data = $stat_query->fetch_row()) {
    $stat_data .= '<li><strong>'.$data[0].'</strong> : '.$data[1].'</li>';
}
$stat_data .= '</ul>';
$collection_stat[__('10 Top Author')] = $stat_data;


// total titles by topic
$stat_query = $dbs->query('SELECT mt.topic, COUNT(bt.topic_id) 
	FROM  biblio AS b 
	LEFT JOIN biblio_topic bt ON b.biblio_id=bt.biblio_id
	LEFT JOIN mst_topic AS mt ON mt.topic_id=bt.topic_id
    GROUP BY mt.topic_id ORDER BY COUNT(bt.topic_id) DESC LIMIT 0,'.$limit);
//$stat_data .= '<div class="chartLink"><a class="notAJAX openPopUp" href="'.MODULES_WEB_ROOT_DIR.'reporting/charts_report.php?chart=total_title_gmd" width="700" height="470" title="'.__('Total Titles By Medium/GMD').'">'.__('Show in chart/plot').'</a></div>';
$stat_data = '<ul>';
while ($data = $stat_query->fetch_row()) {
    $stat_data .= '<li><strong>'.$data[0].'</strong> : '.$data[1].'</li>';
}
$stat_data .= '</ul>';
$collection_stat[__('10 Top Topics')] = $stat_data;

// popular client
$stat_query = $dbs->query('SELECT nc.name, COUNT(b.orig_biblio_id) FROM biblio AS b
    LEFT JOIN nodes_client AS nc ON nc.id=b.node_id
    GROUP BY b.node_id ORDER BY COUNT(b.orig_biblio_id) DESC LIMIT 10');
$stat_data = '<ul>';
while ($data = $stat_query->fetch_row()) {
    $stat_data .= '<li><strong>'.$data[0].'</strong> : '.$data[1].'</li>';
}
$stat_data .= '</ul>';
$collection_stat[__('10 Top Client')] = $stat_data;

// table header
$table->setHeader(array(__('Collection Statistic Summary')));
$table->table_header_attr = 'class="dataListHeader"';
$table->setCellAttr(0, 0, 'colspan="3"');
// initial row count
$row = 1;
foreach ($collection_stat as $headings=>$stat_data) {
    $table->appendTableRow(array($headings, ':', $stat_data));
    // set cell attribute
    $table->setCellAttr($row, 0, 'class="alterCell" valign="top" style="width: 170px;"');
    $table->setCellAttr($row, 1, 'class="alterCell" valign="top" style="width: 1%;"');
    $table->setCellAttr($row, 2, 'class="alterCell2" valign="top" style="width: auto;"');
    // add row count
    $row++;
}

echo $table->printTable();