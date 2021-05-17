<?php

// this contains the search bar and nav functions

/*
    This function prints the non-header search bar - used on several pages.
*/
function searchBar(){
    global $submittedSearch;
    echo "<div class='row'>
            <div class='col-md-4 col-sm-4 col-sm-offset-4 col-md-offset-4 col-xs-4 col-xs-offset-4 searchcol text-centre'>
                
                
                <form class='form-inline' method='get'>
                    <div class='text-centre form-group-lg form-group'  id='mainsearch'>
                        
                        <div class='has-feedback has-search'>
                            <div class='input-group input-group-lg'>
                                <input type='text' class='form-control' name='search'";
                                if (isset($submittedSearch)){
                                    echo " value='{$submittedSearch}'";
                                
                                } 
                                echo " placeholder='Search'>
                                <span class='input-group-btn'>
                                    <button type='submit' value='search-submit' name='search-submit' id='search-submit' class='btn btn-default'><span class='glyphicon glyphicon-search search-icon'></span></button>
                                </span>
                            </div>
                        </div>
                    </div>
                </form>
                
            </div>
        </div>";
}

/*
    This function prints the nav bar needed for the JS-powered sort/page functionality
*/
function searchNav(){

    echo "<div class='row text-center form-group' id='searchnav'>
    <form>
    <label for='page-number'>page</label>
    <input type='number' id='page-number' name='page-number' step='1' value='1' min='1' size='4'>
    <label for='sort'>sort by</label>
    <select name='sort' id='sort' class='form-group'>
        <option value='1'>most relevant</option>

        <option value='2'>rating: high > low</option>
        <option value='3'>rating; low > high</option>
        <option value='4'>most reviewed</option>
        <option value='5'>a-z</option>
        <option value='6'>z-a</option>
    </select>
    <input type='button' class='btn btn-sm btn-default' value='go' id='go'></input>
    <label class='radio-inline'>
        <input type='radio' name='viewradio' id='tiled' value='tiled' checked>tiles
    </label>
    <label class='radio-inline'>
        <input type='radio' name='viewradio' id='list' value='list'>list
    </label>
    </form>                  
</div>";
}

?>