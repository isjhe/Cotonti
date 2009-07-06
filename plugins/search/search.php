<?PHP
/* ====================
[BEGIN_SED_EXTPLUGIN]
Code=search
Part=main
File=search
Hooks=standalone
Tags=
Order=10
[END_SED_EXTPLUGIN]
==================== */

/**
 * Search standalone.
 *
 * @package Cotonti
 * @version 0.0.6
 * @author Olivier C. & Spartan & Boss
 * @copyright Copyright (c) 2008-2009 Cotonti Team
 * @license BSD License
 */

defined('SED_CODE') && defined('SED_PLUG') or die('Wrong URL');

// Include functions
require_once("plugins/search/inc/search.func.inc.php");

// Read GET/POST params
$sq = sed_import('sq','P','TXT',$cfg['plugin']['search']['maxsigns']);
$pre = sed_import('pre','G','TXT');
$sq = (!empty($pre)) ? $pre : $sq;
$sq = preg_replace('/ +/', ' ', $sq);
$sq = sed_sql_prep($sq);
$a = sed_import('a','P','ALP');
$tab = sed_import('tab','G','ALP');
$frm = sed_import('frm','G','BOL');
if($frm)
{ $tab='frm'; }

// Search title
$plugin_title  = "<a href='plug.php?e=search'>".$L['plu_title_all']."</a>";
$plugin_title .= ($tab=='frm' || $tab=='pag') ? " ".$cfg['separator']." " : "";
if($tab=='frm')
{ $plugin_title .= "<a href='plug.php?e=search&tab=frm'>".$L['plu_title_frmtab']."</a>"; }
elseif($tab=='pag')
{ $plugin_title .= "<a href='plug.php?e=search&tab=pag'>".$L['plu_title_pagtab']."</a>"; }

// If advanced search
if($tab=='frm' || $tab=='pag')
{
	// Include file
	require_once("plugins/search/inc/search.ext.inc.php");

	// If date is required
	if($within > 0)
	{
		// Date FROM and TO in timestamp format
		$from_mktime = mktime(0,0,0,$from_month,$from_day,$from_year);
		$to_mktime = mktime(0,0,0,$to_month,$to_day,$to_year);
	}
}

// If it is search query
if($a=='search')
{
	// Query too short message
	if(strlen($sq) < 3)
	{
		$error_string .= "<div>".$L['plu_querytooshort']."</div>";
		unset($a);
	}

	// Count query words
	$words = explode(" ", $sq);
	$words_count = count($words);

	// Too many words error message
	if($words_count > $cfg['plugin']['search']['maxwords'])
	{
		$error_string .= "<div>".$L['plu_toomanywords']." ".$cfg['plugin']['search']['maxwords'].".</div>";
		unset($a);
	}

	// Making query string
	$sqlsearch = implode("%", $words);
	$sqlsearch = "%".$sqlsearch."%";
}


