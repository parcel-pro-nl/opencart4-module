<?php
//==============================================================================
// Parcel Pro Shipping
//
// Company: Parcel Pro
// Contact: info@parcelpro.nl
//==============================================================================

$version = '1.1.0';

// Heading
$_['heading_title'] = 'Parcel Pro';

//Pick up
$_['pick-up_point']    = 'Pick-up point';

// Text
$_['text_list']    = 'Parcel Pro order submit';
$_['text_view']    = 'View';
$_['text_show_label']    = 'Label';
$_['text_show_tracking'] = 'Tracking';
$_['text_submit_su']     = 'Submit PP';
$_['text_count_right']   = 'order(s) successfully submitted: ';
$_['text_already_submitted']   = 'Order already submitted: ';
$_['text_missing_data']   = 'Order is missing details: ';
$_['text_count_wrong']   = 'order(s) not send due problems: ';
$_['text_type_id_3085']     = 'PostNL, Standaard pakket';
$_['text_type_id_3086']     = 'PostNL, Pakket + rembours';
$_['text_type_id_3087']     = 'PostNL, Pakket + verzekerd bedrag';
$_['text_type_id_3089']     = 'PostNL, Pakket + handtekening voor ontvangst';
$_['text_type_id_3091']   = 'PostNL, Rembours + Verhoogd aansprakelijk';
$_['text_type_id_3189']     = 'PostNL, Pakket + handtekening voor ontvangst, ook bij buren';
$_['text_type_id_3385']   = 'PostNL, Alleen Huisadres';
$_['text_type_id_4940']     = 'PostNL, Pakket buitenland';
$_['text_type_id_3533']     = 'PostNL, Pakjegemak';
$_['text_type_id_DFY']    = 'DHL, DFY';
$_['text_type_id_Europlus']    = 'DHL, EUROPLUS';
$_['text_type_id_DFYParcelshop']    = 'DHL, ParcelShop';
$_['text_type_id_101']    = 'DPD, Normaal pakket';
$_['text_type_id_109']    = 'DPD, Rembours';
$_['text_type_id_136']    = 'DPD, Klein pakket';
$_['text_type_id_161']    = 'DPD, Verzekerd';
$_['text_type_id_179']    = 'DPD, DPD 10:00';
$_['text_type_id_191']    = 'DPD, DPD 10:00, rembours';
$_['text_type_id_225']    = 'DPD, DPD 12:00';
$_['text_type_id_228']    = 'DPD, DPD 12:00, zaterdag';
$_['text_type_id_237']    = 'DPD, DPD 12:00, rembours';
$_['text_type_id_350']    = 'DPD, DPD 8:30';
$_['text_type_id_352']    = 'DPD, 8:30, rembours';
$_['text_copyright']     = '<div style="text-align: center" class="help">' . $_['heading_title'] . ' ' . $version . ' &copy; ' . date("Y") . '</div>';

// Column
$_['button_submit_su']   = 'Submit to Parcel Pro';
$_['button_label']       = 'Print Labels';
$_['button_update']      = 'Update';

// Column
$_['column_order_id']            = 'Order ID';
$_['column_su_order_id']      = 'SU Order ID';
$_['column_customer']            = 'Customer';
$_['column_status']              = 'Status';
$_['column_order_date_added'] = 'Date Order Added';
$_['column_su_date_added']    = 'Submit date';
$_['column_total']               = 'Total';
$_['column_product']             = 'Product';
$_['column_model']               = 'Model';
$_['column_quantity']            = 'Quantity';
$_['column_price']               = 'Unit Price';
$_['column_comment']             = 'Comment';
$_['column_notify']              = 'Customer Notified';
$_['column_shipping_method']  = 'Shipping method';
$_['column_barcode']          = 'Barcode';
$_['column_colli']            = 'Colli #';
$_['column_location']            = 'Location';
$_['column_reference']           = 'Reference';
$_['column_action']              = 'Action';
$_['column_weight']           = 'Weight (kg)';

// Error
$_['error_warning']                   = 'Warning: Please check the form carefully for errors!';
$_['error_permission']                = 'Warning: You do not have permission to modify SU orders!';
$_['error_no_submit_orders_selected'] = 'Warning: No orders selected for batch submit!';
$_['error_nothing_selected']          = 'Warning: No orders selected!';
$_['error_action']                    = 'Warning: This action is not possible!';

// Entry
$_['entry_store']                = 'Store';
$_['entry_customer']             = 'Customer';
$_['entry_customer_group']       = 'Customer Group';
$_['entry_firstname']            = 'First Name';
$_['entry_lastname']             = 'Last Name';
$_['entry_email']                = 'E-Mail';
$_['entry_telephone']            = 'Telephone';
$_['entry_fax']                  = 'Fax';
$_['entry_address']              = 'Choose Address';
$_['entry_company']              = 'Company';
$_['entry_address_1']            = 'Address 1';
$_['entry_address_2']            = 'Address 2';
$_['entry_city']                 = 'City';
$_['entry_postcode']             = 'Postcode';
$_['entry_country']              = 'Country';
$_['entry_zone']                 = 'Region / State';
$_['entry_zone_code']            = 'Region / State Code';
$_['entry_product']              = 'Choose Product';
$_['entry_option']               = 'Choose Option(s)';
$_['entry_quantity']             = 'Quantity';
$_['entry_to_name']              = 'Recipient\'s Name';
$_['entry_to_email']             = 'Recipient\'s Email';
$_['entry_from_name']            = 'Senders Name';
$_['entry_from_email']           = 'Senders Email';
$_['entry_theme']                = 'Gift Certificate Theme';
$_['entry_message']              = 'Message';
$_['entry_amount']               = 'Amount';
$_['entry_affiliate']            = 'Affiliate';
$_['entry_order_status']         = 'Order Status';
$_['entry_notify']               = 'Notify Customer';
$_['entry_override']             = 'Override';
$_['entry_comment']              = 'Comment';
$_['entry_currency']             = 'Currency';
$_['entry_shipping_method']      = 'Shipping Method';
$_['entry_payment_method']       = 'Payment Method';
$_['entry_coupon']               = 'Coupon';
$_['entry_voucher']              = 'Voucher';
$_['entry_reward']               = 'Reward';
$_['entry_order_id']             = 'Order ID';
$_['entry_total']                = 'Total';
$_['entry_date_added']           = 'Date Added';
$_['entry_date_modified']        = 'Date Modified';
