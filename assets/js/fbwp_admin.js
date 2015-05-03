// alert("wc-hooks_for_woo.js is loaded!!!")
// var $ = jQuery;
// window.jQuery = window.$ = jQuery; // from ...\kaikei_MYsql\script\common\common.js, but don't work


// var $ = jQuery;
var $ =jQuery.noConflict();

function show_page_info(){
	//show page_id 
	var $objCurrentRow = false; 
	$objCurrentRow = $('#fb2wp_general\\[page_id\\]').parents('tr');  
	$objCurrentRow.fadeIn("fast",function(){
		// do something... .addClass('hidden'); ... .removeClass('hidden'); 
	}); 
	
	//hide group_id  
	$objCurrentRow = $('#fb2wp_general\\[group_id\\]').parents('tr');  
	$objCurrentRow.fadeOut("fast",function(){
		// do something... .addClass('hidden'); ... .removeClass('hidden');  ... // $(this)...
	});
}


function show_group_info(){
//show group_id
	$objCurrentRow = $('#fb2wp_general\\[group_id\\]').parents('tr');  
	$objCurrentRow.fadeIn("fast",function(){
		// do something... .addClass('hidden'); ... .removeClass('hidden');  ... // $(this)...
	}); 
	
	//hide page_id
	var $objCurrentRow = false; 
	$objCurrentRow = $('#fb2wp_general\\[page_id\\]').parents('tr');  
	$objCurrentRow.fadeOut("fast",function(){
		// do something... .addClass('hidden'); ... .removeClass('hidden'); 
	}); 
}

/*
 * hide_all_connection_info
 * 
 * @author: EGANY 20150421 
 * @param: string strTabID (Ex.: fb2wp_general )
 * @return: none
 */ 
function hide_all_connection_info(strTabID){

	//hide Access Token 
	$objCurrentRow = $(strTabID+ '\\[access_token\\]').parents('tr');  
	$objCurrentRow.fadeOut("fast",function(){
		// do something... .addClass('hidden'); ... .removeClass('hidden');  ... // $(this)...
	}); 
		
	//hide App. Info. 
	// okok .addClass('hidden'); ... .removeClass('hidden');    
	var $objCurrentRow = false;  
	$objCurrentRow = $(strTabID+ '\\[app_id\\]').parents('tr');   
	$objCurrentRow.fadeOut("fast",function(){  
		// do something... .addClass('hidden'); ... .removeClass('hidden'); 
	}); 
	$objCurrentRow = $(strTabID+ '\\[app_secret\\]').parents('tr');   
	$objCurrentRow.fadeOut("fast",function(){  
		// do something... .addClass('hidden'); ... .removeClass('hidden'); 
	}); 
}


/*
 * show_access_token
 * 
 * @author: EGANY updated 20150421 
 * @param: string strTabID (Ex.: fb2wp_general )
 * @return: none
 */ 
function show_access_token(strTabID){

		//show Access Token 
		$objCurrentRow = $( strTabID+ '\\[access_token\\]').parents('tr');  
		$objCurrentRow.fadeIn("fast",function(){
			// do something... .addClass('hidden'); ... .removeClass('hidden');  ... // $(this)...
		}); 
		
		//hide App. Info. 
		// okok .addClass('hidden'); ... .removeClass('hidden'); 
		var $objCurrentRow = false; 
		$objCurrentRow = $( strTabID+ '\\[app_id\\]').parents('tr');  
		$objCurrentRow.fadeOut("fast",function(){
			// do something... .addClass('hidden'); ... .removeClass('hidden'); 
		}); 
		$objCurrentRow = $( strTabID+ '\\[app_secret\\]').parents('tr');  
		$objCurrentRow.fadeOut("fast",function(){
			// do something... .addClass('hidden'); ... .removeClass('hidden'); 
		}); 
}



/*
 * show_app_info
 * 
 * @author: EGANY updated 20150421 
 * @param: string strTabID (Ex.: #fb2wp_general )
 * @return: none
 */ 
