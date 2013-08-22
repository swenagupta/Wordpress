<?php
/*

-=[ Copyright Notice ]=-

    Copyright 2009 Nitin Reddy  (email : k_nitin_r {at} antispamyahoo.co.in , k.nitin.r {at} antispamgmail.com)
                                    Replace the {at} with @ and remove the antispam for my email address
                                    

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
	
	WARRANTY AND CUSTOMIZATION
	Warranty and customization for this software is available. Contact the
	author for more details.
*/

/*

-=[ Notes ]=-
	
	If the user hasn't set the custom sort order but has selected the Custom order
	option, we order the items by ID in ascending order.
	
	You might want your DBA to create an index on the knr_author_order field for better performance
*/

/*

-=[ Wish List ]=-

Make the following customizable
- Display name instead of First Name + Last Name
- gravatar size
- Dropdown CSS class

Add support for using AuthorPic as avatar

Change the widget options 'Show As Ordered List' and 'Show As Dropdown' into a dropdown list 
with the following options:
- Unordered list
- Ordered list
- Dropdown list

Create tutorials to show the possibilities when using KNR Author List (CSS 'tricks')

Use a cleaner way of passing parameters to functions. (array? array+extract? class? 
For an array, options array or new array?).
*/

//TODO: Make post-count optional


define('CR', "\n");
$loadedOptions = null;

$knral_defaults = array(
	'title' => '',
	'includeAuthorsWoPosts' => false,
	'includeContributors' => false,
	'includeAdmin' => false,
	'includeEditors' => false,
	'authorLimit' => 0,
	'sort' => null,
	'sortReverse' => null,
	'unorderedListClass' => null,
	'listItemClass' => null,
	'spanCountClass' => null,
	'spanAuthorClass' => null,
	'authorLinkClass' => null,
	'showCount' => null,
	'countBeforeName' => null,
	'moreAuthorsLink' => null,
	'moreAuthorsText' => null,
	'showAvatarMode' => null,
	'showAsDropdown' => false,
	'dropdownUnselectedText' => '',
	'showAsOrderedList' => false,
	'noShowUrlFilter' => null
);

