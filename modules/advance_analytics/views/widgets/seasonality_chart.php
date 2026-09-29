<div class="panel_s">
    <div class="panel-body">
        <p class="bold">Seasonality Heatmap (Top 10)</p>
        <div style="height:250px"><canvas id="seasonality_chart"></canvas></div>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function(event) {
        var seasonRaw = <?php echo json_encode($widget_data); ?>;
        var seasonSets = [];
        var colors = ['#ef4444', '#f97316', '#f59e0b', '#84cc16', '#10b981', '#06b6d4', '#3b82f6', '#8b5cf6'];
        var ci = 0;
        for(var item in seasonRaw){
            if(seasonRaw.hasOwnProperty(item)){
                    seasonSets.push({ label: item.substring(0,10), data: Object.values(seasonRaw[item]), borderColor: colors[ci % colors.length], fill: false });
                    ci++;
            }
        }
        new Chart($('#seasonality_chart'), {
            type: 'line',
            data: { labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'], datasets: seasonSets },
            options: { responsive:true, maintainAspectRatio:false, tooltips:{mode:'index', intersect:false} }
        });
    });
</script>