function show_app_info(strTabID){
	
	// okok .addClass('hidden'); ... .removeClass('hidden'); 
	var $objCurrentRow = false; 
	$objCurrentRow = $(strTabID + '\\[app_id\\]').parents('tr');  
	$objCurrentRow.fadeIn("fast",function(){
		// do something... .addClass('hidden'); ... .removeClass('hidden'); 
	}); 
	$objCurrentRow = $(strTabID + '\\[app_secret\\]').parents('tr');  
	$objCurrentRow.fadeIn("fast",function(){
		// do something... .addClass('hidden'); ... .removeClass('hidden'); 
	});
	//hide access token 
	$objCurrentRow = $(strTabID + '\\[access_token\\]').parents('tr');  
	$objCurrentRow.fadeOut("fast",function(){
		// do something... .addClass('hidden'); ... .removeClass('hidden');  ... // $(this)...
	});
}


$( document ).ready(function(){ 
	// tab General:  default showing 
	var $str_access_type_general = '#fb2wp_general\\[access_type\\]'; 
	if($($str_access_type_general + ' :selected').val() == 'fb_app')
	{
		show_app_info('#fb2wp_general'); 
	}
	else if($($str_access_type_general + ' :selected').val() == 'fb_access_token')  
		show_access_token('#fb2wp_general');  
	else
		hide_all_connection_info('#fb2wp_general');  
	
	if($($str_access_type_general + ' :selected').val() == 'st_page')
	{
		show_page_info();  
	}	
	else
		show_group_info();  
	
	// tab General: change Access Type  
	$($str_access_type_general).change(function(){
		// $str_access_type_general + ' :selected'
		if($( $str_access_type_general + ' :selected').val() == 'fb_app')
		{
			//show App. info. 
			show_app_info('#fb2wp_general'); 
		}
		else if($($str_access_type_general + ' :selected').val() == 'fb_access_token')
			show_access_token('#fb2wp_general'); 
		else
			hide_all_connection_info('#fb2wp_general'); 
		
		// fb2wp_general[app_id]  &  fb2wp_general[app_secret]
		// fb2wp_general[access_token] 
		
	}); 
	
	// $('#fb2wp_general\\[source_type\\]').change(function(){
		// if($('#fb2wp_general\\[source_type\\] :selected').val() == 'st_page') 
		// {
			// show_page_info(); 
			
		// }
		// else 
		// {
			// show_group_info(); 
		// } 
		
		// // fb2wp_general[app_id]  &  fb2wp_general[app_secret]
		// // fb2wp_general[access_token] 
		
	// }); 
	
	
	// tab Page: default showing  
	var $str_access_type_page = '#fb2wp_page\\[access_type\\]';  
	if($($str_access_type_page + ' :selected').val() == 'fb_app')  
	{
		show_app_info('#fb2wp_page'); 
	}
	else if($($str_access_type_page + ' :selected').val() == 'fb_access_token')  
		show_access_token('#fb2wp_page'); 
	else
		hide_all_connection_info('#fb2wp_page'); 
	
	// tab Page: change Access Type  
	$($str_access_type_page).change(function(){
		if($($str_access_type_page + ' :selected').val() == 'fb_app')
		{
			//show App. info. 
			show_app_info('#fb2wp_page'); 
		}
		else if($($str_access_type_page + ' :selected').val() == 'fb_access_token')
			show_access_token('#fb2wp_page'); 
		else
			hide_all_connection_info('#fb2wp_page'); 
		
		// fb2wp_page[app_id]  &  fb2wp_page[app_secret]
		// fb2wp_page[access_token] 
		
	}); 
	
	
	// tab Group: default showing  
	var $strTabID = '#fb2wp_group'; 
	var $str_access_type_id = $strTabID + '\\[access_type\\]';  
	
	if($($str_access_type_id + ' :selected').val() == 'fb_app')  
	{
		show_app_info($strTabID); 
	}
	else if($($str_access_type_id + ' :selected').val() == 'fb_access_token')  
		show_access_token($strTabID); 
	else
		hide_all_connection_info($strTabID); 
	
	// tab Group: change Access Type  
	$($str_access_type_id).change(function(){
		if($($str_access_type_id + ' :selected').val() == 'fb_app')
		{
			//show App. info. 
			show_app_info($strTabID); 
		}
		else if($($str_access_type_id + ' :selected').val() == 'fb_access_token')
			show_access_token($strTabID); 
		else
			hide_all_connection_info($strTabID); 
		
		// fb2wp_group[app_id]  &  fb2wp_group[app_secret]
		// fb2wp_group[access_token] 
		
	}); 
});





