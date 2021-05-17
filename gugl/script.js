$(document).ready(function() {
    // login/signup switcher

    /*
    This triggers if the login-signup element is present
    */
    if ($('.login-signup')) {


        $('#log').click(function() {

            // when the login button is clicked

            // it is disabled
            $(this).attr('disabled', 'disabled');

            // the disabled attribute is removed from the sign-up button
            $('#sign').removeAttr('disabled');

            // the login element is hidden
            $('#log-in').removeClass('hide');

            // the sign up element is shown
            $('#sign-up').addClass('hide');
        });



        $('#sign').click(function() {

            // when the signin button is clicked

            // it is disabled
            $(this).attr('disabled', 'disabled');

            // the disabled attribute is removed from the login button
            $('#log').removeAttr('disabled');

            // the sign-up element is hidden
            $('#sign-up').removeClass('hide');

            // the login element is shown
            $('#log-in').addClass('hide');
        });

        $('#log-in').on('click', '#createacc', function() {

            // this does the same as the "sign up" button

            $('#sign').attr('disabled', 'disabled');
            $('#log').removeAttr('disabled');
            $('#sign-up').removeClass('hide');
            $('#log-in').addClass('hide');
        });
    }


    //populate search pages using API
    try {
        /*
        This only triggers if the element with the applist ID and 
        the appList var are present on the page only included on pages that need search)
        sent to the JS from PHP on the page (have a look there :) )
        */
        if ($('#applist') && appList) {
            // reference to the element id 
            var searchTag = '#applist';

            // the length of each page. I planned to include
            // a feature where a user could vary the amount of results
            // they wanted per page. maybe in future
            var pageLength = 24;

            // the initial page number
            var pageNumber = 1;

            /*
            Assign the max page number to the page number select element
            using a page number function I created
            */
            $('#page-number').attr("max", maxPages(appList, pageLength));

            /*
            Using another self-created function, this populates the result object
            using the page length, page number and the appList from before
            */
            var page = pageList(pageLength, pageNumber, appList);

            /*
            The first load of the website page populates the applist
            element with the first result page using the populateTile method
            I created
            */
            populateTile(page, admin);

            /*
            This triggers when the "go" button is clicked.
            */
            $('#go').click(function() {
                // create a copy of the original list (for sorting)
                var newList = JSON.parse(JSON.stringify(appList));

                // take the value of the sort selector element
                var arrangement = $('#sort').val();

                // switch on it
                switch (arrangement) {
                    case '1':
                        /*
                        Do nothing. This is the default setting.
                        It doesn't exactly return the "most relevant", instead it returns
                        in the order that the results are found in the database. I don't know
                        how to fix this
                        */
                        break;
                    case '2':
                        /*
                        This sorts the newList by its rating, lowest to highest, using a 
                        "sortList()" comparator I created. Then it simply reverses the list
                        */
                        newList.sort(sortList("rating")).reverse();
                        break;
                    case '3':
                        /*
                        As above, except no reversal afterwards
                        */
                        newList.sort(sortList("rating"));
                        break;
                    case '4':
                        /*
                        This sorts the newList by the amount of reviews it has
                        */
                        newList.sort(sortListLength("reviews")).reverse();
                        newList.forEach(function(app) {
                            console.log(app.name + " " + app.reviews.length);
                        });
                    case '5':
                        /*
                        This sorts the newList by the name alphabetically
                        */
                        newList.sort(sortList("name"));
                        break;
                    case '6':
                        /*
                        As above except it reverses it (z-a)
                        */
                        newList.sort(sortList("name")).reverse();
                        break;
                    default:
                        break;
                }

                // This gets the page number in the input box
                pageNumber = $('#page-number').val();

                // This populates the results object to then be 
                // displayed on the website page
                page = pageList(pageLength, pageNumber, newList);

                // This clears the entire list first
                $(searchTag).html("");

                /*
                Here the logic for the tile/list view is calculated from 
                the checkboxes. I appreciate that the comparison to "undefined"
                might be a little "unrefined" (lol) but it works
                */
                if ($('input[id=tiled]:checked').val() !== undefined) {

                    // run the tile populate method
                    populateTile(page, admin);
                } else if ($('input[id=list]:checked').val() !== undefined) {

                    // run the list populate method
                    populateList(page, admin);
                }

            })



            /*
            This function calculates the maximum amount of pages a 
            result object can display given the desired page length
            */
            function maxPages(list, pageLength) {
                var listLength = list.length;
                /*
                If the lengths of the list and pages divide
                evenly, then that is the maximum amount of pages.

                However, if there is a remainder then the maximum
                amount is the integer value of the lengths plus one.
                */
                return (listLength % pageLength === 0) ? listLength / pageLength : Math.floor((listLength / pageLength) + 1);
            }

            /*
            This function populates the result list to actually be shown to screen.
            It takes the desired page length, the desired page number (from 1 to max)
            and the result object to make the page from.
            */
            function pageList(length, page, list) {

                /* 
                This determines the end of the page by way of calculating 
                the last possible result index plus one, for use in the for loop later on
                */
                var ratio = length * page;

                /*
                This is the start of the for loop, determined by the ending of the page
                subtracting the page length
                */
                var start = ratio - length;

                // empty list to populate with elements
                var pageList = [];

                // iterate through the original list between the start and end points
                for (var loop = start; loop < ratio; loop++) {
                    // if the loop finds an undefined object
                    // it terminates
                    if (list[loop] === undefined) {
                        break;
                    }
                    // otherwise populate the new page object
                    pageList.push(list[loop]);
                }

                return pageList;
            }


            /*
            This is where the tiles for the result page get made.
            It takes the page to be shown and the admin var
            Lots of html printout
            */
            function populateTile(page, admin) {
                try {
                    page.forEach(function(app) {
                        appHTML = "<div class='col-md-2 col-xs-4'>";

                        // if admin is logged in, it redirects to the edit page
                        if (admin) {
                            appHTML += "<a href='../editApp.php?app_id=" + app.id + "' class='panel-link' target='_blank'>";
                        } else {
                            appHTML += "<a href='appPage.php?app_id=" + app.id + "' class='panel-link' target='_blank'>";
                        }

                        appHTML += "<div class='panel panel-primary genre-panel shadow'>\
                        <div class='panel-heading'>\
                            <h3 class='panel-title'>" + app.name + "</h3>\
                        </div>\
                        <div class='panel-body'>\
                            <img src='" + app.image + "'>\
                        </div>\
                        <div class='panel-footer rating'>"

                        // calculate the amount of stars to print for the rating

                        // get the amount of reviews
                        var reviews = Object.keys(app.reviews).length;

                        // initialise the rating sum
                        var ratingCount = 0;

                        // iterate through adding the review's rating
                        app.reviews.forEach(function(review) {
                            ratingCount += parseInt(review.rating);
                        })

                        // if there are indeed reviews
                        if (reviews !== 0) {

                            // round down the rating sum divided by the amount of reviews
                            var rating = Math.floor(ratingCount / reviews);

                            // initialise string
                            var ratingString = "";

                            // the star span to be added
                            var star = "<span class='glyphicon glyphicon-star star'></span>";

                            // add the appropriate amount
                            for (var i = 0; i < rating; i++) {

                                ratingString = ratingString + star;

                            }
                            // append it to the html body
                            appHTML += ratingString;
                        }




                        appHTML += "</div>\
                            </div>\
                            </a>\
                            </div>";
                        // append the entire html string to the applist element
                        $(searchTag).append(appHTML);
                    });

                } catch (TypeError) {

                    // used to catch errors that were thrown if the loop
                    // found an "undefined"
                    return;
                }
            }

            /*
            Similar to previous method except it populates a list 
            instead of the tiles 
            */
            function populateList(page, admin) {

                appHTML = "<table class='table table-striped'>\
            <tr>";
                if (admin) {
                    appHTML += "<td>AppID</td>";
                }
                appHTML += "<td>AppName</td>\
            <td>Publisher</td>\
            <td>Price</td>\
            <td></td>\
            </tr>\'";
                endingTag = "</table>";
                try {
                    page.forEach(function(app) {
                        appHTML += "<tr>";
                        if (admin) {
                            appHTML += "<th>" + app.id + "</th>";
                        }
                        appHTML += "<th>" + app.name + "</th>\
                <th>" + app.publisher + "</th>\
                <th>" + app.price + "</th>";
                        if (admin) {
                            appHTML += "<th><a class='btn btn-sm btn-primary' href='../editApp.php?app_id=" + app.id + "' target='_blank'>EDIT</a></th>";
                        } else {
                            appHTML += "<th><a class='btn btn-sm btn-primary' href='appPage.php?app_id=" + app.id + "' target='_blank'>VIEW</a></th>";
                        }

                        appHTML += "<tr>";

                    });
                    $(searchTag).append(appHTML + endingTag);
                } catch (TypeError) {

                    // another catch to catch errors with undefined
                    return;
                }


            }


            /*
            Comparator for sorting the page list
            */
            function sortList(compare) {
                return function(a, b) {
                    if (a[compare] > b[compare]) {
                        return 1;
                    } else if (a[compare] < b[compare]) {
                        return -1;
                    }
                    return 0;
                }
            }

            /*
            Comparator for sorting the page list by reviews

            instead of comparing the values of the values of the key, it compares the length of the sub array
            associated with the key
            */
            function sortListLength(compare) {
                return function(a, b) {
                    if (a[compare].length > b[compare].length) {
                        return 1;
                    } else if (a[compare].length < b[compare].length) {
                        return -1;
                    }
                    return 0;
                }
            }





        }
    } catch (ReferenceError) {

    }




    /*
    This is triggered if the new-app element is present
    */
    if ($('#new-app')) {

        // hide the element at first
        $('#new-app').hide();

        // these are used to ensure that a genre
        // and age restriction entry have been 
        // created for each app
        var genreSelected = false;
        var ageRestrictSelected = false;



        $('#add-app').click(function() {

            // shows the menu when the add-app button is clicked

            $('#new-app').fadeToggle();
        });



        $('#genre').change(function() {

            // if the genre selector is changed from default

            // a genre has been selected
            genreSelected = true;

            // check to enable to the submit button
            allowSubmit(genreSelected, ageRestrictSelected);
        });


        $('#age-restrict').change(function() {

            // if the age restricted selector has been changed
            // from default

            // an age restriction has been selected
            ageRestrictSelected = true;

            // check to enable the submit button
            allowSubmit(genreSelected, ageRestrictSelected);


        })
        $('#submit').click(function() {});

        /*
        This function simply compares the genre and age vars 
        from above
        */
        function allowSubmit(genreBool, ageBool) {

            // if they have both been selected
            if (genreBool && ageBool) {

                // enable the submit button
                $('#submit').removeAttr('disabled');
                console.log("submit button enabled");
            }

        }
    }

    if ($('#edit-account')) {


        var edit = false;

        $('#edit').click(function() {
            $('#first-name').removeAttr('disabled');
            $('#surname').removeAttr('disabled');
            $('#email').removeAttr('disabled');
            $('#password').removeAttr('disabled');
            $(this).attr('disabled', true);
        });

        $('#edit-form').change(function() {
            console.log("first name changed");
            edit = true;
            checkSubmit();
        });


        function checkSubmit() {
            if (edit) {
                $('#details-submit').removeAttr('disabled');
            }
        }

    }

    try {
        // if the basket button and userKey exist this is triggered
        if ($('#basket-btn') && userKey) {
            // when the basket button is clicked
            $('#basket-btn').click(function() {

                // get the app id
                var appID = $('#appid').val();
                /*
                    Create the data for the AJAX body, including 
                    the API key
                */
                var addToBasket = {
                    item: appID,
                    basket: basketID,
                    key: userKey
                };

                // call the ajax method
                $.ajax({
                    url: "http://sharte14.lampt.eeecs.qub.ac.uk/GUGL_API/baskets/index.php?item",
                    method: "POST",
                    data: JSON.stringify(addToBasket),
                    contentType: "json",
                    success: function(result) {
                        // if successful, replace the cart count with itself incremented
                        var basketCount = parseInt($('#cart-count').text());

                        $('#cart-count').text(++basketCount);
                        console.log(result.item + ' added to basket');
                    },
                    error: function(result) {
                        console.log(result.message);
                    }
                });


            });
        }

        // this is triggered if the remove item and user key are present
        if ($('.remove-item') && userKey) {


            $('.remove-item').click(function() {

                /* when the remove item button is clicked, these values are
                    grabbed from hidden fields within the basket form
                */
                var itemid = $(this).find('#itemid').val();
                var basketid = $(this).find('#basketid').val();

                // create the data, including the users API key
                var itemData = {
                    item: itemid,
                    basket: basketid,
                    key: userKey
                };

                // call the ajax method
                $.ajax({
                    url: "http://sharte14.lampt.eeecs.qub.ac.uk/GUGL_API/baskets/index.php?item",
                    method: "DELETE",
                    data: JSON.stringify(itemData),
                    contentType: "json",
                    success: function(result) {
                        console.log(result.message);
                        // on success, reload the page to see the updated basket
                        location.reload();
                    }

                });
            });
        }
    } catch (error) {

    }

    if ($('#review-form')) {
        var ratingVal;
        var reviewContent;
        var appID = $('#appid').val();

        $('#review-rating').change(function() {
            $('#review-submit').removeAttr('disabled');
            ratingVal = $('input[name="review-rating"]:checked').val();
        });

        $('#review-submit').click(function(e) {
            e.preventDefault();
            console.log("rating: " + ratingVal);
            reviewContent = $('#content').val();
            console.log("content: " + reviewContent);

            var reviewData = {
                key: userKey,
                user: userID,
                app: appID,
                content: reviewContent,
                rating: ratingVal
            };

            $.ajax({
                url: "http://sharte14.lampt.eeecs.qub.ac.uk/GUGL_API/reviews/",
                method: 'POST',
                data: JSON.stringify(reviewData),
                contentType: 'json',
                success: function(result) {
                    console.log(result.message);
                    location.reload();
                },
                error: function() {
                    console.log("an error occurred");
                }
            });
        });

    }
});