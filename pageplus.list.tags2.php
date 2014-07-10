<?php

/** 
 * [BEGIN_COT_EXT]
 * Hooks=page.list.tags
 * Tags=page.list.tpl:{LIST_TOP_SORT},{LIST_TOP_WAY},{LIST_TOP_MAXPERPAGE}
 * [END_COT_EXT]
 */
 
/**
 * PagePLUS for Cotonti CMF
 *
 * @version 1.00
 * @author  esclkm
 * @copyright (c) 2012 esclkm
 */

defined('COT_CODE') or die('Wrong URL.');
$cfg['plugin']['pageplus']['fields'] = str_replace(' ', '', $cfg['plugin']['pageplus']['fields']);
$cfg['plugin']['pageplus']['counts'] = str_replace(' ', '', $cfg['plugin']['pageplus']['counts']);

$mainflds = array('title' => $L['Title'], 'key' => $L['Key'], 'date' => $L['Date'], 'author' => $L['Author'], 'owner' => $L['Owner'], 'count' => $L['Hits'], 'filecount' => $L['Downloaded']);

$jumpboxfields = !empty($cfg['plugin']['pageplus']['fields']) ? explode(',', $cfg['plugin']['pageplus']['fields']) : array();
$build_forms_sort = array();

foreach ($cot_extrafields[$db_pages] + $mainflds as $row_k => $row_p)
{
	$hrefs = cot_url('page', array('s' => $row_k) + $list_url_path, '', true);
	if(in_array($row_k, array('title', 'key', 'date', 'author', 'owner', 'count', 'filecount')))
	{
		$titles = $row_p;
	}
	else
	{
		$titles = isset($L['page_'.$row_k.'_title']) ?	$L['page_'.$row_k.'_title'] : $row_p['field_description'];
	}	
	
	if (empty($cfg['plugin']['pageplus']['fields']) || in_array($row_k, $jumpboxfields))
	{
		$build_forms_sort[$hrefs] = $titles;
	}
}
$build_forms_way[cot_url('page', array('w' => 'asc') + $list_url_path, '', true)] = $L['Ascending'];
$build_forms_way[cot_url('page', array('w' => 'desc') + $list_url_path, '', true)] = $L['Descending'];

$jumpboxcount = !empty($cfg['plugin']['pageplus']['counts']) ? explode(',', $cfg['plugin']['pageplus']['counts']) : array(10, 15, 20, 30, 50, 100);
foreach($jumpboxcount as $row_k => $row_p)
{
	$hrefs = cot_url('page', array('perpage' => $row_p) + $list_url_path, '', true);
	$build_forms_count[$hrefs] = $row_p;
}

$t->assign(array(
	"LIST_TOP_SORT" => cot_selectbox(cot_url('page', array('s' => $s) + $list_url_path, '', true), 'jumpboxsort', array_keys($build_forms_sort), array_values($build_forms_sort), false, 'onchange="redirect(this)"'),
	"LIST_TOP_WAY" => cot_selectbox(cot_url('page', array('w' => $w) + $list_url_path, '', true), 'jumpboxway', array_keys($build_forms_way), array_values($build_forms_way), false, 'onchange="redirect(this)"'),
	"LIST_TOP_MAXPERPAGE" => cot_selectbox(cot_url('page', array('perpage' => $perpage) + $list_url_path, '', true), 'jumpboxperpage', array_keys($build_forms_count), array_values($build_forms_count), false, 'onchange="redirect(this)"'),
))


?>
 
