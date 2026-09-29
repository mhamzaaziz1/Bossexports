<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4><?php echo _l('smart_documentation'); ?></h4>
                        <p>Welcome to the Smart Documentation Hub.</p>
                        <div class="_buttons">
                            <a href="<?php echo admin_url('smart_documentation/article'); ?>" class="btn btn-info pull-left display-block"><?php echo _l('sd_new_article'); ?></a>
                            <div class="clearfix"></div>
                        </div>
                        
                        <div class="clearfix"></div>
                        <br />
                        
                        <!-- Search Bar -->
                        <div class="input-group" style="margin-bottom: 40px;">
                            <input type="text" id="search_input" class="form-control" placeholder="<?php echo _l('search'); ?>...">
                            <span class="input-group-btn">
                                <button class="btn btn-default" type="button" id="search_btn"><i class="fa fa-search"></i></button>
                            </span>
                        </div>

                        <div id="loading_spinner" class="text-center hide">
                            <i class="fa fa-spinner fa-spin fa-2x"></i>
                        </div>

                        <div id="articles_grid" class="articles-grid">
                            <!-- Cards will be injected here via JS -->
                        </div>

                        <style>
                            .articles-grid {
                                display: grid;
                                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                                gap: 20px;
                            }
                            .doc-card {
                                background: #fff;
                                border-radius: 12px;
                                padding: 20px;
                                box-shadow: 0 4px 6px rgba(0,0,0,0.05);
                                transition: transform 0.2s, box-shadow 0.2s;
                                border: 1px solid #e5e5e5;
                                display: flex;
                                flex-direction: column;
                                height: 100%;
                            }
                            .doc-card:hover {
                                transform: translateY(-5px);
                                box-shadow: 0 8px 15px rgba(0,0,0,0.1);
                            }
                            .doc-card-header {
                                display: flex;
                                justify-content: space-between;
                                align-items: flex-start;
                                margin-bottom: 15px;
                            }
                            .doc-icon-wrapper {
                                width: 40px;
                                height: 40px;
                                background: #f0f4f7;
                                border-radius: 50%;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                font-size: 18px;
                                color: #333;
                            }
                            .doc-save-icon {
                                color: #ccc;
                                cursor: pointer;
                            }
                            .doc-company-name {
                                font-size: 13px;
                                font-weight: 600;
                                color: #333;
                                margin-bottom: 2px;
                            }
                            .doc-time {
                                font-size: 11px;
                                color: #999;
                            }
                            .doc-title {
                                font-size: 18px;
                                font-weight: 700;
                                color: #1e1e1e;
                                margin: 10px 0;
                                line-height: 1.3;
                            }
                            .doc-tags {
                                display: flex;
                                flex-wrap: wrap;
                                gap: 8px;
                                margin-bottom: auto;
                            }
                            .doc-tag {
                                background: #f0f0f0;
                                color: #555;
                                padding: 4px 10px;
                                border-radius: 6px;
                                font-size: 11px;
                                font-weight: 500;
                            }
                            .doc-card-footer {
                                display: flex;
                                justify-content: space-between;
                                align-items: center;
                                margin-top: 20px;
                                padding-top: 15px;
                                border-top: 1px solid #f0f0f0;
                            }
                            .doc-salary {
                                font-size: 14px;
                                font-weight: 600;
                                color: #333;
                            }
                            .doc-location {
                                font-size: 12px;
                                color: #888;
                            }
                            .doc-btn {
                                background: #000;
                                color: #fff;
                                padding: 8px 16px;
                                border-radius: 8px;
                                font-size: 12px;
                                font-weight: 600;
                                text-decoration: none;
                                transition: background 0.2s;
                            }
                            .doc-btn:hover {
                                background: #333;
                                color: #fff;
                            }
                            
                            /* Utility Classes */
                            .flex { display: flex; }
                            .flex-col { flex-direction: column; }
                            .items-center { align-items: center; }
                            .gap-2 { gap: 8px; }
                            .mb-1 { margin-bottom: 4px; }
                        </style>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</script>

