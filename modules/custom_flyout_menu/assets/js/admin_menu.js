
// Custom Flyout Menu - Admin Sidebar JavaScript

(function ($) {
    "use strict";

    var body = $("body");
    var menu = $("#side-menu");

    if (!menu.length) {
        return;
    }


    $('.hide-menu').remove();
    $(document).on("click", ".hide-menu", function (e) {
        e.stopImmediatePropagation();
        closeAllFlyouts();
    });

    body.addClass("cfm-flyout");

    // Insert open-sidebar menu item after setup-menu-item
    var $setupMenuItem = $("#setup-menu-item");
    if ($setupMenuItem.length && !$("#open-sidebar-menu-item").length) {
        var openSidebarHtml = '<li id="open-sidebar-menu-item">' +
            '<a href="#" class="open-sidebar">' +
            '<i class="fa fa-angle-right menu-icon"></i>' +
            '<span class="menu-text">Close Sidebar</span>' +
            '</a>' +
            '</li>';
        $setupMenuItem.after(openSidebarHtml);
        
        // Set initial icon state based on current sidebar state
        setTimeout(function() {
            var $icon = $("#open-sidebar-menu-item .menu-icon");
            if (!$("body").hasClass("hide-sidebar")) {
                $icon.removeClass("fa-angle-right").addClass("fa-angle-left");
            }
        }, 100);
    }

    // Handle open-sidebar menu item click (toggle sidebar)
  $("#open-sidebar-menu-item .open-sidebar").click(function (e) {
        e.preventDefault();
        var setup_menu = $("#setup-menu-wrapper");
        var $icon = $("#open-sidebar-menu-item .menu-icon");
        
        if ($("body").hasClass("hide-sidebar")) {
            $('body.cfm-flyout .sidebar-user-profile').attr('style', 'display: block !important;');
            $("body").removeClass("hide-sidebar").addClass("show-sidebar");
            $icon.removeClass("fa-angle-right").addClass("fa-angle-left");
        } else {
            $('body.cfm-flyout .sidebar-user-profile').attr('style', 'display: none !important;');
            $("body").removeClass("show-sidebar").addClass("hide-sidebar");
            $icon.removeClass("fa-angle-left").addClass("fa-angle-right");
        }
        
        if (setup_menu.hasClass("display-block")) {
            $(".close-customizer").click();
        }
    });

    function isCollapsed() {
        return body.hasClass("hide-sidebar") && !body.hasClass("show-sidebar") && !body.hasClass("page-small");
    }

    function applyDefaultCollapsedState() {
        if (!body.hasClass("show-sidebar") && !body.hasClass("hide-sidebar") && !body.hasClass("page-small")) {
            body.addClass("hide-sidebar");
        }
    }

    applyDefaultCollapsedState();

    var activeItem = null;
    var hideTimer = null;
    var lastUserToggleAt = 0;


    function getMenuTitle($li) {
        var text = $.trim($li.children("a").find(".menu-text").first().text());
        if (!text) {
            text = $.trim($li.children("a").clone().children().remove().end().text());
        }
        return text;
    }

    function ensureTitle($submenu, title) {
        if (!title) {
            return;
        }

        // Check if buildGroupedSubmenu() decided NOT to show the title
        var shouldShowTitle = $submenu.data("cfmShouldShowTitle");
        if (shouldShowTitle === false) {
            // buildGroupedSubmenu() says we should NOT show the title
            // Don't create or modify it
            return;
        }

        var $title = $submenu.children(".cfm-flyout-title");
        if ($title.length) {
            $title.find("span").text(title);
            return;
        }

        $title = $("<li class=\"cfm-flyout-title\"><span></span></li>");
        $title.find("span").text(title);
        $submenu.prepend($title);
    }

    function getGroupMeta($item) {
        var groupId = $item.data("groupId") || $item.data("group") || $item.attr("data-group-id");
        var groupName = $item.data("groupName") || $item.data("groupLabel") || $item.attr("data-group-name");
        var anchor = $item.find("a").first();

        if (!groupId && anchor.length) {
            groupId = anchor.data("groupId") || anchor.data("group") || anchor.attr("data-group-id");
            groupName = groupName || anchor.data("groupName") || anchor.data("groupLabel") || anchor.attr("data-group-name");
        }

        if (!groupId) {
            return null;
        }

        return {
            id: String(groupId),
            name: groupName ? String(groupName) : String(groupId)
        };
    }

    function buildGroupedSubmenu($submenu) {
        if (!$submenu.length) {
            return;
        }

        if ($submenu.data("cfmGrouped")) {
            return;
        }

        if (!$submenu.data("cfmOriginal")) {
            $submenu.data("cfmOriginal", $submenu.html());
        }

        var $parentLi = $submenu.closest("li");
        var hideIfGrouped = $parentLi.attr("data-hide-title-if-grouped");
        // Handle different possible values: string "1", number 1, or boolean true
        hideIfGrouped = (hideIfGrouped === "1" || hideIfGrouped === 1 || hideIfGrouped === true);
        
        var titleNode = $submenu.children(".cfm-flyout-title").first();
        var items = $submenu.children("li").not(".cfm-flyout-title");
        var grouped = {};
        var groupOrder = [];
        var ungrouped = [];
        var hasAnyGroupedItems = false; // Track if any children have grouping

        items.each(function () {
            var $item = $(this);
            if ($item.hasClass("cfm-group-label") || $item.hasClass("cfm-group-separator")) {
                return;
            }

            var isGroupParent = $item.attr("data-is-group-parent");
            // Handle different possible values for isGroupParent
            if (isGroupParent === "1" || isGroupParent === 1 || isGroupParent === true) {
                return; // Skip group parent items entirely
            }

            var meta = getGroupMeta($item);
            var isNestedGrouped = $item.attr("data-is-nested-grouped");
            // Handle different possible values for isNestedGrouped
            isNestedGrouped = (isNestedGrouped === "1" || isNestedGrouped === 1 || isNestedGrouped === true);
            
            if (meta && meta.id) {
                if (!grouped[meta.id]) {
                    grouped[meta.id] = { name: meta.name, items: [] };
                    groupOrder.push(meta.id);
                }
                grouped[meta.id].items.push($item.clone(true, true));
                hasAnyGroupedItems = true;
            } else if (!isNestedGrouped) {
                ungrouped.push($item.clone(true, true));
            } else {
                // This is a STEP 4 item with grouping but no group metadata found
                // Treat as grouped so parent title hides
                hasAnyGroupedItems = true;
            }
        });

        var fragment = $("<div></div>");
        
        // Determine if we should show the parent title
        var hasUngrouped = ungrouped.length > 0;
        var shouldShowTitle;
        
        if (hideIfGrouped) {
            // Parent item has flag to hide title when grouped children exist
            // Hide title if ANY grouped items found (regardless of ungrouped items)
            shouldShowTitle = !hasAnyGroupedItems;
        } else {
            // No flag: show title if there are no grouped items OR no children at all
            shouldShowTitle = groupOrder.length === 0;
        }
        
        // Store the decision for ensureTitle() to respect
        $submenu.data("cfmShouldShowTitle", shouldShowTitle);
        
        if (titleNode.length && shouldShowTitle) {
            fragment.append(titleNode.clone(true, true));
        }

        groupOrder.forEach(function (groupId, index) {
            var group = grouped[groupId];
            fragment.append("<li class=\"cfm-group-label\">" + group.name.toUpperCase() + "</li>");
            fragment.append("<li><hr class=\"cfm-group-separator\" /></li>");

            group.items.forEach(function ($node) {
                fragment.append($node);
            });

            var hasMoreGroups = index < groupOrder.length - 1;
            if (hasMoreGroups || hasUngrouped) {
                fragment.append("<li><hr class=\"cfm-group-separator\" /></li>");
            }
        });

        if (hasUngrouped) {
            ungrouped.forEach(function ($node) {
                fragment.append($node);
            });
        }

        $submenu.empty();
        fragment.children().appendTo($submenu);
        $submenu.data("cfmGrouped", true);
    }

    function restoreSubmenu($submenu) {
        var original = $submenu.data("cfmOriginal");
        if (original) {
            $submenu.html(original);
            $submenu.removeData("cfmOriginal");
            $submenu.removeData("cfmGrouped");
        }
    }

    function positionFlyout($li, $submenu) {
        if (!$li.length || !$submenu.length) {
            return;
        }

        var rect = $li[0].getBoundingClientRect();
        var padding = 10;
        var maxHeight = Math.max(160, window.innerHeight - padding * 2);

        $submenu.css("maxHeight", maxHeight + "px");

        var submenuHeight = $submenu.outerHeight();
        var maxTop = window.innerHeight - submenuHeight - padding;

        if (maxTop < padding) {
            maxTop = padding;
        }

        var top = rect.top;
        if (top > maxTop) {
            top = maxTop;
        }

        if (top < padding) {
            top = padding;
        }

        $submenu.css("top", top + "px");
    }

    function openFlyout($li) {
        var $submenu = $li.children("ul.nav-second-level");
        if (!$submenu.length) {
            return;
        }

        clearTimeout(hideTimer);

        if (isCollapsed()) {
            buildGroupedSubmenu($submenu);
        }

        if (activeItem && activeItem[0] !== $li[0]) {
            closeFlyout(activeItem);
        }

        activeItem = $li;
        var title = getMenuTitle($li);
        ensureTitle($submenu, title);

        $li.addClass("cfm-flyout-open");
        $li.children("a").attr("aria-expanded", "true");
        $submenu.attr("aria-expanded", "true");

        positionFlyout($li, $submenu);
    }

    function closeFlyout($li) {
        if (!$li || !$li.length) {
            return;
        }

        $li.removeClass("cfm-flyout-open");
        $li.children("a").attr("aria-expanded", "false");
        $li.children("ul.nav-second-level").attr("aria-expanded", "false");

        if (activeItem && activeItem[0] === $li[0]) {
            activeItem = null;
        }
    }

    function closeAllFlyouts() {
        menu.find("li.cfm-flyout-open").each(function () {
            closeFlyout($(this));
        });
        activeItem = null;
    }

    // Get trigger mode from settings (default: 'hover')
    var triggerMode = (window.cfmSettings && window.cfmSettings.triggerMode) ? window.cfmSettings.triggerMode : 'hover';

    // =================================================================
    // EVENT HANDLERS FOR COLLAPSED SIDEBAR (Icon-only mode)
    // =================================================================
    
    // Setup event handlers based on trigger mode
    if (triggerMode === 'click') {
        // CLICK MODE: Open on click, close on click outside
        menu.on("click", "> li > a", function (e) {
            if (!isCollapsed()) {
                // For full sidebar mode, handle click differently
                handleFullSidebarClick(e, $(this));
                return;
            }

            var $link = $(this);
            var $li = $link.parent("li");
            var $submenu = $li.children("ul.nav-second-level");

            if (!$submenu.length) {
                return;
            }

            e.stopImmediatePropagation();

            var href = ($link.attr("href") || "").trim();
            if (!href || href === "#" || href.indexOf("javascript:") === 0) {
                e.preventDefault();
            }

            // Toggle functionality: Check if flyout is already open
            if ($li.hasClass("cfm-flyout-open")) {
                // If already open, close it
                closeFlyout($li);
            } else {
                // If closed, open it (and close others)
                openFlyout($li);
            }
        });
    } else {
        // HOVER MODE (default): Open on hover, close on mouseleave
        menu.on("mouseenter", "> li", function () {
            if (!isCollapsed()) {
                // For full sidebar mode, use hover to open flyout
                openFullSidebarFlyout($(this));
                return;
            }

            openFlyout($(this));
        });

        menu.on("mouseleave", "> li", function () {
            if (!isCollapsed()) {
                // For full sidebar mode, close flyout on mouseleave
                closeFullSidebarFlyout($(this));
                return;
            }

            var $li = $(this);
            if (!$li.hasClass("cfm-flyout-open")) {
                return;
            }

            clearTimeout(hideTimer);
            hideTimer = setTimeout(function () {
                closeFlyout($li);
            }, 160);
        });

        menu.on("mouseenter", "ul.nav-second-level", function () {
            if (!isCollapsed()) {
                clearTimeout(hideTimer);
                return;
            }

            clearTimeout(hideTimer);
        });

        menu.on("mouseleave", "ul.nav-second-level", function () {
            if (!isCollapsed()) {
                var $li = $(this).closest("#side-menu > li");
                clearTimeout(hideTimer);
                hideTimer = setTimeout(function () {
                    closeFlyout($li);
                }, 160);
                return;
            }

            var $li = $(this).closest("li");
            clearTimeout(hideTimer);
            hideTimer = setTimeout(function () {
                closeFlyout($li);
            }, 160);
        });
    }

    // =================================================================
    // FUNCTIONS FOR FULL SIDEBAR MODE (Expanded sidebar with flyout)
    // =================================================================

    /**
     * Open flyout panel for full sidebar mode
     * Prevents default submenu expansion behavior
     */
    function openFullSidebarFlyout($li) {
        var $submenu = $li.children("ul.nav-second-level");
        if (!$submenu.length) {
            return;
        }

        clearTimeout(hideTimer);

        // Build grouped submenu structure
        buildGroupedSubmenu($submenu);

        // Close other open flyouts
        if (activeItem && activeItem[0] !== $li[0]) {
            closeFlyout(activeItem);
        }

        activeItem = $li;
        var title = getMenuTitle($li);
        ensureTitle($submenu, title);

        // Add class to show flyout
        $li.addClass("cfm-flyout-open");
        $li.children("a").attr("aria-expanded", "true");
        $submenu.attr("aria-expanded", "true");

        // Position flyout dynamically
        positionFullSidebarFlyout($li, $submenu);
    }

    /**
     * Close flyout panel for full sidebar mode
     */
    function closeFullSidebarFlyout($li) {
        clearTimeout(hideTimer);
        hideTimer = setTimeout(function () {
            closeFlyout($li);
        }, 160);
    }

    /**
     * Handle click event for full sidebar mode
     */
    function handleFullSidebarClick(e, $link) {
        var $li = $link.parent("li");
        var $submenu = $li.children("ul.nav-second-level");

        if (!$submenu.length) {
            return;
        }

        e.stopImmediatePropagation();
        e.preventDefault();

        // Toggle flyout open/close
        if ($li.hasClass("cfm-flyout-open")) {
            closeFlyout($li);
        } else {
            openFullSidebarFlyout($li);
        }
    }

    /**
     * Position flyout panel for full sidebar mode
     * Calculates position based on menu item location
     */
    function positionFullSidebarFlyout($li, $submenu) {
        if (!$li.length || !$submenu.length) {
            return;
        }

        var rect = $li[0].getBoundingClientRect();
        var padding = 10;
        var maxHeight = Math.max(160, window.innerHeight - padding * 2);

        $submenu.css("maxHeight", maxHeight + "px");

        var submenuHeight = $submenu.outerHeight();
        var maxTop = window.innerHeight - submenuHeight - padding;

        if (maxTop < padding) {
            maxTop = padding;
        }

        var top = rect.top;
        if (top > maxTop) {
            top = maxTop;
        }

        if (top < padding) {
            top = padding;
        }

        $submenu.css("top", top + "px");
    }

    // =================================================================
    // SHARED EVENT HANDLERS (Both collapsed and full sidebar modes)
    // =================================================================

    // =================================================================
    // SHARED EVENT HANDLERS (Both collapsed and full sidebar modes)
    // =================================================================

    // Close all flyouts when clicking outside menu
    $(document).on("click", function (e) {
        if ($(e.target).closest("#menu").length) {
            return;
        }

        closeAllFlyouts();
    });

    // Handle window resize - reposition flyouts or close them
    $(window).on("resize", function () {
        if (activeItem) {
            if (isCollapsed()) {
                positionFlyout(activeItem, activeItem.children("ul.nav-second-level"));
            } else {
                positionFullSidebarFlyout(activeItem, activeItem.children("ul.nav-second-level"));
            }
        }

        // Close flyouts and restore on mode change
        if (!isCollapsed()) {
            menu.find("ul.nav-second-level").each(function () {
                restoreSubmenu($(this));
            });
        }
    });

    // Reposition flyout on sidebar scroll
    $("#menu").on("scroll", function () {
        if (!activeItem) {
            return;
        }

        if (isCollapsed()) {
            positionFlyout(activeItem, activeItem.children("ul.nav-second-level"));
        } else {
            positionFullSidebarFlyout(activeItem, activeItem.children("ul.nav-second-level"));
        }
    });

    // Handle sidebar toggle button click
    $(document).on("click", ".hide-menu", function () {
        lastUserToggleAt = Date.now();
        setTimeout(function () {
            // Always restore submenu structure when toggling
            closeAllFlyouts();
            menu.find("ul.nav-second-level").each(function () {
                restoreSubmenu($(this));
            });
        }, 0);
    });

    // Auto-collapse observer for sidebar state changes
    /*if (window.MutationObserver) {
        var observer = new MutationObserver(function () {
            // Auto re-collapse if sidebar expands unexpectedly
            if (body.hasClass("show-sidebar") && !body.hasClass("page-small")) {
                if (Date.now() - lastUserToggleAt > 350) {
                    body.removeClass("show-sidebar").addClass("hide-sidebar");
                }
            }

            // Restore submenu structure when switching modes
            menu.find("ul.nav-second-level").each(function () {
                restoreSubmenu($(this));
            });
        });

        observer.observe(document.body, { attributes: true, attributeFilter: ["class"] });
    }*/

    // Close flyouts on Escape key
    $(document).on("keydown", function (e) {
        if (e.key === "Escape") {
            closeAllFlyouts();
        }
    });

})(jQuery);
