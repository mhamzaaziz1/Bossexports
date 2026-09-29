<div class="panel_s">
    <div class="panel-body">
        <p class="bold">New vs Recurring Revenue</p>
        <div style="height:350px"><canvas id="loyalty_metrics"></canvas></div>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function(event) {
        var loyaltyData = <?php echo json_encode($widget_data); ?>;
        new Chart($('#loyalty_metrics'), {
            type: 'bar',
            data: loyaltyData,
            options: { 
                responsive:true, 
                maintainAspectRatio:false, 
                scales:{
                    xAxes: [{stacked: true}],
                    yAxes: [{stacked: true}]
                }
            }
        });
    });
</script>