<!-- Article View Modal -->
<div class="modal fade" id="article_view_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document" style="width: 80%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="view_article_title">Article Title</h4>
            </div>
            <div class="modal-body">
                 <div id="view_article_meta" class="text-muted mbottom15"></div>
                 <hr />
                 <div id="view_article_content" class="article-content-wrapper"></div>
            </div>
            <div class="modal-footer">
                <a href="#" id="view_article_edit_btn" class="btn btn-default">Edit Article</a>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        
        let typingTimer;
        let fetchedArticlesMap = {}; // Store articles by ID for quick access
        
        const doneTypingInterval = 500; // time in ms
        const $input = $('#search_input');
        const $grid = $('#articles_grid');
        const $spinner = $('#loading_spinner');

        function fetchArticles(query = '') {
            $grid.addClass('hide');
            $spinner.removeClass('hide');
            
            $.ajax({
                url: '<?php echo admin_url("smart_documentation/get_articles_ajax"); ?>',
                type: 'GET',
                data: {q: query},
                dataType: 'json',
                success: function(data) {
                    if (Array.isArray(data)) {
                        fetchedArticlesMap = {}; // Reset map
                        data.forEach(art => fetchedArticlesMap[art.id] = art);
                        renderArticles(data);
                    } else {
                        console.error("Invalid response format:", data);
                        $grid.html('<div class="alert alert-danger">Error loading articles. Check console for details.</div>');
                        $grid.removeClass('hide');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                    $grid.html('<div class="alert alert-danger">Error: ' + error + '</div>');
                },
                complete: function() {
                    $spinner.addClass('hide');
                    $grid.removeClass('hide');
                }
            });
        }

        function renderArticles(articles) {
            $grid.empty();
            if (articles.length === 0) {
                $grid.html('<div class="col-md-12 text-center text-muted"><p><?php echo _l("no_entries_found"); ?></p></div>');
                return;
            }

            let html = '';
            articles.forEach(article => {
                // Formatting date
                const date = new Date(article.created_at);
                const timeAgo = timeSince(date);
                
                // Fallbacks
                // User Request: "general need to be selected module name if not just leave it blank"
                let headerTitle = '';
                if (article.related_module) {
                    // Format system name: "smart_documentation" -> "Smart Documentation"
                    headerTitle = article.related_module.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                }
                
                const catIcon = article.category_icon || 'fa-file-text-o';
                const sectionName = article.section_name || 'Uncategorized';
                
                html += `
                <div class="doc-card">
                    <div class="doc-card-header">
                        <div class="flex flex-col">
                           <div class="flex items-center gap-2 mb-1">
                                <div class="doc-icon-wrapper">
                                    <i class="fa ${catIcon}"></i>
                                </div>
                                <div>
                                     <div class="doc-company-name">${headerTitle}</div>
                                     <div class="doc-time">${timeAgo} ago</div>
                                </div>
                           </div>
                        </div>
                        <div class="doc-save-icon"><i class="fa fa-bookmark-o"></i></div>
                    </div>
                    
                    <h3 class="doc-title">${article.title}</h3>
                    
                    <div class="doc-tags">
                        <span class="doc-tag">${sectionName}</span>
                        <span class="doc-tag">${article.status === 'published' ? 'Published' : 'Draft'}</span>
                         ${article.visibility ? '<span class="doc-tag">' + article.visibility + '</span>' : ''}
                    </div>
                    
                    <div class="doc-card-footer">
                        <div class="flex flex-col">
                            <!-- Click handler for View Modal -->
                            <div class="doc-location" style="cursor:pointer;" onclick="openViewModal(${article.id})">
                                <i class="fa fa-eye"></i> View
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                             <a href="<?php echo admin_url('smart_documentation/delete_article/'); ?>${article.id}" class="text-danger" onclick="return confirm('<?php echo _l('confirm_action_prompt'); ?>');" title="<?php echo _l('delete'); ?>"><i class="fa fa-trash"></i></a>
                             <a href="<?php echo admin_url('smart_documentation/article/'); ?>${article.id}" class="doc-btn">Edit</a>
                        </div>
                    </div>
                </div>
                `;
            });
            $grid.html(html);
        }

        // Expose function globally for onclick
        window.openViewModal = function(id) {
            const article = fetchedArticlesMap[id];
            if (!article) return;

            $('#view_article_title').text(article.title);
            
            // Format meta info
            const date = new Date(article.created_at);
            const dateStr = date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
            $('#view_article_meta').html(`
                <span class="label label-default">${article.category_name || 'General'}</span>
                <span class="label label-info">${article.section_name || 'Uncategorized'}</span> | 
                <small>Created: ${dateStr}</small>
            `);

            // Content
            $('#view_article_content').html(article.content);
            
            // Edit Link
            $('#view_article_edit_btn').attr('href', '<?php echo admin_url('smart_documentation/article/'); ?>' + article.id);

            $('#article_view_modal').modal('show');
        };

        // Helper for Time Ago
        function timeSince(date) {
            var seconds = Math.floor((new Date() - date) / 1000);
            
            // Fix for negative seconds (future dates or server time mismatch)
            if (seconds < 0) seconds = 0;

            var interval = seconds / 31536000;
            if (interval > 1) return Math.floor(interval) + " years";
            interval = seconds / 2592000;
            if (interval > 1) return Math.floor(interval) + " months";
            interval = seconds / 86400;
            if (interval > 1) return Math.floor(interval) + " days";
            interval = seconds / 3600;
            if (interval > 1) return Math.floor(interval) + " hours";
            interval = seconds / 60;
            if (interval > 1) return Math.floor(interval) + " minutes";
            return Math.floor(seconds) + " seconds";
        }

        // Search Listener - Button Click
        $('#search_btn').on('click', function() {
            clearTimeout(typingTimer);
            fetchArticles($input.val());
        });

        // Search Listener - Enter Key
        $input.on('keydown', function(e) {
            if(e.which == 13) {
                clearTimeout(typingTimer);
                fetchArticles($input.val());
                e.preventDefault(); // Prevent form submission if inside a form
            }
        });

        // Search Listener - Typing (Debounced)
        $input.on('keyup', function () {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(function() {
                fetchArticles($input.val());
            }, doneTypingInterval);
        });

        // Initial Load
        fetchArticles('');
    });
</script>
</body>
</html>
