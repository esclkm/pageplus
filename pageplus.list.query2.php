<?php

/** 
 * [BEGIN_COT_EXT]
 * Hooks=page.list.query
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

$perpage = cot_import('perpage', 'G', 'INT'); // order way (asc, desc)

if ($perpage > 0)
{
	$_SESSION['userset'][$c]['perpage'] = $perpage;
}

if(isset($_SESSION['userset'][$c]['perpage']) && empty($perpage))
{
	$perpage = $_SESSION['userset'][$c]['perpage'];
}

if((int)$perpage > 0)
{
	$cfg['page']['maxrowsperpage'] = (int)$perpage;
}
else
{
	$perpage = $cfg['page']['maxrowsperpage'];
}
	
?>
 
