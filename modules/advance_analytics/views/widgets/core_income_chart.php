<div class="panel_s">
    <div class="panel-body">
        <p class="bold">Total Income</p>
        <div style="height:300px"><canvas id="total_income"></canvas></div>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function(event) {
        var incomeData = <?php echo json_encode($widget_data); ?>;
        new Chart($('#total_income'), {
            type: 'bar',
            data: incomeData,
            options: { responsive:true, maintainAspectRatio:false, scales:{yAxes:[{ticks:{beginAtZero:true}}]} }
        });
    });
</script>
