<?php
// This function should be added to sales_helper.php
// Temporary fix - creating this function based on the existing get_items_by_type pattern

/**
 * Get all items by type with full details including custom fields
 * Similar to get_items_by_type but includes additional item information
 *
 * @param string $type rel_type value
 * @param mixed  $id
 *
 * @return array
 */
function est_get_items_by_type($type, $id)
{
    $CI = &get_instance();
    
    // First get the basic items
    $items = get_items_by_type($type, $id);
    
    // Add any additional processing if needed
    // For now, just return the same as get_items_by_type
    // This can be extended later to include more details
    
    return $items;
}
?>
