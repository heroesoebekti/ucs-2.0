<?php
/**
 * Copyright (C) 2010  Arie Nugraha (dicarve@yahoo.com), 2017 Heru Subekti (heroe.soebekti@gmail.com)
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

/* Nodes Client Management section */

// key to authenticate
define('INDEX_AUTH', '1');

// main system configuration
require '../../../ucsysconfig.inc.php';
// start the session
require UCS_BASE_DIR.'admin/default/session.inc.php';
require UCS_BASE_DIR.'admin/default/session_check.inc.php';
require SIMBIO_BASE_DIR.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO_BASE_DIR.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO_BASE_DIR.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO_BASE_DIR.'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require SIMBIO_BASE_DIR.'simbio_DB/simbio_dbop.inc.php';

// privileges checking
$can_read = utility::havePrivilege('master_file', 'r');
$can_write = utility::havePrivilege('master_file', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
}

/* RECORD OPERATION */
if (isset($_POST['saveData']) AND $can_read AND $can_write) {
    $id = $dbs->escape_string(trim(strip_tags($_POST['id'])));
    $name = $dbs->escape_string(trim(strip_tags($_POST['name'])));
    $password = $dbs->escape_string(trim(strip_tags($_POST['password'])));
    $baseurl = $dbs->escape_string(trim(strip_tags($_POST['baseurl'])));
    $ip = $dbs->escape_string(trim(strip_tags($_POST['ip'])));
    // check form validity
    if (empty($id) && empty($password) && empty($name) && empty($url)) {
        utility::jsAlert(__('Field can\'t be empty')); //mfc
        exit();
    } else {
        $data['id'] = $id;
        $data['name'] = $name;
        $data['password'] = sha1($password);
        $data['baseurl'] = $baseurl;
        $data['ip'] = $ip;

        // create sql op object
        $sql_op = new simbio_dbop($dbs);
        if (isset($_POST['updateRecordID'])) {
            /* UPDATE RECORD MODE */
            // filter update record ID
            $updateRecordID = (integer)$_POST['updateRecordID'];
            // update the data
            $update = $sql_op->update('nodes_client', $data, 'client_id='.$updateRecordID);
            if ($update) {
                utility::jsAlert(__('Node Client Data Successfully Updated'));
                echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(parent.jQuery.ajaxHistory[1].url);</script>';
            } else { utility::jsAlert(__('Node Client Data FAILED to Updated. Please Contact System Administrator')."\nDEBUG : ".$sql_op->error); }
            exit();
        } else {
            /* INSERT RECORD MODE */
            // insert the data
            $insert = $sql_op->insert('nodes_client', $data);
            if ($insert) {
                utility::jsAlert(__('New Node Client Data Successfully Saved'));
                echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'\');</script>';
            } else { utility::jsAlert(__('Node Client Data FAILED to Save. Please Contact System Administrator')."\nDEBUG : ".$sql_op->error); }
            exit();
        }
    }
    exit();
} else if (isset($_POST['itemID']) AND !empty($_POST['itemID']) AND isset($_POST['itemAction'])) {
    if (!($can_read AND $can_write)) {
        die();
    }
    /* DATA DELETION PROCESS */
    $sql_op = new simbio_dbop($dbs);
    $failed_array = array();
    $error_num = 0;
    if (!is_array($_POST['itemID'])) {
        // make an array
        $_POST['itemID'] = array((integer)$_POST['itemID']);
    }

    foreach ($_POST['itemID'] as $itemID) {
        $itemID = (integer)$itemID;
        // check if this biblio data still have an item
        $_sql_biblio_client_q = sprintf('SELECT nc.id, COUNT(node_id) FROM nodes_client AS nc
          LEFT JOIN biblio AS b ON b.node_id=nc.id
          WHERE nc.client_id=%d GROUP BY b.node_id', $itemID);
        $biblio_client_q = $dbs->query($_sql_biblio_client_q);
        $biblio_client_d = $biblio_client_q->fetch_row();
        if ($biblio_client_d[1] < 1) {
          if (!$sql_op->delete('nodes_client', "client_id=$itemID")) {
            $error_num++;
          } 
        } 
    }

    // error alerting
    if ($error_num == 0) {
        utility::jsAlert(__('All Data Successfully Deleted'));
        echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'?'.$_POST['lastQueryStr'].'\');</script>';
    } else {
        utility::jsAlert(__('Some or All Data NOT deleted successfully!\nPlease contact system administrator'));
        echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'?'.$_POST['lastQueryStr'].'\');</script>';
    }
    exit();
}
/* RECORD OPERATION END */

/* search form */
?>
<fieldset class="menuBox">
<div class="menuBoxInner masterFileIcon">
  <div class="per_title">
	  <h2><?php echo __('Node Client'); ?></h2>
  </div>
  <div class="sub_section">
	  <div class="action_button">
      <a href="<?php echo MODULES_WEB_ROOT_DIR; ?>master_file/nodes_client.php?action=detail" class="headerText2"><?php echo __('Add New Node'); ?></a>
      <a href="<?php echo MODULES_WEB_ROOT_DIR; ?>master_file/nodes_client.php" class="headerText2"><?php echo __('Node List'); ?></a>
	  </div>
    <form name="search" action="<?php echo MODULES_WEB_ROOT_DIR; ?>master_file/nodes_client.php" id="search" method="get" style="display: inline;"><?php echo __('Search'); ?> :
    <input type="text" name="keywords" size="30" />
    <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>" class="button" />
    </form>
  </div>
</div>
</fieldset>
<?php
/* search form end */
/* main content */
if (isset($_POST['detail']) OR (isset($_GET['action']) AND $_GET['action'] == 'detail')) {
    if (!($can_read AND $can_write)) {
        die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
    }
    /* RECORD FORM */
    $itemID = (integer)isset($_POST['itemID'])?$_POST['itemID']:0;
    $rec_q = $dbs->query('SELECT * FROM nodes_client WHERE client_id='.$itemID);
    $rec_d = $rec_q->fetch_assoc();

    // create new instance
    $form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'], 'post');
    $form->submit_button_attr = 'name="saveData" value="'.__('Save').'" class="button"';

    // form table attributes
    $form->table_attr = 'align="center" id="dataList" cellpadding="5" cellspacing="0"';
    $form->table_header_attr = 'class="alterCell" style="font-weight: bold;"';
    $form->table_content_attr = 'class="alterCell2"';

    // edit mode flag set
    if ($rec_q->num_rows > 0) {
        $form->edit_mode = true;
        // record ID for delete process
        $form->record_id = $itemID;
        // form record title
        $form->record_title = $rec_d['name'];
        // submit button attribute
        $form->submit_button_attr = 'name="saveData" value="'.__('Update').'" class="button"';
    }

    /* Form Element(s) */
    // node name
    $form->addTextField('text', 'id', __('Node ID').'*', $rec_d['id'], 'style="width: 60%;"');    
    // node name
    $form->addTextField('text', 'name', __('Node Name').'*', $rec_d['name'], 'style="width: 60%;"');

    // password
    $form->addTextField('text', 'password', __('Password').'*', $rec_d['password'], 'style="width: 60%;"');

    // base url
    $form->addTextField('text', 'baseurl', __('Base Url').'*', $rec_d['baseurl'], 'style="width: 60%;"');

    // base url
    $form->addTextField('text', 'ip', __('IP Address').'*', $rec_d['ip'], 'style="width: 60%;"');

    // edit mode messagge
    if ($form->edit_mode) {
        echo '<div class="infoBox">'.__('You are going to edit node data').' : <b>'.$rec_d['place_name'].'</b>  <br />'.__('Last Update').$rec_d['last_update'].'</div>'; //mfc
    }
    // print out the form object
    echo $form->printOut();
} else {
    /* PLACE LIST */
    // table spec
    $table_spec = 'nodes_client';

    // create datagrid
    $datagrid = new simbio_datagrid();
    if ($can_read AND $can_write) {
        $datagrid->setSQLColumn('client_id',
            'id AS \''.__('Client ID').'\'',
            'name AS \''.__('Client Name').'\'',
            'baseurl AS \''.__('Base Url').'\'',
            'ip AS \''.__('IP Address').'\'');
    } else {
        $datagrid->setSQLColumn('id AS \''.__('Client ID').'\'',
            'name AS \''.__('Client Name').'\'',
            'baseurl AS \''.__('Base Url').'\'',
            'ip AS \''.__('IP Address').'\'');
    }
    $datagrid->setSQLorder('name ASC');

    // is there any search
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
       $keywords = $dbs->escape_string($_GET['keywords']);
       $datagrid->setSQLCriteria("name LIKE '%$keywords%'");
    }

    // set table and table header attributes
    $datagrid->table_attr = 'align="center" id="dataList" cellpadding="5" cellspacing="0"';
    $datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
    // set delete proccess URL
    $datagrid->chbox_form_URL = $_SERVER['PHP_SELF'];

    // put the result into variable
    $datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 20, ($can_read AND $can_write));
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
        $msg = str_replace('{result->num_rows}', $datagrid->num_rows, __('Found <strong>{result->num_rows}</strong> from your keywords')); //mfc
        echo '<div class="infoBox">'.$msg.' : "'.$_GET['keywords'].'"</div>';
    }

    echo $datagrid_result;
}
/* main content end */
