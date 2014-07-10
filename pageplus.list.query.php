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
defined('COT_CODE') or die('Wrong URL');

require_once cot_incfile('pageplus', 'plug');
global $ppf_fields;

if (is_array($_GET['ppf']))
{
	$ppf = $_GET['ppf'];
	if (isset($ppf['ppfcat']))
	{
		$ppf_sql_cat = ($ppf['ppfcat'][0] != 'all' && count($ppf['ppfcat']) > 0) ?
			"page_cat IN ('".$db->prep(implode("','", $ppf['ppfcat']))."')" : "page_cat IN ('".implode("','", $ppfcatlist)."')";
	}
	if (isset($ppf['search']) && !empty($ppf['search']))
	{
		$words = explode(' ', $ppf['search']);
		$searchsql_prt = '%'.implode('%', $words).'%';
		$ppf_sql_str[] = "(page_title LIKE '".$searchsql_prt."' OR page_desc LIKE '".$searchsql_prt."' OR page_text LIKE '".$searchsql_prt."')";
	}

	foreach ($ppf_fields as $row)
	{
		$uname = $row['field_name'];
		switch ($row['field_type'])
		{
			case 'checkbox':
				if (isset($ppf[$uname]) && $ppf[$uname] != 2)
				{
					$ppf_sql_str[] = generate_sql_query($uname, $ppf[$uname], 'is');
				}
				else
				{
					$ppf[$uname] == 2;
				}
				break;
			case 'select':
			case 'radio':
			case 'country':
				if (isset($ppf[$uname]) && !empty($ppf[$uname]) && ($ppf[$uname] != 'ppf_nomatter') && $ppf[$uname][0] != 'ppf_nomatter')
				{
					$ppf[$uname] = array_unique($ppf[$uname]);
					if (is_array($ppf[$uname]) && count($ppf[$uname]) == 1)
					{
						$ppf[$uname] = $ppf[$uname][0];
					}
					$ppf_sql_str[] = generate_sql_query($uname, $ppf[$uname], 'array');
				}
				else
				{
					$ppf[$uname] == 'ppf_nomatter';
				}
				break;
			case 'datetime':
				if (isset($ppf[$uname]['isset']) && $ppf[$uname]['isset'] != '')
				{
					$ppf_sql_str[] = generate_sql_query($uname, $ppf[$uname]['isset'], 'isset');
				}
				if (isset($ppf[$uname]['is']) && is_array($ppf[$uname]['is']))
				{
					$ppf[$uname]['is'] = cot_import_date($ppf[$uname]['is'], true, false, 'D');
					$ppf_sql_str[] = generate_sql_query($uname, $ppf[$uname]['is'], 'is');
				}
				if (isset($ppf[$uname]['more']) && is_array($ppf[$uname]['more']))
				{
					$ppf[$uname]['more'] = cot_import_date($ppf[$uname]['more'], true, false, 'D');
					$ppf_sql_str[] = generate_sql_query($uname, $ppf[$uname]['more'], 'more');
				}
				if (isset($ppf[$uname]['less']) && is_array($ppf[$uname]['less']))
				{
					$ppf[$uname]['less'] = cot_import_date($ppf[$uname]['less'], true, false, 'D');
					$ppf_sql_str[] = generate_sql_query($uname, $ppf[$uname]['less'], 'less');
				}
				break;
			case 'inputint':
			case 'currency':
			case 'double':
				if (isset($ppf[$uname]['isset']) && $ppf[$uname]['isset'] != '')
				{
					$ppf_sql_str[] = generate_sql_query($uname, $ppf[$uname]['isset'], 'isset');
				}
				if (isset($ppf[$uname]['is']) && $ppf[$uname]['is'] != '')
				{
					$ppf_sql_str[] = generate_sql_query($uname, $ppf[$uname]['is'], 'is');
				}
				if (isset($ppf[$uname]['more']) && $ppf[$uname]['more'] != '')
				{
					$ppf_sql_str[] = generate_sql_query($uname, $ppf[$uname]['more'], 'more');
				}
				if (isset($ppf[$uname]['less']) && $ppf[$uname]['less'] != '')
				{
					$ppf_sql_str[] = generate_sql_query($uname, $ppf[$uname]['less'], 'less');
				}
				break;
			case 'input':
			case 'textarea':
			case 'file':
			default:
				if (isset($ppf[$uname]['isset']) && $ppf[$uname]['isset'] != '')
				{
					$ppf_sql_str[] = generate_sql_query($uname, $ppf[$uname]['isset'], 'isset');
				}
				if (isset($ppf[$uname]['is']) && $ppf[$uname]['is'] != '')
				{
					$ppf_sql_str[] = generate_sql_query($uname, $ppf[$uname]['is'], 'is');
				}
				if (isset($ppf[$uname]['like']) && $ppf[$uname]['like'] != '')
				{
					$ppf_sql_str[] = generate_sql_query($uname, $ppf[$uname]['like'], 'like');
				}
				break;
		}
	}

	$ppf_url_path = array();
	foreach ($ppf as $k => $v)
	{
		if (is_array($v))
		{
			foreach ($v as $sk => $sv)
			{
				if (is_array($sv))
				{
					foreach ($sv as $ssk => $ssv)
					{
						$ppf_url_path['ppf['.$k.']['.$sk.']['.$ssk.']'] = $ssv;
					}
				}
				else
				{
					$ppf_url_path['ppf['.$k.']['.$sk.']'] = $sv;
				}
				
			}
		}
		else
		{
			$ppf_url_path['ppf['.$k.']'] = $v;
		}
	}

	if (!empty($ppf_sql_str))
	{
		$ppf_sql_str = array_diff($ppf_sql_str, array(''));
		$where['cat'] = (!empty($ppf_sql_cat)) ? $ppf_sql_cat : $where['cat'];
		if (count($ppf_sql_str) > 0)
		{
			$where['ppf'] = implode(" AND ", $ppf_sql_str);
		}
		$list_url_path += $ppf_url_path;
	}

 
}


?>