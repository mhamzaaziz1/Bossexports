console.log("JS LOADED");

function add_warranty_template() {
    $("#modal-wrapper").load(admin_url + "warranty_management/warranty_management/modal",
        {
            slug: "new",
        },

        function () {
            if ($("#TemplateModal").is(":hidden")) {
                $("#TemplateModal").modal({
                    backdrop: "static",
                    show: true,
                });
            }

            appValidateForm($("#template-form"), {
                name: "required",
            });

            tinymce.remove("#content");

            init_editor("#content", {
                pagebreak_split_block: true,
                contextmenu:
                    "link image inserttable | cell row column deletetable | paste copy | pagebreak",
                append_plugins: "pagebreak",
            });
        }
    );
}


function delete_warranty_template(btn, id) {
    if (confirm_delete()) {
        $.post(admin_url + "warranty_management/delete/" + id).done(function (response) {
            response = JSON.parse(response);

            if (response.success) {
                $(btn).closest('.media').fadeOut(300, function () {
                    $(this).remove();
                });
            }
        });
    }
}


function edit_warranty_template(id) {
    // console.log("click");
    // console.log('EDIT ID:', id);

    $("#modal-wrapper").load(admin_url + "warranty_management/warranty_management/modal",
        {
            slug: "edit",
            id: id,
        },

        function () {
            if ($("#TemplateModal").is(":hidden")) {
                $("#TemplateModal").modal({
                    backdrop: "static",
                    show: true,
                });
            }

            appValidateForm($("#template-form"), {
                name: "required",
            });

            tinymce.remove("#content");

            init_editor("#content", {
                pagebreak_split_block: true,
                contextmenu:
                    "link image inserttable | cell row column deletetable | paste copy | pagebreak",
                append_plugins: "pagebreak",
            });
        }
    );
}

function insert_warranty_template(template_id, warranty_id)
{
    $.post(
        admin_url + 'warranty_management/apply_template',
        {
            template_id: template_id,
            warranty_id: warranty_id
        }
    ).done(function (response) {

        response = JSON.parse(response);

        if (response.success) {
            window.location.href = response.redirect_url;
        } else {
            alert(response.message);
        }
    });
}





