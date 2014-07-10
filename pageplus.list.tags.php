<?php

/**
 * [BEGIN_COT_EXT]
 * Hooks=page.list.tags, page.tags, index.tags
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
require_once(cot_langfile('pageplus'));
require_once cot_incfile('forms');
global $ppf_fields;

foreach ($ppf_fields as $row)
{
	$uname = strtoupper($row['field_name']);
	switch ($row['field_type'])
	{
		case 'inputint':
		case 'currency':
		case 'double':
			$t->assign(array(
				$uname => cot_inputbox('text', 'ppf['.$row['field_name'].'][is]', $ppf[$row['field_name']]['is']),
				$uname.'_MORE' => cot_inputbox('text', 'ppf['.$row['field_name'].'][more]', $ppf[$row['field_name']]['more']),
				$uname.'_LESS' => cot_inputbox('text', 'ppf['.$row['field_name'].'][less]', $ppf[$row['field_name']]['less']),
				$uname.'_ISSET' => cot_checkbox($ppf[$row['field_name']]['isset'], 'ppf['.$row['field_name'].'][isset]'),
			));
			break;
		case 'datetime':
			$t->assign(array(
				$uname => cot_selectbox_date($ppf[$row['field_name']]['is'], 'long', 'ppf['.$row['field_name'].'][is]'),
				$uname.'_MORE' => cot_selectbox_date($ppf[$row['field_name']]['more'], 'long', 'ppf['.$row['field_name'].'][more]'),
				$uname.'_LESS' => cot_selectbox_date($ppf[$row['field_name']]['less'], 'long', 'ppf['.$row['field_name'].'][less]'),
				$uname.'_SHORT' => cot_selectbox_date($ppf[$row['field_name']]['is'], 'long', 'ppf['.$row['field_name'].'][is]'),
				$uname.'_MORESHORT' => cot_selectbox_date($ppf[$row['field_name']]['more'], 'short', 'ppf['.$row['field_name'].'][more]'),
				$uname.'_LESSSHORT' => cot_selectbox_date($ppf[$row['field_name']]['less'], 'short', 'ppf['.$row['field_name'].'][less]'),
				$uname.'_ISSET' => cot_checkbox($ppf[$row['field_name']]['isset'], 'ppf['.$row['field_name'].'][isset]'),
			));
			break;
		case 'file':
		case 'input':
			$t->assign(array(
				$uname => cot_inputbox('text', 'ppf['.$row['field_name'].'][is]', $ppf[$row['field_name']]['is']),
				$uname.'_LIKE' => cot_inputbox('text', 'ppf['.$row['field_name'].'][like]', $ppf[$row['field_name']]['like']),
				$uname.'_ISSET' => cot_checkbox($ppf[$row['field_name']]['isset'], 'ppf['.$row['field_name'].'][isset]'),
			));
			break;
		case 'checkbox':
			$R['checkbox_res'] = $R['input_checkbox'];
			$R['input_checkbox'] = '<label><input type="checkbox" name="{$name}" value="{$value}"{$checked}{$attrs} /> {$title}</label>';
			$cfg_params_titles = (isset($L['ppf_'.$row['field_name'].'_params']) && is_array($L['ppf_'.$row['field_name'].'_params'])) ? $L['ppf_'.$row['field_name'].'_params'] : $L['ppf_checkbox'];
			$t->assign(array(
				$uname => cot_selectbox((isset($ppf[$row['field_name']])) ? $ppf[$row['field_name']] : 2, 'ppf['.$row['field_name'].']', range(0, 2), $cfg_params_titles, false),
				$uname.'_CHECK' => cot_checkbox((isset($ppf[$row['field_name']])) ? $ppf[$row['field_name']] : 0, 'ppf['.$row['field_name'].']', isset($L['page_'.$row['field_name'].'_title']) ? $L['page_'.$row['field_name'].'_title'] : $row['field_description'])
			));
			$R['input_checkbox'] = $R['checkbox_res'];
			break;
		case 'select':
		case 'radio':
		case 'country':
			$opt_array = explode(',', $row['field_variants']);
			$options = array();
			$options_titles = array();

			$options[] = 'ppf_nomatter';
			$options_titles[] = $L['ppf_nomatter'];
			if ($row['field_type'] != 'country')
			{
				if (count($opt_array) != 0)
				{
					foreach ($opt_array as $var)
					{
						$options_titles[] = (!empty($L['page_'.$row['field_name'].'_'.$var])) ? $L['page_'.$row['field_name'].'_'.$var] : $var;
						$options[] = $var;
					}
				}
			}
			else
			{
				if (!$cot_countries)
					include_once cot_langfile('countries', 'core');
				$options[] = array_keys($cot_countries);
				$options_titles[] = array_values($cot_countries);
			}
			//options gen
			$ppfmiltiadd = '';
			$ppf[$row['field_name']] = (is_string($ppf[$row['field_name']]) && !empty($ppf[$row['field_name']])) ? array($ppf[$row['field_name']]) : $ppf[$row['field_name']];
			$ppf[$row['field_name']] = (is_array($ppf[$row['field_name']])) ? $ppf[$row['field_name']] : array('ppf_nomatter');
			foreach ($ppf[$row['field_name']] as $ppfval)
			{
				$ppfmiltiadd .= '<div class="option'.$row['field_name'].'">
'.cot_selectbox($ppfval, 'ppf['.$row['field_name'].'][]', $options, $options_titles, false, '').'<button name="deloption" type="button" class="deloption'.$row['field_name'].'" title="'.$L['Delete'].'" style="display:none;"><img src="'.$cfg['plugins_dir'].'/pageplus/img/minus.png" alt="'.$L['Delete'].'" /></button>
</div>';
			}
			$ppfselval = ((is_array($ppf[$row['field_name']]) && count($ppf[$row['field_name']]) > 0)) ? $ppf[$row['field_name']][0] : 'ppf_nomatter';
			//end options gen
			$t->assign(array(
				$uname => cot_selectbox($ppfselval, 'ppf['.$row['field_name'].'][]', $options, $options_titles, false),
				$uname.'_MULTI' => cot_selectbox((isset($ppf[$row['field_name']])) ? $ppf[$row['field_name']] : 'ppf_nomatter', 'ppf['.$row['field_name'].'][]', $options, $options_titles, false, ' multiple="multiple"'),
				$uname.'_RADIO' => cot_radiobox($ppfselval, 'ppf['.$row['field_name'].'][]', $options, $options_titles, false),
				$uname.'_MULTIADD' => $ppfmiltiadd.'<button id="addoption'.$row['field_name'].'" name="addoption" type="button" title="'.$L['Add'].'" style="display:none;"><img src="'.$cfg['plugins_dir'].'/pageplus/img/plus.png" alt="'.$L['Add'].'" /></button>
<script type="text/javascript">
$(".deloption'.$row['field_name'].'").live("click",function () {
	$(this).parent().children("select").attr("value", "ppf_nomatter");
	if ($(".option'.$row['field_name'].'").length > 1)
	{
		$(this).parent().remove();
	}
	return false;
});

$(document).ready(function(){
	$("#addoption'.$row['field_name'].'").click(function () {
	$(".option'.$row['field_name'].'").last().clone().insertAfter($(".option'.$row['field_name'].'").last()).show().children("select").attr("value","ppf_nomatter");
	return false;
	});
	$("#addoption'.$row['field_name'].'").show();
	$(".deloption'.$row['field_name'].'").show();
});
</script>',
			));
			break;
		case 'textarea':
		default:
			$t->assign(array(
				$uname => cot_inputbox('text', 'ppf['.$row['field_name'].'][is]', $ppf[$row['field_name']]['is']),
				$uname.'_LIKE' => cot_inputbox('text', 'ppf['.$row['field_name'].'][like]', $ppf[$row['field_name']]['like']),
				$uname.'_ISSET' => cot_checkbox($ppf[$row['field_name']]['isset'], 'ppf['.$row['field_name'].'][isset]'),
			));
			break;
	}

	$t->assign($uname.'_TITLE', isset($L['page_'.$row['field_name'].'_title']) ? $L['page_'.$row['field_name'].'_title'] : $row['field_description']);
}

$ppfparams = $_GET;
unset($ppfparams['ppf']);
foreach ($ppfparams as $key => $val)
{
	$ppfhidden .= cot_inputbox('hidden', $key, $val);
}

$t->assign(array(
	'LIST_FILTERS_HIDDEN' => $ppfhidden,
	'LIST_FILTERS_CAT' => cot_selectbox((!isset($ppf['ppfcat'])) ? 'all' : $ppf['ppfcat'], 'ppf[ppfcat][]', array_keys($ppf_pages_cat_list), array_values($ppf_pages_cat_list), false, ' multiple="multiple" style="width:50%"'),
	'LIST_FILTERS_SEARCH' => cot_inputbox('text', 'ppf[search]', $ppf['search']),
	'LIST_FILTERS_URL' => cot_url('page', "c=$c&s=$s&w=$w&o=$o&p=$p")
));
$t->parse('MAIN.LIST_FILTERS');
?>