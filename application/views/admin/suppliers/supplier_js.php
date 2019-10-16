<?php
/**
 * Included in application/views/admin/suppliers/supplier.php
 */
?>
<script>
    Dropzone.options.clientAttachmentsUpload = false;
    var supplier_id = $('input[name="id"]').val();
    $(function () {

        if ($('#supplier-attachments-upload').length > 0) {
            new Dropzone('#supplier-attachments-upload', $.extend({}, _dropzone_defaults(), {
                paramName: "file",
                accept: function (file, done) {
                    done();
                },
                success: function (file, response) {
                    if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
                        window.location.reload();
                    }
                }
            }));
        }

        // Save button not hidden if passed from url ?tab= we need to re-click again
        if (tab_active) {
            $('body').find('.nav-tabs [href="#' + tab_active + '"]').click();
        }

        $('a[href="#supplier_admins"]').on('click', function () {
            $('.btn-bottom-toolbar').addClass('hide');
        });

        $('.profile-tabs a').not('a[href="#supplier_admins"]').on('click', function () {
            $('.btn-bottom-toolbar').removeClass('hide');
        });

        $("input[name='tasks_related_to[]']").on('change', function () {
            var tasks_related_values = []
            $('#tasks_related_filter :checkbox:checked').each(function (i) {
                tasks_related_values[i] = $(this).val();
            });
            $('input[name="tasks_related_to"]').val(tasks_related_values.join());
            $('.table-rel-tasks').DataTable().ajax.reload();
        });

        var contact_id = get_url_param('contactid');
        if (contact_id) {
            contact(supplier_id, contact_id);
        }

        // consents=CONTACT_ID
        var consents = get_url_param('consents');
        if (consents) {
            view_contact_consent(consents);
        }

        // If user clicked save and add new contact
        if (get_url_param('new_contact')) {
            contact(supplier_id);
        }

        $('body').on('change', '.onoffswitch input.supplier_file', function (event, state) {
            var invoker = $(this);
            var checked_visibility = invoker.prop('checked');
            var share_file_modal = $('#supplier_file_share_file_with');
            setTimeout(function () {
                $('input[name="file_id"]').val(invoker.attr('data-id'));
                if (checked_visibility && share_file_modal.attr('data-total-contacts') > 1) {
                    share_file_modal.modal('show');
                } else {
                    do_share_file_contacts();
                }
            }, 200);
        });

        $('.supplier-form-submiter').on('click', function () {
            var form = $('.supplier-form');
            if (form.valid()) {
                if ($(this).hasClass('save-and-add-contact')) {
                    form.find('.additional').html(hidden_input('save_and_add_contact', 'true'));
                } else {
                    form.find('.additional').html('');
                }
                form.submit();
            }
        });

        if (typeof (Dropbox) != 'undefined' && $('#dropbox-chooser').length > 0) {
            document.getElementById("dropbox-chooser").appendChild(Dropbox.createChooseButton({
                success: function (files) {
                    $.post(admin_url + 'suppliers/add_external_attachment', {
                        files: files,
                        supplierid: supplier_id,
                        external: 'dropbox'
                    }).done(function () {
                        window.location.reload();
                    });
                },
                linkType: "preview",
                extensions: app_allowed_files.split(','),
            }));
        }

        /* Customer profile tickets table */
        $('.table-tickets-single').find('#th-submitter').removeClass('toggleable');

        initDataTable('.table-tickets-single', admin_url + 'tickets/index/false/' + supplier_id, undefined, undefined, 'undefined', [$('table thead .ticket_created_column').index(), 'desc']);

        /* Customer profile contracts table */
        initDataTable('.table-contracts-single-client', admin_url + 'contracts/table/' + supplier_id, undefined, undefined, 'undefined', [6, 'desc']);

        /* Custome profile contacts table */
        var contactsNotSortable = [];
        <?php if(is_gdpr() && get_option('gdpr_enable_consent_for_contacts') == '1'){ ?>
        contactsNotSortable.push($('#th-consent').index());
        <?php } ?>
        _table_api = initDataTable('.table-contacts', admin_url + 'suppliers/contacts/' + supplier_id, contactsNotSortable, contactsNotSortable);
        if (_table_api) {
            <?php if(is_gdpr() && get_option('gdpr_enable_consent_for_contacts') == '1'){ ?>
            _table_api.on('draw', function () {
                var tableData = $('.table-contacts').find('tbody tr');
                $.each(tableData, function () {
                    $(this).find('td:eq(1)').addClass('bg-light-gray');
                });
            });
            <?php } ?>
        }
        /* Customer profile invoices table */
        initDataTable('.table-invoices-single-client',
            admin_url + 'invoices/table/' + supplier_id,
            'undefined',
            'undefined',
            'undefined', [
                [3, 'desc'],
                [0, 'desc']
            ]);

        initDataTable('.table-credit-notes', admin_url + 'credit_notes/table/' + supplier_id, ['undefined'], ['undefined'], undefined, [0, 'desc']);

        /* Customer profile Estimates table */
        initDataTable('.table-estimates-single-client',
            admin_url + 'estimates/table/' + supplier_id,
            'undefined',
            'undefined',
            'undefined', [
                [3, 'desc'],
                [0, 'desc']
            ]);

        /* Customer profile payments table */
        initDataTable('.table-payments-single-client',
            admin_url + 'payments/table/' + supplier_id, undefined, undefined,
            'undefined', [0, 'desc']);

        /* Customer profile reminders table */
        initDataTable('.table-reminders', admin_url + 'misc/get_reminders/' + supplier_id + '/' + 'customer', undefined, undefined, undefined, [1, 'asc']);

        /* Customer profile expenses table */
        initDataTable('.table-expenses-single-client',
            admin_url + 'expenses/table/' + supplier_id,
            'undefined',
            'undefined',
            'undefined', [5, 'desc']);

        /* Customer profile proposals table */
        initDataTable('.table-proposals-client-profile',
            admin_url + 'proposals/proposal_relations/' + supplier_id + '/customer',
            'undefined',
            'undefined',
            'undefined', [6, 'desc']);

        /* Custome profile projects table */
        initDataTable('.table-projects-single-client', admin_url + 'projects/table/' + supplier_id, undefined, undefined, 'undefined', <?php echo do_action('projects_table_default_order', json_encode(array(5, 'asc'))); ?>);

        var vRules = {};
        if (app_company_is_required == 1) {
            vRules = {
                company: 'required',
            }
        }
        _validate_form($('.supplier-form'), vRules);

        $('.billing-same-as-supplier').on('click', function (e) {
            e.preventDefault();
            $('textarea[name="billing_street"]').val($('textarea[name="address"]').val());
            $('input[name="billing_city"]').val($('input[name="city"]').val());
            $('input[name="billing_state"]').val($('input[name="state"]').val());
            $('input[name="billing_zip"]').val($('input[name="zip"]').val());
            $('select[name="billing_country"]').selectpicker('val', $('select[name="country"]').selectpicker('val'));
        });

        $('.supplier-copy-billing-address').on('click', function (e) {
            e.preventDefault();
            $('textarea[name="shipping_street"]').val($('textarea[name="billing_street"]').val());
            $('input[name="shipping_city"]').val($('input[name="billing_city"]').val());
            $('input[name="shipping_state"]').val($('input[name="billing_state"]').val());
            $('input[name="shipping_zip"]').val($('input[name="billing_zip"]').val());
            $('select[name="shipping_country"]').selectpicker('val', $('select[name="billing_country"]').selectpicker('val'));
        });

        $('body').on('hidden.bs.modal', '#contact', function () {
            $('#contact_data').empty();
        });

        $('.supplier-form').on('submit', function () {
            $('select[name="default_currency"]').prop('disabled', false);
        });

    });

    function delete_contact_profile_image(contact_id) {
        requestGet('suppliers/delete_contact_profile_image/' + contact_id).done(function () {
            $('body').find('#contact-profile-image').removeClass('hide');
            $('body').find('#contact-remove-img').addClass('hide');
            $('body').find('#contact-img').attr('src', '<?php echo base_url('assets/images/user-placeholder.jpg'); ?>');
        });
    }

    function validate_contact_form() {
        _validate_form('#contact-form', {
            firstname: 'required',
            lastname: 'required',
            password: {
                required: {
                    depends: function (element) {
                        var sent_set_password = $('input[name="send_set_password_email"]');
                        if ($('#contact input[name="contactid"]').val() == '' && sent_set_password.prop('checked') == false) {
                            return true;
                        }
                    }
                }
            },
            email: {
                <?php if(do_action('contact_email_required', "true") === "true"){ ?>
                required: true,
                <?php } ?>
                email: true,
                // Use this hook only if the contacts are not logging into the customers area and you are not using support tickets piping.
                <?php if(do_action('contact_email_unique', "true") === "true"){ ?>
                remote: {
                    url: admin_url + "misc/supplier_contact_email_exists",
                    type: 'post',
                    data: {
                        email: function () {
                            return $('#contact input[name="email"]').val();
                        },
                        id: function () {
                            return $('body').find('input[name="contactid"]').val();
                        }
                    }
                }
                <?php } ?>
            }
        }, contactFormHandler);
    }

    function contactFormHandler(form) {
        $('#contact input[name="is_primary"]').prop('disabled', false);

        $("#contact input[type=file]").each(function () {
            if ($(this).val() === "") {
                $(this).prop('disabled', true);
            }
        });

        var formURL = $(form).attr("action");
        var formData = new FormData($(form)[0]);

        $.ajax({
            type: 'POST',
            data: formData,
            mimeType: "multipart/form-data",
            contentType: false,
            cache: false,
            processData: false,
            url: formURL
        }).done(function (response) {
            response = JSON.parse(response);
            if (response.success) {
                alert_float('success', response.message);
                if (typeof (response.is_individual) != 'undefined' && response.is_individual) {
                    $('.new-contact').addClass('disabled');
                    if (!$('.new-contact-wrapper')[0].hasAttribute('data-toggle')) {
                        $('.new-contact-wrapper').attr('data-toggle', 'tooltip');
                    }
                }
            }
            if ($.fn.DataTable.isDataTable('.table-contacts')) {
                $('.table-contacts').DataTable().ajax.reload(null, false);
            }
            if ($.fn.DataTable.isDataTable('.table-all-contacts')) {
                $('.table-all-contacts').DataTable().ajax.reload(null, false);
            }
            if ($.fn.DataTable.isDataTable('.table-opportunity-contacts')) {
                $('.table-opportunity-contacts').DataTable().ajax.reload(null, false);
            }
            if (response.proposal_warning && response.proposal_warning != false) {
                $('body').find('#contact_proposal_warning').removeClass('hide');
                $('body').find('#contact_update_proposals_emails').attr('data-original-email', response.original_email);
                $('#contact').animate({
                    scrollTop: 0
                }, 800);
            } else {
                $('#contact').modal('hide');
            }
        }).fail(function (error) {
            alert_float('danger', JSON.parse(error.responseText));
        });
        return false;
    }

    function contact(supplier_id, contact_id) {
        if (typeof (contact_id) == 'undefined') {
            contact_id = '';
        }
        requestGet('suppliers/contact/' + supplier_id + '/' + contact_id).done(function (response) {
            $('#contact_data').html(response);
            $('#contact').modal({
                show: true,
                backdrop: 'static'
            });
            $('body').off('shown.bs.modal', '#contact');
            $('body').on('shown.bs.modal', '#contact', function () {
                if (contact_id == '') {
                    $('#contact').find('input[name="firstname"]').focus();
                }
            });
            init_selectpicker();
            init_datepicker();
            custom_fields_hyperlink();
            validate_contact_form();
        }).fail(function (error) {
            var response = JSON.parse(error.responseText);
            alert_float('danger', response.message);
        });
    }

    function update_all_proposal_emails_linked_to_contact(contact_id) {
        var data = {};
        data.update = true;
        data.original_email = $('body').find('#contact_update_proposals_emails').data('original-email');
        $.post(admin_url + 'suppliers/update_all_proposal_emails_linked_to_customer/' + contact_id, data).done(function (response) {
            response = JSON.parse(response);
            if (response.success) {
                alert_float('success', response.message);
            }
            $('#contact').modal('hide');
        });
    }

    function do_share_file_contacts(edit_contacts, file_id) {
        var contacts_shared_ids = $('select[name="share_contacts_id[]"]');
        if (typeof (edit_contacts) == 'undefined' && typeof (file_id) == 'undefined') {
            var contacts_shared_ids_selected = $('select[name="share_contacts_id[]"]').val();
        } else {
            var _temp = edit_contacts.toString().split(',');
            for (var cshare_id in _temp) {
                contacts_shared_ids.find('option[value="' + _temp[cshare_id] + '"]').attr('selected', true);
            }
            contacts_shared_ids.selectpicker('refresh');
            $('input[name="file_id"]').val(file_id);
            $('#customer_file_share_file_with').modal('show');
            return;
        }
        var file_id = $('input[name="file_id"]').val();
        $.post(admin_url + 'suppliers/update_file_share_visibility', {
            file_id: file_id,
            share_contacts_id: contacts_shared_ids_selected,
            supplier_id: $('input[name="id"]').val()
        }).done(function () {
            window.location.reload();
        });
    }

    function save_longitude_and_latitude(clientid) {
        var data = {};
        data.latitude = $('#latitude').val();
        data.longitude = $('#longitude').val();
        $.post(admin_url + 'suppliers/save_longitude_and_latitude/' + clientid, data).done(function (response) {
            if (response == 'success') {
                alert_float('success', "<?php echo _l('updated_successfully', _l('client')); ?>");
            }
            setTimeout(function () {
                window.location.reload();
            }, 1200);
        }).fail(function (error) {
            alert_float('danger', error.responseText);
        });
    }

    function fetch_lat_long_from_google_cprofile() {
        var data = {};
        data.address = $('#long_lat_wrapper').data('address');
        data.city = $('#long_lat_wrapper').data('city');
        data.country = $('#long_lat_wrapper').data('country');
        $('#gmaps-search-icon').removeClass('fa-google').addClass('fa-spinner fa-spin');
        $.post(admin_url + 'misc/supplier_fetch_address_info_gmaps', data).done(function (data) {
            data = JSON.parse(data);
            $('#gmaps-search-icon').removeClass('fa-spinner fa-spin').addClass('fa-google');
            if (data.response.status == 'OK') {
                $('input[name="latitude"]').val(data.lat);
                $('input[name="longitude"]').val(data.lng);
            } else {
                if (data.response.status == 'ZERO_RESULTS') {
                    alert_float('warning', "<?php echo _l('g_search_address_not_found'); ?>");
                } else {
                    alert_float('danger', data.response.status);
                }
            }
        });
    }
</script>
