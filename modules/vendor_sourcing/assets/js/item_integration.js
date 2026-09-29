$(function(){
    // Hook into the item modal show event
    $('body').on('show.bs.modal', '#sales_item_modal', function (event) {
        var $itemModal = $('#sales_item_modal');
        var id = $(event.relatedTarget).data('id');
        
        // Let the default logic run then inject via a slight timeout to ensure DOM is ready and clean
        setTimeout(function() {
            // Remove existing vendor section if it exists
            $('#vendor_sourcing_section').remove();
            
            // Create container
            var container = '<div id="vendor_sourcing_section" class="col-md-12" style="margin-top:20px;">Loading vendors...</div>';
            
            $itemModal.find('.modal-body .row').append(container);
            
            var url = admin_url + 'vendor_sourcing/get_item_vendors_ui';
            if (typeof(id) !== 'undefined' && id != '') {
                url += '/' + id;
            }
            
            $.get(url, function(response) {
                $('#vendor_sourcing_section').html(response);
            });
        }, 100);
    });
});
