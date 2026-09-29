<div class="panel_s">
    <div class="panel-body">
        <p class="bold">RFM Segmentation</p>
        <div style="height:350px"><canvas id="rfm_chart"></canvas></div>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function(event) {
        var rfmRaw = <?php echo json_encode($widget_data['segments']); ?>; // Adjusted to match expected model output structure if needed
        var rfmColors = Object.keys(rfmRaw).map(function(label){
            if(label === 'Champions') return '#10b981'; if(label === 'Loyal Customers') return '#34d399'; if(label === 'At Risk') return '#f59e0b'; return '#3b82f6';
        });
        new Chart($('#rfm_chart'), {
            type: 'bar',
            data: { labels: Object.keys(rfmRaw), datasets: [{ label: 'Count', data: Object.values(rfmRaw), backgroundColor: rfmColors }] },
            options: { responsive:true, maintainAspectRatio:false, legend:{display:false}, scales:{yAxes:[{ticks:{beginAtZero:true}}]} }
        });
    });
</script>