// If it is forum tab and it is on, the search forums
if($tab=='frm' && !$cfg['disable_forums'])
{
	$sea_frmtitle = sed_import('sea_frmtitle','P','INT');
	$sea_frmtext = sed_import('sea_frmtext','P','INT');
	$sea_frmreply = sed_import('sea_frmreply','P','INT');
	$sea_frmsort = sed_import('sea_frmsort','P','INT');
	$sea_frmsort2 = sed_sql_prep(sed_import('sea_frmsort2','P','TXT'));
	$sea_frmsub = sed_import('sea_frmsub','P','ARR');

	$sql1 = sed_sql_query("SELECT s.fs_id, s.fs_title, s.fs_category FROM $db_forum_sections AS s
		LEFT JOIN $db_forum_structure AS n ON n.fn_code=s.fs_category
		ORDER by fn_path ASC, fs_order ASC");

	// Making the sections list
	$plugin_forum_sec_list  = "<select multiple name='sea_frmsub[]' size='10' style='width:385px'>";
	$plugin_forum_sec_list .= "<option value='all'".(($sea_frmsub[0]=='all' || count($sea_frmsub)==0)?" selected='selected'":"").">".$L['plu_allsections']."</option>";
	while($row1 = mysql_fetch_array($sql1))
	{
		if(sed_auth('forums', $row1['fs_id'], 'R'))
		{
			$plugin_forum_sec_list .= "<option value='".$row1['fs_id']."'";

			// Select sections which have been selected before
			if(count($sea_frmsub) > 0)
			{
				for($i=0; $i<count($sea_frmsub); $i++)
				{
					$plugin_forum_sec_list .= $row1['fs_id'] == $sea_frmsub[$i] && $sea_frmsub[0] != 'all' ? " selected='selected'" : "";
				}
			}

			$plugin_forum_sec_list .= ">".sed_build_forums($row1['fs_id'], $row1['fs_title'], $row1['fs_category'], FALSE)."</option>";
		}
	}
	$plugin_forum_sec_list .= "</select>";

	// Making the list for ordering
	$plugin_forum_res_sort  = "<select style='width:160px' name='sea_frmsort'>";
	$plugin_forum_res_sort .= "<option value='1'".(($sea_frmsort==1 || !isset($sea_frmsort))?" selected":"").">".$L['plu_frm_res_sort1']."</option>";
	$plugin_forum_res_sort .= "<option value='2'".($sea_frmsort==2?" selected":"").">".$L['plu_frm_res_sort2']."</option>";
	$plugin_forum_res_sort .= "<option value='3'".($sea_frmsort==3?" selected":"").">".$L['plu_frm_res_sort3']."</option>";
	$plugin_forum_res_sort .= "<option value='4'".($sea_frmsort==4?" selected":"").">".$L['plu_frm_res_sort4']."</option>";
	$plugin_forum_res_sort .= "<option value='5'".($sea_frmsort==5?" selected":"").">".$L['plu_frm_res_sort5']."</option>";
	$plugin_forum_res_sort .= "</select>";

	// Ordering params
	$plugin_forum_res_desc = "<input type='radio' name='sea_frmsort2' value='DESC' id='frmsort2_DESC'".($sea_frmsort2=='ASC'?"":" checked")."> <label for='frmsort2_DESC'>".$L['plu_sort_desc']."</label>";
	$plugin_forum_res_asc = "<input type='radio' name='sea_frmsort2' value='ASC' id='frmsort2_ASC'".($sea_frmsort2=='ASC'?" checked":"")."> <label for='frmsort2_ASC'>".$L['plu_sort_asc']."</label>";

	// Extra search options
	$plugin_forum_search_names = "<input type='checkbox' name='sea_frmtitle' id='sea_frmtitle'".(($sea_frmtitle==1 || count($sea_frmsub)==0)?" checked='true'":"")." value='1' /> <label for='sea_frmtitle'>".$L['plu_frm_search_names']."</label>";
	$plugin_forum_search_post = "<input type='checkbox' name='sea_frmtext' id='sea_frmtext'".(($sea_frmtext==1 || count($sea_frmsub)==0)?" checked='true'":"")." value='1' /> <label for='sea_frmtext'>".$L['plu_frm_search_post']."</label>";
	$plugin_forum_search_answ = "<input type='checkbox' name='sea_frmreply' id='sea_frmreply'".($sea_frmreply==1?" checked='true'":"")." value='1' /> <label for='sea_frmreply'>".$L['plu_frm_search_answ']."</label>";

	// Output data array
	$t->assign(array(
		"PLUGIN_FORUM_SEC_LIST" => $plugin_forum_sec_list,
		"PLUGIN_FORUM_RES_SORT" => $plugin_forum_res_sort,
		"PLUGIN_FORUM_RES_DESC" => $plugin_forum_res_desc,
		"PLUGIN_FORUM_RES_ASC" => $plugin_forum_res_asc,
		"PLUGIN_FORUM_SEARCH_NAMES" => $plugin_forum_search_names,
		"PLUGIN_FORUM_SEARCH_POST" => $plugin_forum_search_post,
		"PLUGIN_FORUM_SEARCH_ANSW" => $plugin_forum_search_answ,
		"PLUGIN_FORUM_SEARCH_DATE" => $html_code_java.$html_code_date
	));

	// Parse the block
	$t->parse('MAIN.FORUMS_OPTIONS');

	// If in search query, continue
	if($a == 'search')
	{
		// Checking the sections array
		if($sea_frmsub[0]=='all')
		{
			// All sections
			$sqlsections = '';
		}
		else
		{
			// Walking through array
			foreach($sea_frmsub as $i => $k)
			{
				// Making new array
				$sections1[] = "s.fs_id='".sed_sql_prep($k)."'";
			}
			// Making SQL query
			$sqlsections = "AND (".implode(' OR ', $sections1).")";
		}

		// Handling param - Show only topics with replies
		if($sea_frmreply=='1')
		{ $frm_reply = "AND t.ft_postcount>1"; }

		// Handling param - Date/time
		if($within > 0)
		{
			// If searching in titles and posts or just posts
			if(($sea_frmtitle==1 && $sea_frmtext==1) || $sea_frmtext==1)
			{
				// In db - from creation date to the date updated
				$sqlsections2 = "AND p.fp_creation>=$from_mktime AND p.fp_updated<=$to_mktime";
			}
			// Othewise use topic dates
			else
			{
				// В логике - от даты создания, до даты обновления темы.
				// Хотя дата обновления = это дата последнего поста, обрабатывается запрос нормально.
				// Видимо в самом запросе ниже есть ограничение на круг поиска.
				$sqlsections2 = "AND t.ft_creationdate>=$from_mktime AND t.ft_updated<=$to_mktime";
			}
		}

		// Handling param - ordering
		if($sea_frmsort=='1')
		{ $orderby = "ft_updated ".$sea_frmsort2; }
		elseif($sea_frmsort=='2')
		{ $orderby = "ft_creationdate ".$sea_frmsort2; }
		elseif($sea_frmsort=='3')
		{ $orderby = "ft_title ".$sea_frmsort2; }
		elseif($sea_frmsort=='4')
		{ $orderby = "ft_postcount ".$sea_frmsort2; }
		elseif($sea_frmsort=='5')
		{ $orderby = "ft_viewcount ".$sea_frmsort2; }

		// Text output in results
		$text_from_sql = $cfg['plugin']['search']['showtext_ext'] == 1 ? "p.fp_text," : "";

		// Search in titles only
		if($sea_frmtitle=='1' && $sea_frmtext!='1')
		{

			$sql = sed_sql_query("SELECT p.fp_id, $text_from_sql t.ft_firstposterid, t.ft_firstpostername, t.ft_title, t.ft_id, t.ft_updated, s.fs_id, s.fs_title, s.fs_category
			 	FROM $db_forum_posts p, $db_forum_topics t, $db_forum_sections s
				WHERE 1 AND (t.ft_title LIKE '".sed_sql_prep($sqlsearch)."')
				AND p.fp_topicid=t.ft_id $frm_reply
				AND p.fp_sectionid=s.fs_id $sqlsections $sqlsections2
				GROUP BY t.ft_id ORDER BY $orderby
				LIMIT ".$cfg['plugin']['search']['maxitems_ext']);
			$items = mysql_num_rows($sql);
		}

		// Othewise search in post body
		elseif($sea_frmtext=='1' && $sea_frmtitle!='1')
		{
			$sql = sed_sql_query("SELECT p.fp_id, $text_from_sql p.fp_updated, t.ft_firstposterid, t.ft_firstpostername, t.ft_title, t.ft_id, s.fs_id, s.fs_title, s.fs_category
			 	FROM $db_forum_posts p, $db_forum_topics t, $db_forum_sections s
				WHERE 1 AND (p.fp_text LIKE '".sed_sql_prep($sqlsearch)."')
				AND p.fp_topicid=t.ft_id $frm_reply
				AND p.fp_sectionid=s.fs_id $sqlsections $sqlsections2
				GROUP BY t.ft_id ORDER BY $orderby
				LIMIT ".$cfg['plugin']['search']['maxitems_ext']);
			$items = mysql_num_rows($sql);
		}

		// Otherwise search both titles and body
		elseif($sea_frmtext=='1' && $sea_frmtitle=='1')
		{
			$sql = sed_sql_query("SELECT p.fp_id, $text_from_sql t.ft_firstposterid, t.ft_firstpostername, t.ft_title, t.ft_id, t.ft_updated, s.fs_id, s.fs_title, s.fs_category
			 	FROM $db_forum_posts p, $db_forum_topics t, $db_forum_sections s
				WHERE 1 AND (p.fp_text LIKE '".sed_sql_prep($sqlsearch)."' OR t.ft_title LIKE '".sed_sql_prep($sqlsearch)."')
				AND p.fp_topicid=t.ft_id $frm_reply
				AND p.fp_sectionid=s.fs_id $sqlsections $sqlsections2
				GROUP BY t.ft_id ORDER BY $orderby
				LIMIT ".$cfg['plugin']['search']['maxitems_ext']);
			$items = mysql_num_rows($sql);
		}

		// Othewise error message
		else
		{
			$error_string .= "<div>".$L['plu_notseltopmes']."</div>";
			unset($a);
			$items = 0;
		}

		// Display results if some were found
		if($items > 0)
		{
			while($row = mysql_fetch_array($sql))
			{
				// Display only what the user is allowed to see
				if(sed_auth('forums', $row['fs_id'], 'R'))
				{
					if($row['ft_updated'] > 0)


						$t->assign(array(
							"PLUGIN_FR_CATEGORY" => sed_build_forums($row['fs_id'], $row['fs_title'], $row['fs_category'], TRUE),
							"PLUGIN_FR_TITLE" => "<a href='forums.php?m=posts&amp;p=".$row['fp_id']."#".$row['fp_id']."'>".sed_cc($row['ft_title'])."</a>",
							"PLUGIN_FR_TEXT" => hw_clear_mark($row['fp_text'], 0, $words),
							"PLUGIN_FR_TIME" => $row['ft_updated'] > 0 ? @date($cfg['dateformat'], $row['ft_updated'] + $usr['timezone'] * 3600) : @date($cfg['dateformat'], $row['fp_updated'] + $usr['timezone'] * 3600)
						));
					$t->parse("MAIN.FORUMS_RESULTS.ITEM");
				}
			}

			// Making the output array
			$t->assign(array(
				"PLUGIN_FORUM_FOUND" => $L['plu_found']." ".($items == $cfg['plugin']['search']['maxitems_ext'] ? $L['plu_moreres'].' ' : '').$items." ".$L['plu_match']
			));

			// Parsing the block
			$t->parse('MAIN.FORUMS_RESULTS');
		}

		// Othewise tell that nothing was found
		else
		{
			$error_string .= "<div>".$L['plu_noneresult']."</div>";
		}
	}
}

// Otherwise if page tab selected and not disabled
elseif($tab=='pag' && !$cfg['disable_page'])
{
	$sea_pagtitle = sed_import('sea_pagtitle','P','INT');
	$sea_pagdesc = sed_import('sea_pagdesc','P','INT');
	$sea_pagtext = sed_import('sea_pagtext','P','INT');
	$sea_pagfile = sed_import('sea_pagfile','P','INT');
	$sea_pagsort = sed_import('sea_pagsort','P','INT');
	$sea_pagsort2 = sed_sql_prep(sed_import('sea_pagsort2','P','TXT'));
	$sea_pagsub = sed_import('sea_pagsub','P','ARR');

	// Making the category list
	$plugin_page_sec_list  = "<select multiple name='sea_pagsub[]' size='10' style='width:385px'>";
	$plugin_page_sec_list .= "<option value='all'".(($sea_pagsub[0]=='all' || count($sea_pagsub)==0)?" selected='selected'":"").">".$L['plu_allcategories']."</option>";
	foreach($sed_cat as $i =>$x)
	{
		if($i!='all' && $i!='system' && sed_auth('page', $i, 'R'))
		{
			if($x['group']==0)
			{
				$plugin_page_sec_list .= "<option value='".$i."'";

				// Select what has been selected
				if(count($sea_pagsub) > 0)
				{
					for($j=0; $j<count($sea_pagsub); $j++)
					{
						$plugin_page_sec_list .= $i == $sea_pagsub[$j] && $sea_pagsub[0] != 'all' ? " selected='selected'" : "";
					}
				}

				$plugin_page_sec_list .= ">".$x['tpath']."</option>";
			}
		}
	}
	$plugin_page_sec_list .= "</select>";

	// Result ordering list
	$plugin_page_res_sort  = "<select style='width:160px' name='sea_pagsort'>";
	$plugin_page_res_sort .= "<option value='1'".(($sea_pagsort==1 || !isset($sea_pagsort))?" selected":"").">".$L['plu_pag_res_sort1']."</option>";
	$plugin_page_res_sort .= "<option value='2'".($sea_pagsort==2?" selected":"").">".$L['plu_pag_res_sort2']."</option>";
	$plugin_page_res_sort .= "<option value='3'".($sea_pagsort==3?" selected":"").">".$L['plu_pag_res_sort3']."</option>";
	$plugin_page_res_sort .= "</select>";

	// Result ordering param
	$plugin_page_res_desc = "<input type='radio' name='sea_pagsort2' value='DESC' id='pagsort2_DESC'".($sea_pagsort2=='ASC'?"":" checked")."> <label for='pagsort2_DESC'>".$L['plu_sort_desc']."</label>";
	$plugin_page_res_asc = "<input type='radio' name='sea_pagsort2' value='ASC' id='pagsort2_ASC'".($sea_pagsort2=='ASC'?" checked":"")."> <label for='pagsort2_ASC'>".$L['plu_sort_asc']."</label>";

	// Extra search options
	$plugin_page_search_names = "<input type='checkbox' name='sea_pagtitle' id='sea_pagtitle'".(($sea_pagtitle==1 || count($sea_pagsub)==0)?" checked='true'":"")." value='1' /> <label for='sea_pagtitle'>".$L['plu_pag_search_names']."</label>";
	$plugin_page_search_desc = "<input type='checkbox' name='sea_pagdesc' id='sea_pagdesc'".(($sea_pagdesc==1 || count($sea_pagsub)==0)?" checked='true'":"")." value='1' /> <label for='sea_pagdesc'>".$L['plu_pag_search_desc']."</label>";
	$plugin_page_search_text = "<input type='checkbox' name='sea_pagtext' id='sea_pagtext'".(($sea_pagtext==1 || count($sea_pagsub)==0)?" checked='true'":"")." value='1' /> <label for='sea_pagtext'>".$L['plu_pag_search_text']."</label>";
	$plugin_page_search_file = "<input type='checkbox' name='sea_pagfile' id='sea_pagfile'".($sea_pagfile==1?" checked='true'":"")." value='1' /> <label for='sea_pagfile'>".$L['plu_pag_search_file']."</label>";

	// Output array
	$t->assign(array(
		"PLUGIN_PAGE_SEC_LIST" => $plugin_page_sec_list,
		"PLUGIN_PAGE_RES_SORT" => $plugin_page_res_sort,
		"PLUGIN_PAGE_RES_DESC" => $plugin_page_res_desc,
		"PLUGIN_PAGE_RES_ASC" => $plugin_page_res_asc,
		"PLUGIN_PAGE_SEARCH_NAMES" => $plugin_page_search_names,
		"PLUGIN_PAGE_SEARCH_DESC" => $plugin_page_search_desc,
		"PLUGIN_PAGE_SEARCH_TEXT" => $plugin_page_search_text,
		"PLUGIN_PAGE_SEARCH_FILE" => $plugin_page_search_file,
		"PLUGIN_PAGE_SEARCH_DATE" => $html_code_java.$html_code_date
	));

	// Parsing the block
	$t->parse('MAIN.PAGES_OPTIONS');

	// If search is active
	if($a == 'search')
	{
		// Check categories
		if($sea_pagsub[0]=='all')
		{
			// All categories
			$sqlsections = '';
		}
		else
		{
			// Walking through array
			foreach($sea_pagsub as $i => $k)
			{
				// Making new array
				$sections2[] = "page_cat='".sed_sql_prep($k)."'";
			}
			// SQL query
			$sqlsections = "AND (".implode(' OR ', $sections2).")";
		}

		// +TITLE -DESC -TEXT
		if($sea_pagtitle=='1' && $sea_pagdesc!='1' && $sea_pagtext!='1')
		{
			$pagsql = "(p.page_title LIKE '".$sqlsearch."') AND ";
		}
		// +TITLE +DESC -TEXT
		elseif($sea_pagtitle=='1' && $sea_pagdesc=='1' && $sea_pagtext!='1')
		{
			$pagsql = "(p.page_title LIKE '".$sqlsearch."' OR p.page_desc LIKE '".$sqlsearch."') AND ";
		}
		// +TITLE -DESC +TEXT
		elseif($sea_pagtitle=='1' && $sea_pagdesc!='1' && $sea_pagtext=='1')
		{
			$pagsql = "(p.page_title LIKE '".$sqlsearch."' OR p.page_text LIKE '".sed_sql_prep($sqlsearch)."') AND ";
		}
		// -TITLE +DESC -TEXT
		elseif($sea_pagtitle!='1' && $sea_pagdesc=='1' && $sea_pagtext!='1')
		{
			$pagsql = "(p.page_desc LIKE '".$sqlsearch."') AND ";
		}
		// -TITLE +DESC +TEXT
		elseif($sea_pagtitle!='1' && $sea_pagdesc=='1' && $sea_pagtext=='1')
		{
			$pagsql = "(p.page_desc LIKE '".$sqlsearch."' OR p.page_text LIKE '".sed_sql_prep($sqlsearch)."') AND ";
		}
		// -TITLE -DESC +TEXT
		elseif($sea_pagtitle!='1' && $sea_pagdesc!='1' && $sea_pagtext=='1')
		{
			$pagsql = "(p.page_text LIKE '".sed_sql_prep($sqlsearch)."') AND ";
		}
		// +TITLE +DESC +TEXT
		elseif($sea_pagtitle=='1' && $sea_pagdesc=='1' && $sea_pagtext=='1')
		{
			$pagsql = "(p.page_text LIKE '".$sqlsearch."' OR p.page_title LIKE '".$sqlsearch."' OR p.page_desc LIKE '".sed_sql_prep($sqlsearch)."') AND ";
		}

		// Otherwise error message
		else
		{
			$error_string .= "<div>".$L['plu_notseloption']."</div>";
			unset($a, $pagsql);
		}

		// Handling param - date/time
		if($within > 0)
		{
			$sqlsections2 = "AND page_date>=$from_mktime AND page_date<=$to_mktime";
		}

		// Handling param - ordering
		if ($sea_pagsort=='1')
		{ $orderby = "page_date ".$sea_pagsort2; }
		elseif ($sea_pagsort=='2')
		{ $orderby = "page_title ".$sea_pagsort2; }
		elseif ($sea_pagsort=='3')
		{ $orderby = "page_count ".$sea_pagsort2; }

		// If it was not canceled, continue
		if($a == 'search')
		{
			// Display text in results
			$text_from_sql = $cfg['plugin']['search']['showtext_ext'] == 1 ? "page_text, page_type," : "";

			// Only pages with files
			if($sea_pagfile==1)
			{
				$sql = sed_sql_query("SELECT page_id, page_date, page_ownerid, page_title, $text_from_sql page_cat from $db_pages p, $db_structure s
		   	 		WHERE $pagsql
					p.page_file='1'
			     	 		AND p.page_state='0'
			       	 	AND p.page_cat=s.structure_code
			       	 	AND p.page_cat NOT LIKE 'system' $sqlsections2
					$sqlsections ORDER BY $orderby
			       	 	LIMIT ".$cfg['plugin']['search']['maxitems_ext']);
				$items = mysql_num_rows($sql);
			}
			// Otherwise everything
			else
			{
				$sql = sed_sql_query("SELECT page_id, page_date, page_ownerid, page_title, $text_from_sql page_cat from $db_pages p, $db_structure s
					WHERE $pagsql
					p.page_state='0'
					AND p.page_cat=s.structure_code
					AND p.page_cat NOT LIKE 'system' $sqlsections2
					$sqlsections ORDER BY $orderby
					LIMIT ".$cfg['plugin']['search']['maxitems_ext']);
				$items = mysql_num_rows($sql);
			}
		}

		// Display results if something was found
		if($items > 0)
		{
			while($row = mysql_fetch_array($sql))
			{
				// Display only what the user is allowed to see
				if(sed_auth('page', $row['page_cat'], 'R'))
				{
					$t->assign(array(
						"PLUGIN_PR_CATEGORY" => "<a href='list.php?c=".$row['page_cat']."'>".$sed_cat[$row['page_cat']]['tpath']."</a>",
						"PLUGIN_PR_TITLE" => "<a href='page.php?id=".$row['page_id']."'>".sed_cc($row['page_title'])."</a>",
						"PLUGIN_PR_TEXT" => hw_clear_mark($row['page_text'], $row['page_type'], $words),
						"PLUGIN_PR_TIME" => @date($cfg['dateformat'], $row['page_date'] + $usr['timezone'] * 3600)
					));
					$t->parse("MAIN.PAGES_RESULTS.ITEM");
				}
			}

			// Output array
			$t->assign(array(
				"PLUGIN_PAGE_FOUND" => $L['plu_found']." ".($items == $cfg['plugin']['search']['maxitems_ext'] ? $L['plu_moreres'].' ' : '').$items." ".$L['plu_match']
			));

			// Parsing the block
			$t->parse('MAIN.PAGES_RESULTS');
		}

		// Otherwise nothing was found message
		else
		{
			$error_string .= "<div>".$L['plu_noneresult']."</div>";
		}
	}
}

// Otherwise use common search
else
{
	// If forums are enabled
	if(!$cfg['disable_forums'])
	{
		$sea_frmtitle = sed_import('sea_frmtitle','P','INT');
		$sea_frmtext = sed_import('sea_frmtext','P','INT');
		$sea_frmreply = sed_import('sea_frmreply','P','INT');
		$sea_frmsort = sed_import('sea_frmsort','P','INT');
		$sea_frmsort2 = sed_sql_prep(sed_import('sea_frmsort2','P','TXT'));
		$sea_frmsub = sed_import('sea_frmsub','P','ARR');

		$sql1 = sed_sql_query("SELECT s.fs_id, s.fs_title, s.fs_category FROM $db_forum_sections AS s
			LEFT JOIN $db_forum_structure AS n ON n.fn_code=s.fs_category
			ORDER by fn_path ASC, fs_order ASC");

		// Sections list
		$plugin_forum_sec_list  = "<select multiple name='sea_frmsub[]' size='6' style='width:385px'>";
		$plugin_forum_sec_list .= "<option value='all'".(($sea_frmsub[0]=='all' || count($sea_frmsub)==0)?" selected='selected'":"").">".$L['plu_allsections']."</option>";
		while($row1 = mysql_fetch_array($sql1))
		{
			if(sed_auth('forums', $row1['fs_id'], 'R'))
			{
				$plugin_forum_sec_list .= "<option value='".$row1['fs_id']."'";

				// Apply selection
				if(count($sea_frmsub) > 0)
				{
					for($i=0; $i<count($sea_frmsub); $i++)
					{
						$plugin_forum_sec_list .= $row1['fs_id'] == $sea_frmsub[$i] && $sea_frmsub[0] != 'all' ? " selected='selected'" : "";
					}
				}

				$plugin_forum_sec_list .= ">".sed_build_forums($row1['fs_id'], $row1['fs_title'], $row1['fs_category'], FALSE)."</option>";
			}
		}
		$plugin_forum_sec_list .= "</select>";

		// Extra options
		$plugin_forum_search_names = "<input type='checkbox' name='sea_frmtitle' id='sea_frmtitle'".(($sea_frmtitle==1 || count($sea_frmsub)==0)?" checked='true'":"")." value='1' /> <label for='sea_frmtitle'>".$L['plu_frm_search_names']."</label>";
		$plugin_forum_search_post = "<input type='checkbox' name='sea_frmtext' id='sea_frmtext'".(($sea_frmtext==1 || count($sea_frmsub)==0)?" checked='true'":"")." value='1' /> <label for='sea_frmtext'>".$L['plu_frm_search_post']."</label>";

		// Output
		$t->assign(array(
			"PLUGIN_FORUM_SEC_LIST" => $plugin_forum_sec_list,
			"PLUGIN_FORUM_SEARCH_NAMES" => $plugin_forum_search_names,
			"PLUGIN_FORUM_SEARCH_POST" => $plugin_forum_search_post,
		));
	}

	// If pages are enabled
	if(!$cfg['disable_page'])
	{
		$sea_pagtitle = sed_import('sea_pagtitle','P','INT');
		$sea_pagdesc = sed_import('sea_pagdesc','P','INT');
		$sea_pagtext = sed_import('sea_pagtext','P','INT');
		$sea_pagfile = sed_import('sea_pagfile','P','INT');
		$sea_pagsort = sed_import('sea_pagsort','P','INT');
		$sea_pagsort2 = sed_sql_prep(sed_import('sea_pagsort2','P','TXT'));
		$sea_pagsub = sed_import('sea_pagsub','P','ARR');

		// Category list
		$plugin_page_sec_list  = "<select multiple name='sea_pagsub[]' size='6' style='width:385px'>";
		$plugin_page_sec_list .= "<option value='all'".(($sea_pagsub[0]=='all' || count($sea_pagsub)==0)?" selected='selected'":"").">".$L['plu_allcategories']."</option>";
		foreach($sed_cat as $i =>$x)
		{
			if($i!='all' && $i!='system' && sed_auth('page', $i, 'R'))
			{
				if($x['group']==0)
				{
					$plugin_page_sec_list .= "<option value='".$i."'";

					// Apply selection
					if(count($sea_pagsub) > 0)
					{
						for($j=0; $j<count($sea_pagsub); $j++)
						{
							$plugin_page_sec_list .= $i == $sea_pagsub[$j] && $sea_pagsub[0] != 'all' ? " selected='selected'" : "";
						}
					}

					$plugin_page_sec_list .= ">".$x['tpath']."</option>";
				}
			}
		}
		$plugin_page_sec_list .= "</select>";

		// Extra options
		$plugin_page_search_names = "<input type='checkbox' name='sea_pagtitle' id='sea_pagtitle'".(($sea_pagtitle==1 || count($sea_pagsub)==0)?" checked='true'":"")." value='1' /> <label for='sea_pagtitle'>".$L['plu_pag_search_names']."</label>";
		$plugin_page_search_desc = "<input type='checkbox' name='sea_pagdesc' id='sea_pagdesc'".(($sea_pagdesc==1 || count($sea_pagsub)==0)?" checked='true'":"")." value='1' /> <label for='sea_pagdesc'>".$L['plu_pag_search_desc']."</label>";
		$plugin_page_search_text = "<input type='checkbox' name='sea_pagtext' id='sea_pagtext'".(($sea_pagtext==1 || count($sea_pagsub)==0)?" checked='true'":"")." value='1' /> <label for='sea_pagtext'>".$L['plu_pag_search_text']."</label>";

		// Output array
		$t->assign(array(
			"PLUGIN_PAGE_SEC_LIST" => $plugin_page_sec_list,
			"PLUGIN_PAGE_SEARCH_NAMES" => $plugin_page_search_names,
			"PLUGIN_PAGE_SEARCH_DESC" => $plugin_page_search_desc,
			"PLUGIN_PAGE_SEARCH_TEXT" => $plugin_page_search_text,
		));
	}

	// Parse the block
	if(!$cfg['disable_forums'] || !$cfg['disable_page'])
	{
		$t->parse('MAIN.EASY_OPTIONS');
	}

	// If search is active
	if($a == 'search' && strlen($sq) > 0)
	{
		if(!$cfg['disable_forums'])
		{
			// Check sections
			if($sea_frmsub[0]=='all')
			{
				// All sections
				$sqlsections = '';
			}
			else
			{
				// Walking through array
				foreach($sea_frmsub as $i => $k)
				{
					// Making new array.
					$sections1[] = "s.fs_id='".sed_sql_prep($k)."'";
				}
				// SQL query
				$sqlsections = "AND (".implode(' OR ', $sections1).")";
			}

			// Display text in results
			$text_from_sql = $cfg['plugin']['search']['showtext'] == 1 ? "p.fp_text," : "";

			// Search titles only
			if($sea_frmtitle=='1' && $sea_frmtext!='1')
			{
				$sql = sed_sql_query("SELECT p.fp_id, $text_from_sql t.ft_firstposterid, t.ft_firstpostername, t.ft_title, t.ft_id, t.ft_updated, s.fs_id, s.fs_title, s.fs_category
				 	FROM $db_forum_posts p, $db_forum_topics t, $db_forum_sections s
					WHERE 1 AND (t.ft_title LIKE '".sed_sql_prep($sqlsearch)."')
					AND p.fp_topicid=t.ft_id
					AND p.fp_sectionid=s.fs_id $sqlsections
					GROUP BY t.ft_id ORDER BY fp_id DESC
					LIMIT ".$cfg['plugin']['search']['maxitems']);
				$items1 = mysql_num_rows($sql);
			}

			// Bodies only
			elseif($sea_frmtext=='1' && $sea_frmtitle!='1')
			{
				$sql = sed_sql_query("SELECT p.fp_id, $text_from_sql p.fp_updated, t.ft_firstposterid, t.ft_firstpostername, t.ft_title, t.ft_id, s.fs_id, s.fs_title, s.fs_category
				 	FROM $db_forum_posts p, $db_forum_topics t, $db_forum_sections s
					WHERE 1 AND (p.fp_text LIKE '".sed_sql_prep($sqlsearch)."')
					AND p.fp_topicid=t.ft_id
					AND p.fp_sectionid=s.fs_id $sqlsections
					GROUP BY t.ft_id ORDER BY fp_id DESC
					LIMIT ".$cfg['plugin']['search']['maxitems']);
				$items1 = mysql_num_rows($sql);
			}

			// Title+body
			elseif($sea_frmtext=='1' && $sea_frmtitle=='1')
			{
				$sql = sed_sql_query("SELECT p.fp_id, $text_from_sql t.ft_firstposterid, t.ft_firstpostername, t.ft_title, t.ft_id, s.fs_id, t.ft_updated, s.fs_title, s.fs_category
				 	FROM $db_forum_posts p, $db_forum_topics t, $db_forum_sections s
					WHERE 1 AND (p.fp_text LIKE '".sed_sql_prep($sqlsearch)."' OR t.ft_title LIKE '".sed_sql_prep($sqlsearch)."')
					AND p.fp_topicid=t.ft_id
					AND p.fp_sectionid=s.fs_id $sqlsections
					GROUP BY t.ft_id ORDER BY fp_id DESC
					LIMIT ".$cfg['plugin']['search']['maxitems']);
				$items1 = mysql_num_rows($sql);
			}

			// Otherwise error message
			else
			{
				$error_string .= "<div>".$L['plu_notseltopmes']."</div>";
				unset($a);
				$items1 = 0;
			}

			// Display results if something was found
			if($items1 > 0)
			{
				while($row = mysql_fetch_array($sql))
				{
					// Check permissions
					if(sed_auth('forums', $row['fs_id'], 'R'))
					{
						$t->assign(array(
							"PLUGIN_FR_CATEGORY" => sed_build_forums($row['fs_id'], $row['fs_title'], $row['fs_category'], TRUE),
							"PLUGIN_FR_TITLE" => "<a href='forums.php?m=posts&amp;p=".$row['fp_id']."#".$row['fp_id']."'>".sed_cc($row['ft_title'])."</a>",
							"PLUGIN_FR_TEXT" => hw_clear_mark($row['fp_text'], 0, $words),
							"PLUGIN_FR_TIME" => $row['ft_updated'] > 0 ? @date($cfg['dateformat'], $row['ft_updated'] + $usr['timezone'] * 3600) : @date($cfg['dateformat'], $row['fp_updated'] + $usr['timezone'] * 3600)
						));
						$t->parse("MAIN.EASY_FORUMS_RESULTS.ITEM");
					}
				}

				// Output
				$t->assign(array(
					"PLUGIN_EASY_FORUM_FOUND" => $L['plu_found']." ".($items1 == $cfg['plugin']['search']['maxitems'] ? $L['plu_moreres'].' ' : '').$items1." ".$L['plu_match']
				));

				$t->parse('MAIN.EASY_FORUMS_RESULTS');
			}
		}

		// If pages are enabled
		if(!$cfg['disable_page'])
		{
			// Check the categories
			if($sea_pagsub[0]=='all')
			{
				// All categories
				$sqlsections = '';
			}
			else
			{
				// Walking through array
				foreach($sea_pagsub as $i => $k)
				{
					// Making new array
					$sections2[] = "page_cat='".sed_sql_prep($k)."'";
				}
				// SQL query
				$sqlsections = "AND (".implode(' OR ', $sections2).")";
			}

			// +TITLE -DESC -TEXT
			if($sea_pagtitle=='1' && $sea_pagdesc!='1' && $sea_pagtext!='1')
			{
				$pagsql = "(p.page_title LIKE '".$sqlsearch."') AND ";
			}
			// +TITLE +DESC -TEXT
			elseif($sea_pagtitle=='1' && $sea_pagdesc=='1' && $sea_pagtext!='1')
			{
				$pagsql = "(p.page_title LIKE '".$sqlsearch."' OR p.page_desc LIKE '".$sqlsearch."') AND ";
			}
			// +TITLE -DESC +TEXT
			elseif($sea_pagtitle=='1' && $sea_pagdesc!='1' && $sea_pagtext=='1')
			{
				$pagsql = "(p.page_title LIKE '".$sqlsearch."' OR p.page_text LIKE '".sed_sql_prep($sqlsearch)."') AND ";
			}
			// -TITLE +DESC -TEXT
			elseif($sea_pagtitle!='1' && $sea_pagdesc=='1' && $sea_pagtext!='1')
			{
				$pagsql = "(p.page_desc LIKE '".$sqlsearch."') AND ";
			}
			// -TITLE +DESC +TEXT
			elseif($sea_pagtitle!='1' && $sea_pagdesc=='1' && $sea_pagtext=='1')
			{
				$pagsql = "(p.page_desc LIKE '".$sqlsearch."' OR p.page_text LIKE '".sed_sql_prep($sqlsearch)."') AND ";
			}
			// -TITLE -DESC +TEXT
			elseif($sea_pagtitle!='1' && $sea_pagdesc!='1' && $sea_pagtext=='1')
			{
				$pagsql = "(p.page_text LIKE '".sed_sql_prep($sqlsearch)."') AND ";
			}
			// +TITLE +DESC +TEXT
			elseif($sea_pagtitle=='1' && $sea_pagdesc=='1' && $sea_pagtext=='1')
			{
				$pagsql = "(p.page_text LIKE '".$sqlsearch."' OR p.page_title LIKE '".$sqlsearch."' OR p.page_desc LIKE '".sed_sql_prep($sqlsearch)."') AND ";
			}

			// Otherwise error message
			else
			{
				$error_string .= "<div>".$L['plu_notseloption']."</div>";
				unset($a, $pagsql);
			}

			// Continue if not cancelled
			if($a == 'search')
			{
				// Display text in results
				$text_from_sql = $cfg['plugin']['search']['showtext'] == 1 ? "page_text, page_type," : "";

				$sql = sed_sql_query("SELECT page_id, page_date, page_ownerid, page_title, $text_from_sql page_cat from $db_pages p, $db_structure s
					WHERE $pagsql
					p.page_state='0'
					AND p.page_cat=s.structure_code
					AND p.page_cat NOT LIKE 'system'
					$sqlsections ORDER BY page_cat ASC, page_title ASC
					LIMIT ".$cfg['plugin']['search']['maxitems']);
				$items2 = mysql_num_rows($sql);

				// Display results if something was found
				if($items2 > 0)
				{
					while($row = mysql_fetch_array($sql))
					{
						// Apply permissions
						if(sed_auth('page', $row['page_cat'], 'R'))
						{
							$t->assign(array(
								"PLUGIN_PR_CATEGORY" => "<a href='list.php?c=".$row['page_cat']."'>".$sed_cat[$row['page_cat']]['tpath']."</a>",
								"PLUGIN_PR_TITLE" => "<a href='page.php?id=".$row['page_id']."'>".sed_cc($row['page_title'])."</a>",
								"PLUGIN_PR_TEXT" => hw_clear_mark($row['page_text'], $row['page_type'], $words),
								"PLUGIN_PR_TIME" => @date($cfg['dateformat'], $row['page_date'] + $usr['timezone'] * 3600)
							));
							$t->parse("MAIN.EASY_PAGES_RESULTS.ITEM");
						}
					}

					// Output
					$t->assign(array(
						"PLUGIN_EASY_PAGE_FOUND" => $L['plu_found']." ".($items2 == $cfg['plugin']['search']['maxitems'] ? $L['plu_moreres'].' ' : '').$items2." ".$L['plu_match']
					));

					$t->parse('MAIN.EASY_PAGES_RESULTS');
				}
			}
		}

		// Common "nothing was found" message
		if(!$items1 > 0 && !$items2 > 0)
		{
			$error_string .= "<div>".$L['plu_noneresult']."</div>";
		}
	}
}

// Output
$t->assign(array(
	"PLUGIN_TITLE" => $plugin_title,
	"PLUGIN_SEARCH_TEXT" => "<input type='text' name='sq' style='width:310px; padding:2px 0; margin:0' value='".sed_cc($sq)."' size='32' maxlength='".$cfg['plugin']['search']['maxsigns']."' />",
	"PLUGIN_SEARCH_KEY" => "<input type='submit' value='".$L['plu_search_key']."' style='width:70px' />",
	"PLUGIN_ERROR" => $error_string
));

// Display warnings and error messages
if(strlen($error_string))
{ $t->parse('MAIN.ERROR'); }

// Вывод инфы для отладки.
// echo "<pre>",htmlspecialchars(print_r($_POST,true)),"</pre>";
// echo "<pre>",htmlspecialchars(print_r($sed_cat,true)),"</pre>";
// echo "<pre>",htmlspecialchars(print_r($words,true)),"</pre>";

?>