function getAuthorListData(
    $aIncludeAuthorsWithoutPosts = true,    //affects the SQL
    $aIncludeEditors = true,                //affects the SQL
    $aIncludeAdministrators = true,         //affects the SQL
    $aAuthorLimit = -1,                     //affects the SQL
    $aIncludeContributors = true,           //affects the SQL
    $aOrderBy = null,                       //affects the SQL
    $aOrderReverse = false,                 //affects the SQL
	$behaviorShowMoreLink = false,			//affects the SQL (returns an extra row to determine if we should show the more link)
    $aNoPostCount = 0                       //Reserved for future use to perform query optimization
) {
    global $wpdb;

    $behaviorSqlJoinMode_Post = $aIncludeAuthorsWithoutPosts ? 'LEFT OUTER' : '';
    $behaviorSqlFilter_MatchEditors = $aIncludeEditors ? " OR capa.meta_value LIKE '%editor%'" : '';
    $behaviorSqlFilter_MatchAdmins = $aIncludeAdministrators ? " OR capa.meta_value LIKE '%admin%'" : '';
    $behaviorSqlFilter_MatchContribs = $aIncludeContributors ? " OR capa.meta_value LIKE '%contrib%'" : '';
    $behaviorSqlFilter_Limit = $aAuthorLimit > 0 ? "LIMIT {$aAuthorLimit}" : '';
    $aAuthorLimitPlusOne = $aAuthorLimit + 1;
    $behaviorSqlFilter_LimitPlusOne = $aAuthorLimit > 0 ? "LIMIT {$aAuthorLimitPlusOne}" : '';
    //$behaviorShowMoreLink = ($aMoreAuthorsLink != '');
    if ($aAuthorLimit > 0 && (!$behaviorShowMoreLink)) $behaviorSqlFilter_LimitPlusOne = $behaviorSqlFilter_Limit;

    //Build the ORDER BY query for different sort options
    if ('fname' == $aOrderBy || 'lname' == $aOrderBy || 'post_count' == $aOrderBy || 'ID' == $aOrderBy || 'display_name' ==  $aOrderBy)
        $behaviorSqlSort = 'ORDER BY '.$aOrderBy;
    else if ('flname' == $aOrderBy)
        $behaviorSqlSort = 'ORDER BY fname, lname';
    else if ('lfname' == $aOrderBy)
        $behaviorSqlSort = 'ORDER BY lname, fname';
    else if ('custom' == $aOrderBy)
        $behaviorSqlSort = 'ORDER BY usr.knr_author_order, ID';
    if ('' != $behaviorSqlSort && 'custom' != $behaviorSqlSort) $behaviorSqlSort .= ($aOrderReverse ? ' DESC' : '');
    
    $userrows = $wpdb->get_results("
SELECT
usr.ID,
usr.user_email,
fnametab.meta_value AS fname,
lnametab.meta_value as lname,
cnt.post_count
FROM {$wpdb->users} usr
JOIN {$wpdb->usermeta} capa ON usr.ID = capa.user_id AND capa.meta_key LIKE '%capabilities'
LEFT OUTER JOIN {$wpdb->usermeta} fnametab ON usr.ID = fnametab.user_id AND fnametab.meta_key = 'first_name'
LEFT OUTER JOIN {$wpdb->usermeta} lnametab ON usr.ID = lnametab.user_id AND lnametab.meta_key = 'last_name'
{$behaviorSqlJoinMode_Post} JOIN (
    SELECT post_author, COUNT(1) post_count
    FROM {$wpdb->posts}
    WHERE post_type='post'
    AND post_status='publish'
    GROUP BY post_author
) cnt ON usr.ID = cnt.post_author
WHERE capa.meta_value LIKE '%author%'{$behaviorSqlFilter_MatchContribs}{$behaviorSqlFilter_MatchEditors}{$behaviorSqlFilter_MatchAdmins}
{$behaviorSqlSort}
{$behaviorSqlFilter_LimitPlusOne}
    ");
	
    return (array) $userrows;    
}

function getAvatar($aMode, $aId, $aSize) {
    if (1 == $aMode) {
        return get_avatar($aId, $aSize);
    } else {
        return '';
    }
}

function getAuthorInfo_ddl_knrAuthorList(
    $aIncludeAuthorsWithoutPosts = true,    //affects the SQL
    $aIncludeEditors = true,                //affects the SQL
    $aIncludeAdministrators = true,         //affects the SQL
    $aAuthorLimit = -1,                     //affects the SQL
    $aIncludeContributors = true,           //affects the SQL
    $aOrderBy = null,                       //affects the SQL
    $aOrderReverse = false,                 //affects the SQL
    $aUnorderedListClass = '',
    $aListItemClass = '',
    $aSpanCountClass = '',
    $aSpanAuthorClass = '',
    $aAuthorLinkClass = '',
    $aShowCount = true,
    $aCountBeforeName = false,
    $aMoreAuthorsLink = '',
    $aMoreAuthorsText = '...',
    $aShowAvatarMode = 0,
    $aDropdownUnselectedText = '',
    $aShowAsOrderedList = false
    ) {
    $behaviorShowCount = $aShowCount;
    $behaviorCountBeforeName = $aCountBeforeName;    
    
    $userrows_arr = getAuthorListData($aIncludeAuthorsWithoutPosts, $aIncludeEditors, $aIncludeAdministrators, $aAuthorLimit, $aIncludeContributors, $aOrderBy, $aOrderReverse, ($aMoreAuthorsLink != ''), 0);

    $blogUrl = get_bloginfo('url');

    $knrAuthListDdl_Script = '<script>
    function knrAuthorListDdl() {
        mapper = new Object();        
    ';

    echo '<select id="knrAuthorListDdl" name="knrAuthorListDdl" onchange="knrAuthorListDdl();">';
    echo '<option value="" selected="selected">'.$aDropdownUnselectedText.'</option>';

	$behaviorShowMoreLink = ($aMoreAuthorsLink != '');    
    
    //loop over each of the rows in the result set
    for ($i = 0; $i < count($userrows_arr); $i++) {
        
        //We have an author limit set and we've reached it
        if ($aAuthorLimit > 0 && $i == $aAuthorLimit) {
            
            //if we're supposed to show the more link
            if ($behaviorShowMoreLink) {
                $knrAuthListDdl_Script .= 'mapper["more"] = "'. htmlspecialchars($aMoreAuthorsLink).'";
                ';
                            
                $moreMarkup = "
                    <option value=\"more\">
                    $aMoreAuthorsText
                    </option>
                ";
            
                //output the actual 'more' markup
                echo $moreMarkup;
            }
            
            break;
        }
        
        $row = $userrows_arr[$i];
        $row->post_count=$row->post_count?$row->post_count:0;
        $authorUrl = $blogUrl . '/?author=' . $row->ID;

        //set the mapping for the Javascript
        $knrAuthListDdl_Script .= 'mapper["a' . $row->ID . '"] = "'. htmlspecialchars($authorUrl).'";
        ';
        
        //We shouldn't need this since the user must have entered a first name and last name, but just in case
        if ($row->lname == null && $row->fname == null) {
            $tempUser = get_userdata($row->ID);
            if ($tempUser->nickname != null)
                $row->fname = $tempUser->nickname;
            else
                $row->fname = $tempUser->user_login;
        }
        
        
        $authorNameMarkup = "
            {$row->fname} {$row->lname}
        ";
        
        
        $countMarkup='';
        if ($behaviorShowCount) {
            $countMarkup = '('.$row->post_count.')';
        }
        
        echo '
        <option value="'.$row->ID.'">'.
        ($behaviorCountBeforeName?($countMarkup.' '.$authorNameMarkup):($authorNameMarkup.' '.$countMarkup))
        .'</option>'.CR;
    }
    
    echo '</select>';
    $knrAuthListDdl_Script .= '
    theSelAuthor = document.getElementById("knrAuthorListDdl").value;
        if ("" != theSelAuthor) {
			if ("more" != theSelAuthor)
				theSelAuthor = "a"+theSelAuthor;
            location.href = mapper[theSelAuthor];
        }
    }</script>';
    echo $knrAuthListDdl_Script;
}

//Output the list items (LI tags)
function getAuthorInfo_knrAuthorList(
	$aIncludeAuthorsWithoutPosts = true,    //affects the SQL
	$aIncludeEditors = true,                //affects the SQL
	$aIncludeAdministrators = true,         //affects the SQL
	$aAuthorLimit = -1,                     //affects the SQL
	$aIncludeContributors = true,           //affects the SQL
	$aOrderBy = null,                       //affects the SQL
	$aOrderReverse = false,                 //affects the SQL
	$aUnorderedListClass = '',
	$aListItemClass = '',
	$aSpanCountClass = '',
	$aSpanAuthorClass = '',
	$aAuthorLinkClass = '',
	$aShowCount = true,
	$aCountBeforeName = false,
	$aMoreAuthorsLink = '',
	$aMoreAuthorsText = '...',
    $aShowAvatarMode = 0,
    $aDropdownUnselectedText = '',
    $aShowAsOrderedList = false
	) {

    if ($aShowAsOrderedList)    
	if (isset($aUnorderedListClass) && '' != $aUnorderedListClass)
	        echo '                        <ol class="'.$aUnorderedListClass.'">'.CR;
	else
	        echo '                        <ol>'.CR;
    else
	if (isset($aUnorderedListClass) && '' != $aUnorderedListClass)
	        echo '                        <ul class="'.$aUnorderedListClass.'">'.CR;
	else
        	echo '                        <ul>'.CR;
        
	$behaviorShowCount = $aShowCount;
	$behaviorCountBeforeName = $aCountBeforeName;	
	
	$markupListItemClass = '';
	if (isset($aListItemClass) && '' != $aListItemClass)
		$markupListItemClass = ' class="' . $aListItemClass . '"';

	$markupAuthorLinkClass = '';
	if (isset($aAuthorLinkClass) && '' != $aAuthorLinkClass)
		$markupAuthorLinkClass = ' class="' . $aAuthorLinkClass . '"';

	$markupSpanCountClass = '';
	if (isset($aSpanCountClass) && '' != $aSpanCountClass)
		$markupSpanCountClass = ' class="' . $aSpanCountClass . '"';

	$markupSpanAuthorClass = '';
	if (isset($aSpanAuthorClass) && '' != $aSpanAuthorClass)
		$markupSpanAuthorClass = ' class="' . $aSpanAuthorClass . '"';

	$userrows_arr = getAuthorListData($aIncludeAuthorsWithoutPosts, $aIncludeEditors, $aIncludeAdministrators, $aAuthorLimit, $aIncludeContributors, $aOrderBy, $aOrderReverse, ($aMoreAuthorsLink != ''), 0);

    $blogUrl = get_bloginfo('url');
    $outerMoreLink = ''; //used for ordered lists

	$behaviorShowMoreLink = ($aMoreAuthorsLink != '');    
	
    //loop over each of the rows in the result set
	for ($i = 0; $i < count($userrows_arr); $i++) {
        
        //We have an author limit set and we've reached it
		if ($aAuthorLimit > 0 && $i == $aAuthorLimit) {
			
            //if we're supposed to show the more link
            if ($behaviorShowMoreLink) {			
				$authorNameMarkup = "
					<a href=\"$aMoreAuthorsLink\"{$markupAuthorLinkClass}>
					$aMoreAuthorsText
					</a>
				";
				if ('' != $markupSpanAuthorClass)
					$authorNameMarkup = '<span'.$markupSpanAuthorClass.'>'.$authorNameMarkup.'</span>';
				
				
				$countMarkup='';
			
                if ($aShowAsOrderedList)
                    $outerMoreLink = '<p>'.$authorNameMarkup.'</p>';
                else
            	    //output the actual 'more' markup
				    echo '
				    <li'.$markupListItemClass.'>'.
				    $authorNameMarkup
				    .'</li>'.CR;
                
			}
			
			break;
		}
		
		$row = $userrows_arr[$i];
	//foreach((array) $userrows as $row) {
		$row->post_count=$row->post_count?$row->post_count:0;
		//$authorUrl = $blogUrl . '/?author=' . $row->ID;
		$authorUrl = get_author_posts_url($row->ID);
        
		//We shouldn't need this since the user must have entered a first name and last name, but just in case
		if ($row->lname == null && $row->fname == null) {
			$tempUser = get_userdata($row->ID);
			if ($tempUser->nickname != null)
				$row->fname = $tempUser->nickname;
			else
				$row->fname = $tempUser->user_login;
		}
		
		
		$authorNameMarkup = "
			<a href=\"$authorUrl\" title=\"Posts by {$row->fname} {$row->lname}\"{$markupAuthorLinkClass}>
			{$row->fname} {$row->lname}
			</a>
		";
		if ('' != $markupSpanAuthorClass)
			$authorNameMarkup = '<span'.$markupSpanAuthorClass.'>'.$authorNameMarkup.'</span>';
		
		
		$countMarkup='';
		if ($behaviorShowCount) {
			$countMarkup = '('.$row->post_count.')';
			if ('' != $markupSpanCountClass)
				$countMarkup = '<span'.$markupSpanCountClass.'>'.$countMarkup.'</span>';
		}
		
        if (0 != $aShowAvatarMode)
        $avatarPic = getAvatar($aShowAvatarMode, $row->user_email, 16);
        else
        $avatarPic = '';
        
		echo '
		<li'.$markupListItemClass.'>'.
        $avatarPic.
		($behaviorCountBeforeName?($countMarkup.' '.$authorNameMarkup):($authorNameMarkup.' '.$countMarkup))
		.'</li>'.CR;
	}
    
    if ($aShowAsOrderedList) {
    echo '                        </ol>'.CR;
    } else
    echo '                        </ul>'.CR;
}

//writes the unordered list tags and passes options to the getAuthorInfo_knrAuthorList function
function writeMarkup_knrAuthorList($options) {
	if ($options == null || count($options) == 0) {
		$options = getCurrentOptions_knrAuthorList(); //supporting legacy code
	}

    if (isset($options['showAsDropdown']) && $options['showAsDropdown'])
        getAuthorInfo_ddl_knrAuthorList(
            $options['includeAuthorsWoPosts'], 
            $options['includeEditors'], 
            $options['includeAdmin'], 
            isset($options['authorLimit'])?$options['authorLimit']:-1, 
            $options['includeContributors'], 
            $options['sort'], 
            $options['sortReverse'],
            $options['unorderedListClass'],
            $options['listItemClass'],
            $options['spanCountClass'],
            $options['spanAuthorClass'],
            $options['authorLinkClass'],
            $options['showCount'],
            $options['countBeforeName'],
            $options['moreAuthorsLink'],
            $options['moreAuthorsText'],
            $options['showAvatarMode'],
            $options['dropdownUnselectedText'],
            $options['showAsOrderedList']
            );
    else    
    	getAuthorInfo_knrAuthorList(
		    $options['includeAuthorsWoPosts'], 
		    $options['includeEditors'], 
		    $options['includeAdmin'], 
		    isset($options['authorLimit'])?$options['authorLimit']:-1, 
		    $options['includeContributors'], 
		    $options['sort'], 
		    $options['sortReverse'],
		    $options['unorderedListClass'],
		    $options['listItemClass'],
		    $options['spanCountClass'],
		    $options['spanAuthorClass'],
		    $options['authorLinkClass'],
		    $options['showCount'],
		    $options['countBeforeName'],
		    $options['moreAuthorsLink'],
		    $options['moreAuthorsText'],
            $options['showAvatarMode'],
            $options['dropdownUnselectedText'],
            $options['showAsOrderedList']        
		    );
}

//sets the default options
function getDefaultOptionArray_knrAuthorList() {
	$defaultTitle = 'Authors';
	$defaultIncludeAuthorsWoPosts = true;
	$defaultIncludeContributors = true;
	$defaultIncludeEditors = true;
	$defaultIncludeAdmin = false;
	$defaultAuthorLimit = -1;
	$defaultSort = 'none';
	$defaultSortReverse = false;
	$defaultUnorderedListClass = ''; //CSS class for UL tag below heading tag
	$defaultListItemClass = ''; //CSS class for LI tag for each author in the list
	$defaultSpanCountClass = ''; //CSS class for SPAN tag for the post-count in the list items
	$defaultSpanAuthorClass = ''; //CSS class for SPAN tag for each author in the list items
	$defaultAuthorLinkClass = ''; //CSS class for A HREF tag for each author in the list
	$defaultShowCount = true;
	$defaultCountBeforeName = false;
	$defaultMoreAuthorsLink = '';
    $defaultShowAvatarMode = 0;
    $defaultShowAsDropdown = false;
    $defaultDropdownUnselectedText = '';
    $defaultShowAsOrderedList = false;
    $defaultNoShowUrlFilter = '';

	return array('title'=>$defaultTitle,
			'includeAuthorsWoPosts'=>$defaultIncludeAuthorsWoPosts,
			'includeContributors'=>$defaultIncludeContributors,
			'includeAdmin'=>$defaultIncludeAdmin,
			'includeEditors'=>$defaultIncludeEditors,
			'authorLimit'=>$defaultAuthorLimit,
			'sort'=>$defaultSort,
			'sortReverse'=>$defaultSortReverse,
			'unorderedListClass'=>$defaultUnorderedListClass,
			'listItemClass'=>$defaultListItemClass,
			'spanCountClass'=>$defaultSpanCountClass,
			'spanAuthorClass'=>$defaultSpanAuthorClass,
			'authorLinkClass'=>$defaultAuthorLinkClass,
			'showCount'=>$defaultShowCount,
			'countBeforeName'=>$defaultCountBeforeName,
			'moreAuthorsLink'=>$defaultMoreAuthorsLink,
			'moreAuthorsText'=>$defaultMoreAuthorsText,
            'showAvatarMode'=>$defaultShowAvatarMode,
            'showAsDropdown'=>$defaultShowAsDropdown,
            'dropdownUnselectedText'=>$defaultDropdownUnselectedText,
            'showAsOrderedList'=>$defaultShowAsOrderedList,
			'noShowUrlFilter'=>$defaultNoShowUrlFilter
		);
}

//reads the plugin options
function getCurrentOptions_knrAuthorList() {
	if ($loadedOptions == null) { //prevent reading options more than once in the same request
		$options = get_option('widget_knrAuthorList');
		if ( !is_array($options) ) {
			$options = getDefaultOptionArray_knrAuthorList();
		}
		$loadedOptions = $options;
	}
	return $loadedOptions; //return $options;
}

/*
function checkForSkip($pattern, $currUrl) {
	if (!$pattern) return false;	
	return ereg($pattern, $currUrl);
}
*/

/*
//outputs the 'skeleton' code
function widget_knrAuthorList($args) {
	$options = getCurrentOptions_knrAuthorList();	
	$retValSkip = checkForSkip($options['noShowUrlFilter'], $_SERVER['REQUEST_URI']);
	if ($retValSkip === 1) {
		return;	
	}
	extract($args);
	echo $before_widget;
	echo $before_title;

	echo $options['title'];

	echo $after_title;
	writeMarkup_knrAuthorList($options);
	echo $after_widget;	
}
*/

//widget settings in Admin
function control_knrAuthorList() {
	$options = getCurrentOptions_knrAuthorList();
	
	if ($_POST["knrAuthorList-submit"]) {
		$options['title'] = strip_tags(stripslashes($_POST['knrAuthorList-title']));
		$options['includeAuthorsWoPosts'] = isset($_POST['knrAuthorList-incAuthorsWoPosts']);
		$options['includeContributors'] = isset($_POST['knrAuthorList-incContributors']);
		$options['includeAdmin'] = isset($_POST['knrAuthorList-incAdmin']);
		$options['includeEditors'] = isset($_POST['knrAuthorList-incEditors']);
		$options['authorLimit'] = strip_tags(stripslashes($_POST['knrAuthorList-authorLimit']));
		$options['sort'] = strip_tags(stripslashes($_POST['knrAuthorList-sort']));
		$options['sortReverse'] = isset($_POST['knrAuthorList-sortReverse']);
		$options['unorderedListClass'] = strip_tags(stripslashes($_POST['knrAuthorList-unorderedListClass']));
		$options['listItemClass'] = strip_tags(stripslashes($_POST['knrAuthorList-listItemClass']));
		$options['spanCountClass'] = strip_tags(stripslashes($_POST['knrAuthorList-spanCountClass']));
		$options['spanAuthorClass'] = strip_tags(stripslashes($_POST['knrAuthorList-spanAuthorClass']));
		$options['authorLinkClass'] = strip_tags(stripslashes($_POST['knrAuthorList-authorLinkClass']));
		$options['showCount'] = isset($_POST['knrAuthorList-showCount']);
		$options['countBeforeName'] = isset($_POST['knrAuthorList-countBeforeName']);
		$options['moreAuthorsLink'] = strip_tags(stripslashes($_POST['knrAuthorList-moreAuthorsLink']));
		$options['moreAuthorsText'] = strip_tags(stripslashes($_POST['knrAuthorList-moreAuthorsText']));
        $options['showAvatarMode'] = strip_tags(stripslashes($_POST['knrAuthorList-showAvatarMode']));
        $options['showAsDropdown'] = isset($_POST['knrAuthorList-showAsDropdown']);
        $options['dropdownUnselectedText'] = strip_tags(stripslashes($_POST['knrAuthorList-dropdownUnselectedText']));
        $options['showAsOrderedList'] = isset($_POST['knrAuthorList-showAsOrderedList']);
        $options['noShowUrlFilter'] = $_POST['knrAuthorList-noShowUrlFilter'];        
		
		update_option('widget_knrAuthorList', $options);
	}
	
	$title = htmlspecialchars($options['title'], ENT_QUOTES);
	$includeAuthorsWoPosts = $options['includeAuthorsWoPosts'];
	$includeContributors = $options['includeContributors'];
	$includeAdmin = $options['includeAdmin'];
	$includeEditors = $options['includeEditors'];
	$authorLimit = $options['authorLimit'];
	$sortOrder = $options['sort'];
	$sortOrderReverse = $options['sortReverse'];
	$unorderedListClass = $options['unorderedListClass'];
	$listItemClass = $options['listItemClass'];
	$spanCountClass = $options['spanCountClass'];
	$spanAuthorClass = $options['spanAuthorClass'];
	$authorLinkClass = $options['authorLinkClass'];
	$showCount = $options['showCount'];
	$countBeforeName = $options['countBeforeName'];
	$moreAuthorsLink = $options['moreAuthorsLink'];
	$moreAuthorsText = $options['moreAuthorsText'];
    $showAvatarMode = $options['showAvatarMode'];
    $showAsDropdown = $options['showAsDropdown'];
    $dropdownUnselectedText = $options['dropdownUnselectedText'];
    $showAsOrderedList = $options['showAsOrderedList'];
    $noShowUrlFilter = $options['noShowUrlFilter'];

	echo '<p style="font-weight: bold;">General Options</p>';

	
	echo '<p>
		<label for="knrAuthorList-title">' . __('Title:') . '</label>
		<input style="width: 200px;" id="knrAuthorList-title" name="knrAuthorList-title" type="text" value="'.$title.'" />
		</p>';
	echo '<p>
		<label for="knrAuthorList-incAuthorsWoPosts">' . __('Include Authors With 0 Posts:') . '</label>
		<input id="knrAuthorList-incAuthorsWoPosts" name="knrAuthorList-incAuthorsWoPosts" type="checkbox"'.($includeAuthorsWoPosts?' checked="checked"':'').' />
		</p>';
	echo '<p>
		<label for="knrAuthorList-incContributors">' . __('Include Contributors:') . '</label>
		<input id="knrAuthorList-incContributors" name="knrAuthorList-incContributors" type="checkbox"'.($includeContributors?' checked="checked"':'').' />
		</p>';
	echo '<p>
		<label for="knrAuthorList-incAdmin">' . __('Include Administrators:') . '</label>
		<input id="knrAuthorList-incAdmin" name="knrAuthorList-incAdmin" type="checkbox"'.($includeAdmin?' checked="checked"':'').' />
		</p>';
	echo '<p>
		<label for="knrAuthorList-incEditors">' . __('Include Editors:') . '</label>
		<input id="knrAuthorList-incEditors" name="knrAuthorList-incEditors" type="checkbox"'.($includeEditors?' checked="checked"':'').' />
		</p>';
	echo '<p>
		<label for="knrAuthorList-authorLimit">' . __('Author Limit:') . '</label>
		<input style="width: 200px;" id="knrAuthorList-authorLimit" name="knrAuthorList-authorLimit" type="text" value="'.$authorLimit.'" />
		<br />
		<small>Enter -1 or 0 for no limit</small>
		</p>';
	echo '<p>
		<label for="knrAuthorList-moreAuthorsLink">' . __('More Authors Link:') . '</label>
		<input style="width: 200px;" id="knrAuthorList-moreAuthorsLink" name="knrAuthorList-moreAuthorsLink" type="text" value="'.$moreAuthorsLink.'" />
		<br />
		<small>Leave blank for no "more" link</small>
		</p>';
	echo '<p>
		<label for="knrAuthorList-moreAuthorsText">' . __('More Authors Text:') . '</label>
		<input style="width: 200px;" id="knrAuthorList-moreAuthorsText" name="knrAuthorList-moreAuthorsText" type="text" value="'.$moreAuthorsText.'" />
		</p>';
    echo '<p>
        <label for="knrAuthorList-showAsOrderedList">' . __('Show As Ordered List:') . '</label>
        <input id="knrAuthorList-showAsOrderedList" name="knrAuthorList-showAsOrderedList" type="checkbox"'.($showAsOrderedList?' checked="checked"':'').' />
        </p>';
	echo '<p>
		<label for="knrAuthorList-sort">' . __('Sort with:') . '</label>
		<select name="knrAuthorList-sort" id="knrAuthorList-sort">
		<option value="fname"'.($sortOrder == 'fname'?' selected="selected"':'').'>First Name</option>
		<option value="lname"'.($sortOrder == 'lname'?' selected="selected"':'').'>Last Name</option>
		<option value="flname"'.($sortOrder == 'flname'?' selected="selected"':'').'>First & Last Name</option>
		<option value="lfname"'.($sortOrder == 'lfname'?' selected="selected"':'').'>Last & First Name</option>
		<option value="display_name"'.($sortOrder == 'display_name'?' selected="selected"':'').'>Display Name</option>
		<option value="post_count"'.($sortOrder == 'post_count'?' selected="selected"':'').'>No. of Posts</option>
		<option value="ID"'.($sortOrder == 'ID'?' selected="selected"':'').'>Author Registration Date</option>
		<option value="custom"'.($sortOrder == 'custom'?' selected="selected"':'').'>Custom</option>
		<option value="none"'.($sortOrder == 'none'?' selected="selected"':'').'>No Sorting</option>
		</select>
		<br />
		<small>With "Custom" sort order, you have to go to the "Author Order" setting page to manually set the sort order</small>
		</p>';
	echo '<p>
		<label for="knrAuthorList-sortReverse">' . __('Sort Reverse:') . '</label>
		<input id="knrAuthorList-sortReverse" name="knrAuthorList-sortReverse" type="checkbox"'.($sortOrderReverse?' checked="checked" ':'').' />
		<br />
		<small>Reverses the sort order set by "Sort with"</small>
		</p>';
    echo '<p>
        <label for="knrAuthorList-showAvatarMode">' . __('Show Avatar:') . '</label>
        <select name="knrAuthorList-showAvatarMode" id="knrAuthorList-showAvatarMode">
        <option value="0"'.($showAvatarMode == 0?' selected="selected"':'').'>None</option>
        <option value="1"'.($showAvatarMode == 1?' selected="selected"':'').'>Gravatars</option>
        </select>
        </p>';
    echo '<p>
        <label for="knrAuthorList-showAsDropdown">' . __('Show As Dropdown:') . '</label>
        <input id="knrAuthorList-showAsDropdown" name="knrAuthorList-showAsDropdown" type="checkbox"'.($showAsDropdown?' checked="checked"':'').' />
        </p>';
    echo '<p>
        <label for="knrAuthorList-dropdownUnselectedText">' . __('Dropdown Unselected Text:') . '</label>
        <input style="width: 200px;" id="knrAuthorList-dropdownUnselectedText" name="knrAuthorList-dropdownUnselectedText" type="text" value="'.$dropdownUnselectedText.'" />
        </p>';
	echo '<p>
		<label for="knrAuthorList-noShowUrlFilter">' . __('Do not show for URLs matching:') . '</label>
		<input style="width: 200px;" id="knrAuthorList-noShowUrlFilter" name="knrAuthorList-noShowUrlFilter" type="text" value="'.$noShowUrlFilter.'" />
        </p>';

	echo '<p style="font-weight: bold;">Markup Options</p>';


	echo '<p>
		<label for="knrAuthorList-unorderedListClass">' . __('List Class:') . '</label>
		<input style="width: 200px;" id="knrAuthorList-unorderedListClass" name="knrAuthorList-unorderedListClass" type="text" value="'.$unorderedListClass.'" />
		</p>';
	echo '<p>
		<label for="knrAuthorList-listItemClass">' . __('List Item Class:') . '</label>
		<input style="width: 200px;" id="knrAuthorList-listItemClass" name="knrAuthorList-listItemClass" type="text" value="'.$listItemClass.'" />
		</p>';
	echo '<p>
		<label for="knrAuthorList-spanCountClass">' . __('Span Count Class:') . '</label>
		<input style="width: 200px;" id="knrAuthorList-spanCountClass" name="knrAuthorList-spanCountClass" type="text" value="'.$spanCountClass.'" />
		</p>';
	echo '<p>
		<label for="knrAuthorList-spanAuthorClass">' . __('Span Author Class:') . '</label>
		<input style="width: 200px;" id="knrAuthorList-spanAuthorClass" name="knrAuthorList-spanAuthorClass" type="text" value="'.$spanAuthorClass.'" />
		</p>';
	echo '<p>
		<label for="knrAuthorList-authorLinkClass">' . __('Author Link Class:') . '</label>
		<input style="width: 200px;" id="knrAuthorList-authorLinkClass" name="knrAuthorList-authorLinkClass" type="text" value="'.$authorLinkClass.'" />
		</p>';
	echo '<p>
		<label for="knrAuthorList-showCount">' . __('Show Count:') . '</label>
		<input id="knrAuthorList-showCount" name="knrAuthorList-showCount" type="checkbox"'.($showCount?' checked="checked"':'').' />
		</p>';
	echo '<p>
		<label for="knrAuthorList-countBeforeName">' . __('Display Count Before Name:') . '</label>
		<input id="knrAuthorList-countBeforeName" name="knrAuthorList-countBeforeName" type="checkbox"'.($countBeforeName?' checked="checked"':'').' />
		</p>';
	
	
	echo '<input type="hidden" id="knrAuthorList-submit" name="knrAuthorList-submit" value="1" />';
}

/*
//initializes the widget (ensure author ordering column is present)
function knrAuthorList_init() {
	global $wpdb;
	$wpdb->show_errors();

	$query1 = $wpdb->query("SHOW COLUMNS FROM $wpdb->users LIKE 'knr_author_order'");
	
	if ($query1 == 0) {
		$wpdb->query("ALTER TABLE $wpdb->users ADD `knr_author_order` INT( 4 ) NULL DEFAULT '0'");
	}
		
	register_sidebar_widget(__('KNR Author List'), 'widget_knrAuthorList');    
	register_widget_control(__('KNR Author List'), 'control_knrAuthorList', 250, 400);
}
*/

//Adds the custom author order page to the settings menu of the Admin
  function knrAuthorWidget_menu() {
	add_options_page('knrCustomAuthorSort', 'KNR Author List', 'manage_options', __FILE__, 'knrAuthorWidget_menufunc');
  }
  
  //Admin menu for custom ordering of authors
  function knrAuthorWidget_menufunc() {
/*
* I would like to mention that Wil Linssen's tutorial was really helpful in creating 
* the drag-drop interface with jQuery UI Sortable for manually ordering the authors
*/
	global $wpdb;
	
$dirloc=dirname(__FILE__);
$dirloc=str_ireplace('\\', '/', $dirloc);
$dirloc='..'.substr($dirloc,stripos($dirloc,'/wp-content')).'/';

	echo <<< EOS
<script type="text/javascript">
// When the document is ready set up our sortable with it's inherant function(s)
jQuery(document).ready(
	function() {
		jQuery("#author-list").sortable(
			{
				handle : '.handle',
				update : function () {
					var order = jQuery('#author-list').sortable('serialize');
					jQuery("div#statusInfo").load("{$dirloc}knrAuthorListCustomSortSave.php?"+order );
				}
			}
		);
	}
);
</script>
EOS;
	
	$sqlAuthorListQuery = "
SELECT
usr.ID,
fnametab.meta_value AS fname,
lnametab.meta_value as lname
FROM {$wpdb->users} usr
JOIN {$wpdb->usermeta} capa ON usr.ID = capa.user_id AND capa.meta_key LIKE '%capabilities%'
LEFT OUTER JOIN {$wpdb->usermeta} fnametab ON usr.ID = fnametab.user_id AND fnametab.meta_key = 'first_name'
LEFT OUTER JOIN {$wpdb->usermeta} lnametab ON usr.ID = lnametab.user_id AND lnametab.meta_key = 'last_name'
WHERE capa.meta_value NOT LIKE '%subscribe%'
ORDER BY usr.knr_author_order, ID
	";	
	$authorRows = $wpdb->get_results($sqlAuthorListQuery);
    echo CR.'<p>If you require any assistance with this plugin, feel free to contact me on k.nitin.r'.'@gmail.com or k_nitin_r'.'@yahoo.co.in
    or leave me a comment on my blog at http://www.nitinkatkam.com .</p>'.CR;
	echo CR.'<h1>Custom Author Ordering</h1>'.CR;
	echo '<small>This page is for only for ordering the authors manually. For other settings (and to set the sort order to "custom"), go to the Widgets page under Appearance, add the KNR Author List widget to your sidebar and click "Edit".</small>'.CR;
	echo '<p id="authorListInstructions">Please order the authors below by dragging &amp; dropping them at the desired position.</p>'.CR;
	
	echo '<ul id="author-list">'.CR;
    
    $blogUrl = get_bloginfo('url');
foreach((array) $authorRows as $row) {
		$authorUrl = $blogUrl . '/?author=' . $row->ID;
		
		//We shouldn't need this since the user must have entered a first name and last name, but just in case
		if ($row->lname == null && $row->fname == null) {
			$tempUser = get_userdata($row->ID);
			if ($tempUser->nickname != null)
				$row->fname = $tempUser->nickname;
			else
				$row->fname = $tempUser->user_login;
		}		
		
		echo '<li id="listItem_'.$row->ID.'"><span id="et" class="handle" style="background-color: navy; color: white; width: 150px; display: block; padding: 0px 0px 0px 2px;">'.$row->fname.' '.$row->lname.'</span></li>'.CR;
	}	
	echo '</ul>'.CR;
	
	echo <<<STATUSDIV
	<div id="statusInfo"></div>
STATUSDIV;
  }
  
  function knrAuthorWidget_menu_js() {
	  //TODO: Change this comparison to 'contains'
//if ( $_GET['page'] == "knr-author-list-widget/knrAuthorList.php" )
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-sortable');	  
  }
  
  
//add_action("plugins_loaded", "knrAuthorList_init");
  add_action('admin_menu', 'knrAuthorWidget_menu');
  add_action('admin_menu', 'knrAuthorWidget_menu_js');

?>
