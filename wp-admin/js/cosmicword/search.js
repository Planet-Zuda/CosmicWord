jQuery(document).ready(function($) {
    var $searchInput = $("#plugin-search");
    var $pluginBrowser = $(".cosmicword-plugin-browser");
    var $loadMore = $("#load-more-plugins");
    var searchTimer;
    var currentPage = 1;
    var isLoading = false;
    var lastSearchTerm = "";
    
    var cosmicNonce = "cb8420e297";

    function performSearch(initialLoad, newSearch = false) {
        if (isLoading) return;
        
        if (newSearch) {
            currentPage = 1;
            $pluginBrowser.empty();
        }
        
        isLoading = true;
        var searchTerm = initialLoad ? "" : $searchInput.val().trim();
        lastSearchTerm = searchTerm;
        
        $("#search-status").html("<p>Loading plugins...</p>");
        $loadMore.hide();
        
        $.post(ajaxurl, {
            action: "cosmicword_search_plugins",
            search: searchTerm,
            page: currentPage,
            _ajax_nonce: cosmicNonce,
            initial_load: initialLoad ? 1 : 0
        })
        .done(function(response) {
            isLoading = false;
            if (response.success) {
                if (newSearch) {
                    $pluginBrowser.html(response.data.html);
                } else {
                    $pluginBrowser.append(response.data.html);
                }
                
                $("#search-status").html(response.data.total_plugins + " plugins found");
                
                if (response.data.has_more) {
                    $loadMore.show();
                } else {
                    $loadMore.hide();
                }
            } else {
                $("#search-status").html("<p class='error'>" + (response.data || "Search failed") + "</p>");
                $loadMore.hide();
            }
        })
        .fail(function(xhr, status, error) {
            isLoading = false;
            $("#search-status").html("<p class='error'>Search failed: " + error + "</p>");
            $loadMore.hide();
        });
    }

    $searchInput.on("input", function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
            if ($searchInput.val().trim().length < 3) {
                $("#search-status").html("<p>Please enter at least 3 characters to search</p>");
                return;
            }
            performSearch(false, true);
        }, 500);
    });

    $loadMore.on("click", function(e) {
        e.preventDefault();
        currentPage++;
        performSearch(false, false);
    });

    $("#plugin-search-button").on("click", function(e) {
        e.preventDefault();
        if ($searchInput.val().trim().length < 3) {
            $("#search-status").html("<p>Please enter at least 3 characters to search</p>");
            return;
        }
        performSearch(false, true);
    });
    
    performSearch(true, true);
});