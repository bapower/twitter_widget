
/**
 * Start the tweet refresh countdown. Stop and call loadTweets when counter ends.
 *
 * @param   {string} elementName element id to put the timer
 * @param   {number} minutes to countdown
 * @param   {number} seconds to countdown
 *
 */
function startCountdown(elementName, minutes, seconds)
{
    var element, endTime, hours, minutes, msLeft, time;

    function convertTwoDigits(n)
    {
        return (n <= 9 ? "0" + n : n);
    }

    function updateTimer()
    {
        msLeft = endTime - (+new Date);
        if ( msLeft < 1000 ) {
            loadTweets('Refreshing tweets...');
        } else {
            time = new Date(msLeft);
            hours = time.getUTCHours();
            minutes = time.getUTCMinutes();
            element.innerHTML = (hours ? hours + 
                ':' + convertTwoDigits(minutes) : minutes) + 
                ':' + convertTwoDigits(time.getUTCSeconds());

            window.timer = setTimeout(updateTimer, time.getUTCMilliseconds() + 500);
        }
    }

    element = document.getElementById(elementName);
    endTime = (+new Date) + 1000 * (60*minutes + seconds) + 500;
    updateTimer();
}

/**
 * Make an ajax call to get and then load the embed html for resulting tweets. 
 *
 * @param   {string} message to display while loading
 *
 */
function loadTweets(message) 
{
    document.getElementById("countdown").innerHTML = '';
    var countdownText = $('.count-wrapper .message');
    var search;

    countdownText.text(message);

    if ($('p.error').length > 0) {
        $('#term').val('');
        search = '';
    } else {
        search = $('#term').val();
    }

    $.ajax({
      url: 'tweets.php',
      type: 'GET',
      data: 'search='+search,
      success: function(data) {

        var tweets = $('.tweets');

        if (data.length <= 1) {
            tweets.empty().append('<p class="error">No tweets found for that search. Try again.</p>');
        } else {
            tweets.empty().append(data);
        }

        countdownText.text('Tweets will refresh in ');

        clearTimeout(window.timer);
        $('#countdown').empty();
        
        startCountdown("countdown", 0, 30);
        
      },
      error: function(e) {
        countdownText.text('Error refreshing tweets ');
      }
    });
}

/**
 * Initialize listeners on button and input. Search and load tweets with new search term in callback. 
 *
 */
function initSearch() {
    $('.search-wrapper [type="button"]').on('click', function() {
        searchTweets()
    });

    $('.search-wrapper [type="text"]').on('keyup', function() {
        if (event.keyCode === 13) {
            searchTweets();
            $('.search-wrapper [type="text"]').blur(); 
        }
    });
}


/**
 * Perform search for new tweets.
 *
 */
function searchTweets() {
    clearTimeout(window.timer);
    $('#countdown').empty();
    loadTweets('Searching tweets...');
}

initSearch();
startCountdown("countdown", 0, 30);
