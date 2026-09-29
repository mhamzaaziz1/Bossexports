// Custom Flyout Menu - Menu Builder JavaScript

(function($) {
    'use strict';

    if (typeof $ === 'undefined') {
        console.warn('Custom Flyout Menu builder requires jQuery.');
        return;
    }

    class MenuBuilder {
        constructor(area) {
            this.area = area;
            this.init();
        }
        
        init() {
            this.bindEvents();
            this.loadMenuData();
        }
        
        bindEvents() {
            const self = this;
            
            // Add group button
            $('#cfm-add-group').on('click', function() {
                self.showGroupModal();
            });
            
            // Save group button
            $('#cfm-save-group').on('click', function() {
                self.saveGroup();
            });
            
            // Add item button
            $('#cfm-add-item').on('click', function() {
                self.showItemModal();
            });
            
            // Save item button
            $('#cfm-save-item').on('click', function() {
                self.saveItem();
            });
            
            // Group name to slug auto-fill
            $('#group-name').on('input', function() {
                if (!$('#group-id').val()) {
                    const slug = $(this).val().toLowerCase().replace(/[^a-z0-9]+/g, '_');
                    $('#group-slug').val(slug);
                }
            });
        }
        
        loadMenuData() {
            const self = this;
            $.get(admin_url + 'custom_flyout_menu/get_menu_structure', { area: this.area }, function(data) {
                self.renderGroups(data.groups);
                self.renderItems(data.items);
                self.populateGroupSelect(data.groups);
                self.populateParentItemsSelect(data.items);
            }, 'json');
        }
        
        renderGroups(groups) {
            const $tbody = $('#cfm-groups-table-body');
            $tbody.empty();
            
            if (!groups || groups.length === 0) {
                $tbody.append('<tr><td colspan="6" class="text-center text-muted">No groups yet. Click "Add Group" to create one.</td></tr>');
                return;
            }
            
            groups.forEach(group => {
                const activeChecked = group.active == 1 ? 'checked' : '';
                const activeSwitch = `
                    <div class="onoffswitch">
                        <input type="checkbox" data-switch-url="${admin_url}custom_flyout_menu/toggle_group_status" 
                               name="onoffswitch" class="onoffswitch-checkbox" 
                               id="group_${group.id}" data-id="${group.id}" ${activeChecked}>
                        <label class="onoffswitch-label" for="group_${group.id}"></label>
                    </div>
                    <span class="hide">${group.active == 1 ? 'Active' : 'Inactive'}</span>
                `;
                
                const $row = $(`
                    <tr data-group-id="${group.id}">
                        <td>${group.id}</td>
                        <td>${escapeHtml(group.name)}</td>
                        <td>${escapeHtml(group.slug)}</td>
                        <td>${group.sort_order}</td>
                        <td>${activeSwitch}</td>
                        <td>
                            <a href="#" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 edit-group" data-id="${group.id}">
                                <i class="fa-regular fa-pen-to-square fa-lg"></i>
                            </a>
                            <a href="#" class="tw-mt-px tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 delete-group" data-id="${group.id}">
                                <i class="fa-regular fa-trash-can fa-lg"></i>
                            </a>
                        </td>
                    </tr>
                `);
                
                $row.find('.edit-group').on('click', (e) => {
                    e.preventDefault();
                    this.editGroup(group.id);
                });
                
                $row.find('.delete-group').on('click', (e) => {
                    e.preventDefault();
                    this.deleteGroup(group.id);
                });
                
                $tbody.append($row);
            });
            
            // Initialize DataTables if not already initialized
            this.initDataTables();
        }
        
        renderItems(items) {
            const $tbody = $('#cfm-items-table-body');
            $tbody.empty();
            
            if (!items || items.length === 0) {
                $tbody.append('<tr><td colspan="8" class="text-center text-muted">No items yet. Click "Add Item" to create one.</td></tr>');
                return;
            }
            
            items.forEach(item => {
                this.renderItemRow(item, $tbody, 0);
            });
            
            // Initialize DataTables if not already initialized
            this.initDataTables();
        }
        
        renderItemRow(item, $container, level) {
            const activeChecked = item.active == 1 ? 'checked' : '';
            const activeSwitch = `
                <div class="onoffswitch">
                    <input type="checkbox" data-switch-url="${admin_url}custom_flyout_menu/toggle_item_status" 
                           name="onoffswitch" class="onoffswitch-checkbox" 
                           id="item_${item.id}" data-id="${item.id}" ${activeChecked}>
                    <label class="onoffswitch-label" for="item_${item.id}"></label>
                </div>
                <span class="hide">${item.active == 1 ? 'Active' : 'Inactive'}</span>
            `;
            
            const $row = $(`
                <tr data-item-id="${item.id}" data-level="${level}">
                    <td>${item.id}</td>
                    <td>${escapeHtml(item.label)}</td>
                    <td>${item.group_name || ''}</td>
                    <td>${item.parent_label || ''}</td>
                    <td>${escapeHtml(item.url)}</td>
                    <td><i class="ph ${item.icon || ''}"></i> ${item.icon}</td>
                    <td>${activeSwitch}</td>
                    <td>
                        <a href="#" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 edit-item" data-id="${item.id}">
                            <i class="fa-regular fa-pen-to-square fa-lg"></i>
                        </a>
                        <a href="#" class="tw-mt-px tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 delete-item" data-id="${item.id}">
                            <i class="fa-regular fa-trash-can fa-lg"></i>
                        </a>
                    </td>
                </tr>
            `);
            
            $row.find('.edit-item').on('click', (e) => {
                e.preventDefault();
                this.editItem(item.id);
            });
            
            $row.find('.delete-item').on('click', (e) => {
                e.preventDefault();
                this.deleteItem(item.id);
            });
            
            $container.append($row);
            
            // Render children
            if (item.children && item.children.length > 0) {
                item.children.forEach(child => {
                    this.renderItemRow(child, $container, level + 1);
                });
            }
        }
        
        populateGroupSelect(groups) {
            const $select = $('#item-group');
            $select.find('option:not(:first)').remove();
            
            if (groups && groups.length > 0) {
                groups.forEach(group => {
                    $select.append(`<option value="${group.id}">${escapeHtml(group.name)}</option>`);
                });
            }
            
            $select.selectpicker('refresh');
        }

        populateParentItemsSelect(items) {
            const $select = $('#item-parent');
            $select.find('option:not(:first)').remove();
            
            if (items && items.length > 0) {
                items.forEach(item => {
                    if(!item.parent_id && !item.group_id)
                        $select.append(`<option value="${item.id}">${escapeHtml(item.label)}</option>`);
                });
            }
            
            $select.selectpicker('refresh');
        }
        
        showGroupModal(groupData = null) {
            $('#cfm-group-modal .modal-title').text(groupData ? 'Edit Group' : 'Add Group');
            $('#cfm-group-form')[0].reset();
            
            if (groupData) {
                $('#group-id').val(groupData.id);
                $('#group-name').val(groupData.name);
                $('#group-slug').val(groupData.slug);
                $('#group-sort-order').val(groupData.sort_order);
                $('#group-active').prop('checked', groupData.active == 1);
            } else {
                $('#group-id').val('');
            }
            
            $('#cfm-group-modal').modal('show');
        }
        
        showItemModal(itemData = null) {
            $('#cfm-item-modal .modal-title').text(itemData ? 'Edit Item' : 'Add Item');
            $('#cfm-item-form')[0].reset();
            
            if (itemData) {
                $('#item-id').val(itemData.id);
                $('#item-label').val(itemData.label);
                $('#item-url').val(itemData.url);
                $('#item-icon').val(itemData.icon);
                $('#item-group').val(itemData.group_id);
                $('#item-parent').val(itemData.parent_id);
                $('#item-badge-text').val(itemData.badge_text);
                $('#item-badge-color').val(itemData.badge_color_class);
                $('#item-permission').val(itemData.permission);
                $('#item-sort-order').val(itemData.sort_order);
                $('#item-active').prop('checked', itemData.active == 1);
            } else {
                $('#item-id').val('');
            }
            
            $('.selectpicker').selectpicker('refresh');
            $('#cfm-item-modal').modal('show');
        }
        
        saveGroup() {
            const formData = $('#cfm-group-form').serialize();
            const self = this;
            
            $.post(admin_url + 'custom_flyout_menu/save_group', formData, (response) => {
                if (response.success) {
                    alert_float('success', 'Group saved successfully');
                    $('#cfm-group-modal').modal('hide');
                    self.loadMenuData();
                } else {
                    alert_float('danger', response.error || 'Failed to save group');
                }
            }, 'json').fail(function(xhr) {
                if (xhr.status === 403) {
                    alert_float('danger', 'Session expired. Please refresh and try again.');
                    location.reload();
                } else {
                    alert_float('danger', 'An error occurred while saving the group.');
                }
            });
        }
        
        saveItem() {
            const formData = $('#cfm-item-form').serialize();
            const self = this;
            
            $.post(admin_url + 'custom_flyout_menu/save_item', formData, (response) => {
                if (response.success) {
                    alert_float('success', 'Menu item saved successfully');
                    $('#cfm-item-modal').modal('hide');
                    self.loadMenuData();
                } else {
                    alert_float('danger', response.error || 'Failed to save item');
                }
            }, 'json').fail(function(xhr) {
                if (xhr.status === 403) {
                    alert_float('danger', 'Session expired. Please refresh and try again.');
                    location.reload();
                } else {
                    alert_float('danger', 'An error occurred while saving the item.');
                }
            });
        }
        
        editGroup(id) {
            const self = this;
            $.get(admin_url + 'custom_flyout_menu/get_menu_structure', { area: this.area }, (data) => {
                const group = data.groups.find(g => g.id == id);
                if (group) {
                    self.showGroupModal(group);
                }
            }, 'json');
        }
        
        deleteGroup(id) {
            const self = this;
            if (confirm('Are you sure you want to delete this group?')) {
                $.post(admin_url + 'custom_flyout_menu/delete_group/' + id, (response) => {
                    if (response.success) {
                        alert_float('success', 'Group deleted successfully');
                        self.loadMenuData();
                    } else {
                        alert_float('danger', response.error || 'Failed to delete group');
                    }
                }, 'json').fail(function(xhr) {
                    if (xhr.status === 403) {
                        alert_float('danger', 'Session expired. Please refresh and try again.');
                        location.reload();
                    } else {
                        alert_float('danger', 'An error occurred while deleting the group.');
                    }
                });
            }
        }
        
        editItem(id) {
            const self = this;
            $.get(admin_url + 'custom_flyout_menu/get_menu_structure', { area: this.area }, (data) => {
                const item = self.findItemById(data.items, id);
                if (item) {
                    self.showItemModal(item);
                }
            }, 'json');
        }
        
        findItemById(items, id) {
            for (let item of items) {
                if (item.id == id) {
                    return item;
                }
                if (item.children) {
                    const found = this.findItemById(item.children, id);
                    if (found) return found;
                }
            }
            return null;
        }
        
        deleteItem(id) {
            const self = this;
            if (confirm('Are you sure you want to delete this menu item?')) {
                $.post(admin_url + 'custom_flyout_menu/delete_item/' + id, (response) => {
                    if (response.success) {
                        alert_float('success', 'Menu item deleted successfully');
                        self.loadMenuData();
                    } else {
                        alert_float('danger', response.error || 'Failed to delete item');
                    }
                }, 'json').fail(function(xhr) {
                    if (xhr.status === 403) {
                        alert_float('danger', 'Session expired. Please refresh and try again.');
                        location.reload();
                    } else {
                        alert_float('danger', 'An error occurred while deleting the item.');
                    }
                });
            }
        }
        
        initDataTables() {
            // Destroy existing DataTables if they exist
            if ($.fn.DataTable.isDataTable('.datatable')) {
                $('.datatable').DataTable().destroy();
            }
            
            // Initialize DataTables with client-side processing
            $('.datatable').DataTable({
                "order": [[0, "asc"]],
                "pageLength": 25,
                "language": {
                    "emptyTable": "No data available"
                }
            });
        }
    }
    
    // Helper function
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Initialize on page load
    $(document).ready(function() {
        const area = $('#cfm-builder-wrapper').data('area');
        if (area) {
            window.cfmMenuBuilder = new MenuBuilder(area);
        }
    });
    
})(jQuery);
