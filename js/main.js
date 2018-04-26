//Genre Ajax Filtering
jQuery(function($)
{
    //Load posts on document ready
    get_posts();
 
 
    //If input is changed, load posts
    $('#location-filter input').live('change', function(){
        get_posts(); //Load Posts
    });
	
	   $('#branch-filter input').live('change', function(){
        get_posts(); //Load Posts
    });
 
    //Find Selected Location
    function getSelectedLocation()
    {
        var loc = []; //Setup empty array
 
        $("#location-filter label input:checked").each(function() {
            var val = $(this).val();
            loc.push(val); //Push value onto array
        });     
 
        return loc; //Return all of the selected location in an array
    }
	
	 //Find Selected Branches
    function getSelectedBranches()
    {
        var branch = []; //Setup empty array
 
        $("#branch-filter label input:checked").each(function() {
            var val = $(this).val();
            branch.push(val); //Push value onto array
        });     
 
        return branch; //Return all of the selected branch in an array
    }
 
    //Fire ajax request when typing in search
    $('#location-filter input.text-search').live('keyup', function(e){
        if( e.keyCode == 27 )
        {
            $(this).val(''); //If 'escape' was pressed, clear value
        }
 
        get_posts(); //Load Posts
    });
	
	    //Fire ajax request when typing in search
    $('#branch-filter input.text-search').live('keyup', function(e){
        if( e.keyCode == 27 )
        {
            $(this).val(''); //If 'escape' was pressed, clear value
        }
 
        get_posts(); //Load Posts
    });
	
	$('#submit-search').live('click', function(e){
		e.preventDefault();
		genre_get_posts(); //Load Posts
	});
 
 
    //If pagination is clicked, load correct posts
    $('.filter-navigation a').live('click', function(e){
        e.preventDefault();
 
        var url = $(this).attr('href'); //Grab the URL destination as a string
        var paged = url.split('&paged='); //Split the string at the occurance of &paged=
 
        get_posts(paged[1]); //Load Posts (feed in paged value)
    });
 
    //Main ajax function
    function get_posts(paged)
    {
        var paged_value = paged; //Store the paged value if it's being sent through when the function is called
        var ajax_url = ajax_offices_params.ajax_url; //Get ajax url (added through wp_localize_script)
 
        $.ajax({
            type: 'GET',
            url: ajax_url,
            data: {
                action: 'offices_filter',
                loc: getSelectedLocation, //Get array of values from previous function
				branch: getSelectedBranches, //Get array of values from previous function
                paged: paged_value //If paged value is being sent through with function call, store here
            },
            beforeSend: function ()
            {
                //You could show a loader here
            },
            success: function(data)
            {
                //Hide loader here
                $('#filter-results').html(data);
            },
            error: function()
            {
                                //If an ajax error has occured, do something here...
                $("#filter-results").html('<p>There has been an error</p>');
            }
        });
    }
 
});