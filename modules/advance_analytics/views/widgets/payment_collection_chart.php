<div class="panel_s">
    <div class="panel-body">
        <p class="bold">Payment Collection Efficiency</p>
        <div style="height:300px"><canvas id="payment_collection"></canvas></div>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function(event) {
        var collectData = <?php echo json_encode($widget_data); ?>;
        new Chart($('#payment_collection'), {
            type: 'line',
            data: collectData,
            options: { 
                responsive:true, 
                maintainAspectRatio:false,
                scales:{yAxes:[{ticks:{beginAtZero:true}}]} 
            }
        });
    });
</script>
