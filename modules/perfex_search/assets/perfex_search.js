(function($) {
    "use strict";

    $(document).ready(function (){

        $("#menu-filter").keyup(function () {

            var aside_search_button = $("#aside_search_button");

            var a = $(this).val().toLocaleLowerCase();

            $('.sidebar-form').nextAll('li').addClass("hidden");
            $('#side-menu > li:contains("' + a + '")').removeClass("hidden");

            if (a === ""){
                aside_search_button

                    .html('<i class="fa fa-search"></i>')

                    .removeClass("search_remove");

                return;
            }

            aside_search_button

                .html('<i class="fa fa-remove"></i>')

                .addClass("search_remove");
        });

        $("#aside_search_button").on("click", function () {

            var $menu_filter = $("#menu-filter");

            if ($(this).hasClass("search_remove")) {

                $(this).html('<i class="fa fa-search"></i>').removeClass("search_remove");

                $menu_filter.val("");

                $('#side-menu > li').removeClass("hidden");

            }

            $menu_filter.focus();

        });

        $.expr[":"].contains = $.expr.createPseudo(function (a) {
            return function (b) {
                return $(b).text().toLocaleLowerCase().indexOf(a.toLocaleLowerCase()) >= 0
            }
        });

    })

})(jQuery);
