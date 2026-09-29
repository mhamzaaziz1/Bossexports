<div class="panel_s">
    <div class="panel-body">
        <p class="bold">Expenses vs Income</p>
        <div style="height:350px"><canvas id="expense_income"></canvas></div>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function(event) {
        var expIncData = <?php echo json_encode($widget_data); ?>;
        new Chart($('#expense_income'), {
            type: 'bar',
            data: expIncData,
            options: { responsive:true, maintainAspectRatio:false, scales:{yAxes:[{ticks:{beginAtZero:true}}]} }
        });
    });
</script>
