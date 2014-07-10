<?php

/**
 * [BEGIN_COT_EXT]
 * Hooks=global
 * [END_COT_EXT]
 */

/**
 * PagePLUS for Cotonti CMF
 *
 * @version 1.00
 * @author  esclkm
 * @copyright (c) 2012 esclkm
 */
defined('COT_CODE') or die('Wrong URL');

require_once cot_incfile('page', 'module');

/**
 * Generates page list widget
 * @param  string  $code       List code - need for pagination and template (template name: pageplus.$code.tpl)
 * @param  integer $items      Number of items to show. 0 - all items
 * @param  string  $order      Sorting order (SQL)
 * @param  string  $condition  Custom selection filter (SQL)
 * @param  string  $cat        Custom parent category code
 * @param  boolean $sub        Include subcategories TRUE/FALSE
 * @param  boolean $noself     Exclude the current page from the rowset for pages.
 * @param  string  $blacklist  Category black list, semicolon separated
 * @param  string  $whitelist  Category white list, semicolon separated
 * @return string              Parsed HTML
 */
function pp_list($code = '', $items = 0, $order = '', $condition = '', $cat = '', $sub = true, $noself = false, $blacklist = '', $whitelist = '')
{
	global $db, $db_pages, $db_users, $env, $structure, $cfg;
	
	if ($env['ext'] == 'admin')
	{
		return '';
	}
	// Compile lists
	$blacklist = str_replace(' ', '', $blacklist);
	$whitelist = str_replace(' ', '', $whitelist);
	$blacklist = (!empty($blacklist)) ? explode(',', $blacklist) : array();
	$whitelist = (!empty($whitelist)) ? explode(',', $whitelist) : array();

	// Get the cats
	$cats = array();
	if (empty($cat) && (count($blacklist) > 0 || count($whitelist) > 0))
	{
		// All cats except bl/wl
		foreach ($structure['page'] as $key => $row)
		{
			if ((count($blacklist) > 0 && !in_array($key, $blacklist))
				|| (count($whitelist) > 0 && in_array($key, $whitelist)))
			{
				$cats[] = $key;
			}
		}
	}
	elseif (!empty($cat) && $sub)
	{
		// Specific cat
		$cats = cot_structure_children('page', $cat, $sub);
	}

	if (count($cats) > 0)
	{
		$cats = (count($blacklist) > 0 ) ? array_diff($cats, $blacklist) : $cats;
		$cats = (count($whitelist) > 0) ? array_intersect($cats, $whitelist) : $cats;
		$where_cat = "AND page_cat IN ('" . implode("','", $cats) . "')";
	}
	elseif (!empty($cat))
	{
		$where_cat = "AND page_cat = " . $db->quote($cat);
	}

	$where_condition = (empty($condition)) ? '' : "AND $condition";

	if ($noself && defined('COT_PAGES') && !defined('COT_LIST'))
	{
		global $id;
		$where_condition .= " AND page_id != $id";
	}

	$pagination = 'pp' . $code . 'd';
	// Get pagination number if necessary
	list($pg, $d, $durl) = cot_import_pagenav($pagination, $items);

	// Display the items
	$t = new XTemplate(cot_tplfile(array('pageplus', $code), 'plug'));

	/* === Hook === */
	foreach (cot_getextplugins('pageplus.query') as $pl)
	{
		include $pl;
	}
	/* ===== */
	
	if ($cfg['plugin']['pageplus']['comments'] && cot_plugin_active('comments'))
	{
		global $db_com;

		require_once cot_incfile('comments', 'plug');

		$cns_join_columns .= ", (SELECT COUNT(*) FROM `$db_com` WHERE com_area = 'page' AND com_code = p.page_id) AS com_count";
	}
	$sql_order = empty($order) ? 'ORDER BY page_date DESC' : "ORDER BY $order";
	$sql_limit = ($items > 0) ? "LIMIT $d, $items" : '';
	
	$totalitems = $db->query("SELECT COUNT(*) FROM $db_pages AS p $cns_join_tables WHERE page_state='0' $where_cat $where_condition")->fetchColumn();

	$sql = $db->query("SELECT p.*, u.* $cns_join_columns FROM $db_pages AS p LEFT JOIN $db_users AS u ON p.page_ownerid = u.user_id
			$cns_join_tables WHERE page_state='0' $where_cat $where_condition $sql_order $sql_limit");

	$jj = 1;
	while ($row = $sql->fetch())
	{
		$t->assign(cot_generate_pagetags($row, 'PAGE_ROW_'));

		$t->assign(array(
			'PAGE_ROW_NUM' => $jj,
			'PAGE_ROW_ODDEVEN' => cot_build_oddeven($jj),
			'PAGE_ROW_RAW' => $row
		));

		$t->assign(cot_generate_usertags($row, 'PAGE_ROW_OWNER_'));

		/* === Hook === */
		foreach (cot_getextplugins('pageplus.loop') as $pl)
		{
			include $pl;
		}
		/* ===== */
		
		if ($cfg['plugin']['pageplus']['comments'] && cot_plugin_active('comments'))
		{
			$rowe_urlp = empty($row['page_alias']) ? array('c' => $row['page_cat'], 'id' => $row['page_id']) : array('c' => $row['page_cat'], 'al' => $row['page_alias']);
			$t->assign(array(
				'PAGE_ROW_COMMENTS' => cot_comments_link('page', $rowe_urlp, 'page', $row['page_id'], $c, $row),
				'PAGE_ROW_COMMENTS_COUNT' => cot_comments_count('page', $row['page_id'], $row)
			));
		}

		$t->parse("MAIN.PAGE_ROW");
		$jj++;
	}

	// Render pagination
	$url_params = $_GET;
	$url_area = 'index';
	if(cot_plugin_active($url_params['e']))
	{
		$url_area = 'plug';
	}
	if(cot_plugin_active($url_params['e']))
	{
		$url_area = $url_params['e'];
		unset($url_params['e']);
	}	
	unset($url_params[$pagination]);
	
	$pagenav = cot_pagenav($url_area, $url_params, $d, $totalitems, $items, $pagination);

	$t->assign(array(
		'PAGE_TOP_PAGINATION' => $pagenav['main'],
		'PAGE_TOP_PAGEPREV' => $pagenav['prev'],
		'PAGE_TOP_PAGENEXT' => $pagenav['next'],
		'PAGE_TOP_FIRST' => $pagenav['first'],
		'PAGE_TOP_LAST' => $pagenav['last'],
		'PAGE_TOP_CURRENTPAGE' => $pagenav['current'],
		'PAGE_TOP_TOTALLINES' => $totalitems,
		'PAGE_TOP_MAXPERPAGE' => $items,
		'PAGE_TOP_TOTALPAGES' => $pagenav['total']
	));

	/* === Hook === */
	foreach (cot_getextplugins('pageplus.tags') as $pl)
	{
		include $pl;
	}
	/* ===== */

	$t->parse();
	return $t->text();
}

?>