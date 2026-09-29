<?php

/**
 * Format task category (get name by slug)
 */
function wmm_format_category($category_name = '', $category_icon = NULL, $category_color = NULL)
{
	if (empty($category_name))
	{
		return empty($category_icon) ? '' : '<i class="fa '.html_escape($category_icon ?: 'fa-tasks').' tw-mr-2" style="color: '.html_escape($category_color ?: '#3b82f6').'"></i>';
	} else
	{
		$nameHtml = '<i class="fa '.html_escape($category_icon ?: 'fa-tasks').' tw-mr-2" style="color: '.html_escape($category_color ?: '#3b82f6').'"></i>';
		$nameHtml .= '<strong>'.html_escape($category_name).'</strong>';

		return '<span class="label label-default">'.$nameHtml.'</span>';
	}
}
