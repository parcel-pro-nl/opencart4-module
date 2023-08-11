<?php

namespace Opencart\Admin\Controller\Extension\ParcelPro\Shipping;

use Opencart\System\Engine\Controller;
use Opencart\System\Engine\Registry;
use Opencart\System\Library\ParcelPro as ParcelProInstance;

class parcelpro extends Controller {

    private $error = array();

    public function __construct(Registry $registry)
    {
        parent::__construct($registry);
        require_once DIR_EXTENSION . 'parcelpro/system/library/ParcelPro.php';
    }

    public function install(): void
    {
        $this->load->model("setting/event");

        $this->model_setting_event->addEvent([
            'code' => 'parcelpro_add_pickup_points',
            'description' => '',
            'trigger' => 'catalog/view/checkout/shipping_method/before',
            'action' => 'extension/parcelpro/shipping/parcelpro.addPickupPoints',
            'status' => 1,
            'sort_order' => 1
        ]);

        $this->model_setting_event->addEvent([
            'code' => 'parcelpro_save_pickup_point_to_database',
            'description' => '',
            'trigger' => 'catalog/controller/checkout/success/before',
            'action' => 'extension/parcelpro/shipping/parcelpro.savePickupPointToDatabase',
            'status' => 1,
            'sort_order' => 1
        ]);
    }

    public function uninstall()
    {
        $this->load->model('setting/event');
        $this->model_setting_event->deleteEventByCode('parcelpro_add_pickup_points');
        $this->model_setting_event->deleteEventByCode('parcelpro_save_pickup_point_to_database');
    }

    public function index() {
        $this->load->language('extension/parcelpro/shipping/parcel_pro');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('shipping_parcel_pro', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('extension/parcelpro/shipping/parcel_pro', 'user_token=' . $this->session->data['user_token']));
        }
        
        if(isset($this->session->data['success'])){
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }

        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_version'] = $this->language->get('text_version');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_all_zones'] = $this->language->get('text_all_zones');
        $data['text_none'] = $this->language->get('text_none');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');
        $data['text_select_all'] = $this->language->get('text_select_all');
        $data['text_unselect_all'] = $this->language->get('text_unselect_all');
        $data['text_min'] = $this->language->get('text_min');
        $data['text_max'] = $this->language->get('text_max');
        $data['text_status'] = $this->language->get('text_status');
        $data['text_name'] = $this->language->get('text_name');
        $data['text_shipping_price'] = $this->language->get('text_shipping_price');
        $data['text_copyright'] = $this->language->get('text_copyright');

        $data['entry_version'] = $this->language->get('entry_version');
        $data['entry_user_id'] = $this->language->get('entry_user_id');
        $data['entry_api_key'] = $this->language->get('entry_api_key');
        $data['entry_sender_name'] = $this->language->get('entry_sender_name');
        $data['entry_sender_street'] = $this->language->get('entry_sender_street');
        $data['entry_sender_number'] = $this->language->get('entry_sender_number');
        $data['entry_sender_postcode'] = $this->language->get('entry_sender_postcode');
        $data['entry_sender_city'] = $this->language->get('entry_sender_city');
        $data['entry_sender_country'] = $this->language->get('entry_sender_country');
        $data['entry_sender_iban'] = $this->language->get('entry_sender_iban');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_auto_export_status'] = $this->language->get('entry_auto_export_status');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');
        $data['entry_order_status'] = $this->language->get('entry_order_status');
        $data['entry_sub_total'] = $this->language->get('entry_sub_total');
        $data['entry_weight'] = $this->language->get('entry_weight');
        $data['entry_shipping_heading'] = $this->language->get('entry_shipping_heading');
        $data['entry_tax_class'] = $this->language->get('entry_tax_class');
        $data['entry_handtekening'] = $this->language->get('entry_handtekening');
        $data['entry_avond'] = $this->language->get('entry_avond');
        $data['entry_extrazeker'] = $this->language->get('entry_extrazeker');
        $data['entry_nietbijburen'] = $this->language->get('entry_nietbijburen');

        $data['button_add_rule'] = $this->language->get('button_add_rule');
        $data['button_remove'] = $this->language->get('button_remove');
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        $options = array();
        $instance = new ParcelProInstance($this->registry);
        $dictionary_types = $instance->get_types_filtered();
        $options = $instance->push_types_filtered_primitive($options);


        $data['tab_general'] = $this->language->get('tab_general');


        for ($i = 0; $i < count($options); $i++) {
            if(! isset($data["shipmentOptions"]['tab_type_id_'.strval($options[$i])])){
                if($this->language->get('tab_type_id_'.$options[$i]) === 'tab_type_id_'.$options[$i]){
                    $data["shipmentOptions"]['tab_type_id_'.strval($options[$i])] =  $dictionary_types[strval($options[$i])];
                }else{
                    $data["shipmentOptions"]['tab_type_id_'.strval($options[$i])] = $this->language->get('tab_type_id_'.strval($options[$i]));
                };
            }
        }

        $data['column_general'] = $this->language->get('column_general');
        $data['column_geo_zone'] = $this->language->get('column_geo_zone');
        $data['column_cart_value'] = $this->language->get('column_cart_value');
        $data['column_options'] = $this->language->get('column_options');
        $data['column_pricing'] = $this->language->get('column_pricing');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        // Load Order Statuses
        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/shipping/flat', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/parcelpro/shipping/parcel_pro', 'user_token=' . $this->session->data['user_token']);

        $data['cancel'] = $this->url->link('marketplace/extension&type=shipping', 'user_token=' . $this->session->data['user_token'], 'SSL');

        // General
        if (isset($this->request->post['shipping_parcel_pro_status'])) {
            $data['shipping_parcel_pro_status'] = $this->request->post['shipping_parcel_pro_status'];
        } else {
            $data['shipping_parcel_pro_status'] = $this->config->get('shipping_parcel_pro_status');
        }



        if (isset($this->request->post['parcel_pro_auto_export_status'])) {
            $data['shipping_parcel_pro_auto_export_status'] = $this->request->post['shipping_parcel_pro_auto_export_status'];
        } else {
            $data['shipping_parcel_pro_auto_export_status'] = $this->config->get('shipping_parcel_pro_auto_export_status');
        }

        $this->load->model('localisation/language');
        $languages = $this->model_localisation_language->getLanguages();


        foreach ($languages as $language) {
            if (isset($this->request->post['shipping_parcel_pro_heading'][$language['code']])) {

                $data['shipping_parcel_pro_heading'][$language['code']] = $this->request->post['shipping_parcel_pro_heading'][$language['code']];
            } else {
                $data['shipping_parcel_pro_heading'][$language['code']] = $this->config->get('shipping_parcel_pro_heading')[$language['code']] ?? null;
            }
        }

        if (isset($this->request->post['shipping_parcel_pro_sort_order'])) {
            $data['shipping_parcel_pro_sort_order'] = $this->request->post['shipping_parcel_pro_sort_order'];
        } else {
            $data['shipping_parcel_pro_sort_order'] = $this->config->get('shipping_parcel_pro_sort_order');
        }


        $data['shipping_parcel_pro_loaded_types']=urlencode(json_encode($dictionary_types));

        for ($i = 0; $i < count($options); $i++) {

            if (isset($this->request->post['shipping_parcel_pro_type_id_'.$options[$i].'_status'])) {
                $data['config'][$options[$i]]['status'] = $this->request->post['shipping_parcel_pro_type_id_'.$options[$i].'_status'];
            } else {
                $data['config'][$options[$i]]['status'] = $this->config->get('shipping_parcel_pro_type_id_'.$options[$i].'_status');
            }

            if (isset($this->request->post['shipping_parcel_pro_type_id_'.$options[$i].'_tax_class_id'])) {
                $data['config'][$options[$i]]['tax_class_id'] = $this->request->post['shipping_parcel_pro_type_id_'.$options[$i].'_tax_class_id'];
            } else {
                $data['config'][$options[$i]]['tax_class_id'] = $this->config->get('shipping_parcel_pro_type_id_'.$options[$i].'_tax_class_id');
            }

            if (isset($this->request->post['shipping_parcel_pro_type_id_'.$options[$i].'_sort_order'])) {
                $data['config'][$options[$i]]['sort_order'] = $this->request->post['shipping_parcel_pro_type_id_'.$options[$i].'_sort_order'];
            } else {
                $data['config'][$options[$i]]['sort_order'] = $this->config->get('shipping_parcel_pro_type_id_'.$options[$i].'_sort_order');
            }

            $rules = ($this->config->get('shipping_parcel_pro_type_id_'.$options[$i].'_rule') ? $this->config->get('shipping_parcel_pro_type_id_'.$options[$i].'_rule') : array());

            $row_count = 0;
            $rows = '';
            foreach ($rules as $rule) {
                if($options[$i] === 'DFY' || $options[$i] === '00'){
                    $rows .= $this->addRule_2($options[$i], $rule, $row_count);
                }else{
                    $rows .= $this->addRule_1($options[$i], $rule, $row_count);
                }
                $row_count++;
            }

            $data['config'][$options[$i]]['rows'] = $rows;
            $data['config'][$options[$i]]['count'] = $row_count;
        }

        $this->load->model('localisation/tax_class');
        $data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

        $this->load->model('localisation/geo_zone');
        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $data['text_edit'] = $this->language->get('text_edit');

        $this->response->setOutput($this->load->view('extension/parcelpro/shipping/parcelpro', $data));
    }


    public function get_Types_From_API(){

    }

    public function Get_Type_Identifier($Code,$ExtraCode,$Type){

    }


    public function addRule_1($type_id, $rule = array(), $rule_counter = 0) {
        $this->language->load('shipping/parcel_pro');

        $this->load->model('localisation/tax_class');
        $tax_classes = $this->model_localisation_tax_class->getTaxClasses();

        $this->load->model('localisation/geo_zone');
        $geo_zones = $this->model_localisation_geo_zone->getGeoZones();

        $this->load->model('localisation/language');
        $languages = $this->model_localisation_language->getLanguages();

        $result = '<tbody id="shipping_parcel_pro_type_id_' . $type_id . '_rule' . $rule_counter . '">';
        $result .= '  <tr>';
        $result .= '    <td class="text-center">';
        $result .= '      <div><strong>' . $this->language->get('text_name') . '</strong></div>';
        foreach ($languages as $language) {
            $result .= '      <img src="language/' . $language['code'] . '/' . $language['code'] . '.png" title="' . $language['name'] . '" /> <input type="text" name="shipping_parcel_pro_type_id_' . $type_id . '_rule[' . $rule_counter . '][name][' . $language['code'] . ']" value="' . $rule['name'][$language['code']] . '" size="25" class="form-control" style="width: 25%; padding:5px; display: inline-block; height: 30px;" /><br />';
        }
        $result .= '      <div class="spacer_1"><strong>' . $this->language->get('text_status') . '</strong></div>';
        $result .= '      <select name="shipping_parcel_pro_type_id_' . $type_id . '_rule[' . $rule_counter . '][status]">';
        if ($rule['status'] == '1') {
            $result .= '        <option value="1" selected="selected">' . $this->language->get('text_enabled') . '</option>';
            $result .= '        <option value="0">' . $this->language->get('text_disabled') . '</option>';
        } else {
            $result .= '        <option value="1" >' . $this->language->get('text_enabled') . '</option>';
            $result .= '        <option value="0" selected="selected">' . $this->language->get('text_disabled') . '</option>';
        }
        $result .= '      </select>';
        $result .= '    </td>';
        $result .= '    <td class="center">';
        $result .= '      <div class="well well-sm" style="height: 100px; overflow: auto;">';
        if (!isset($rule['geo_zones'])) {
            $rule['geo_zones'] = array();
        }
        $class = 'even';
        foreach ($geo_zones as $geo_zone) {
            $result .= '        <div class="checkbox" style="padding: 0; min-height: auto;">';
            $result .= '<label>';
            if (in_array($geo_zone['geo_zone_id'], $rule['geo_zones'])) {
                $result .= '          <input type="checkbox" name="shipping_parcel_pro_type_id_' . $type_id . '_rule[' . $rule_counter . '][geo_zones][]" value="' . $geo_zone['geo_zone_id'] . '" checked="checked" />';
            } else {
                $result .= '          <input type="checkbox" name="shipping_parcel_pro_type_id_' . $type_id . '_rule[' . $rule_counter . '][geo_zones][]" value="' . $geo_zone['geo_zone_id'] . '" />';
            }
            $result .= '</label>';
            $result .= str_replace("'", "&#39;", $geo_zone['name']);
            $result .= '        </div>';
        }
        $result .= '      </div>';
        $result .= '      <a onclick="$(this).parent().find(\':checkbox\').prop(\'checked\', true);">Select All</a> / <a onclick="$(this).parent().find(\':checkbox\').prop(\'checked\', false);">Unselect All</a>';
        $result .= '    </td>';
        $result .= '    <td class="center">';
        $result .= '      <table style="margin: 0 auto;">';
        $result .= '        <thead>';
        $result .= '          <tr>';
        $result .= '            <td class="left">&nbsp;</td>';
        $result .= '            <td class="center">' . $this->language->get('text_min') . '</td>';
        $result .= '            <td class="center">' . $this->language->get('text_max') . '</td>';
        $result .= '          </tr>';
        $result .= '        </thead>';
        $result .= '        <tbody>';
        $result .= '          <tr>';
        $result .= '            <td class="left">' . $this->language->get('entry_sub_total') . '</td>';
        $result .= '            <td class="center"><input type="text" name="shipping_parcel_pro_type_id_' . $type_id . '_rule[' . $rule_counter . '][total_min]" value="' . $rule['total_min'] . '" size="5" class="form-control" style="width: 75%; padding:5px; display: inline-block; height: 30px;" /></td>';
        $result .= '            <td class="center"><input type="text" name="shipping_parcel_pro_type_id_' . $type_id . '_rule[' . $rule_counter . '][total_max]" value="' . $rule['total_max'] . '" size="5" class="form-control" style="width: 75%; padding:5px; display: inline-block; height: 30px;" /></td>';
        $result .= '          </tr>';
        $result .= '          <tr>';
        $result .= '            <td class="left">' . $this->language->get('entry_weight') . '</td>';
        $result .= '            <td class="center"><input type="text" name="shipping_parcel_pro_type_id_' . $type_id . '_rule[' . $rule_counter . '][weight_min]" value="' . $rule['weight_min'] . '" size="5" class="form-control" style="width: 75%; padding:5px; display: inline-block; height: 30px;" /></td>';
        $result .= '            <td class="center"><input type="text" name="shipping_parcel_pro_type_id_' . $type_id . '_rule[' . $rule_counter . '][weight_max]" value="' . $rule['weight_max'] . '" size="5" class="form-control" style="width: 75%; padding:5px; display: inline-block; height: 30px;" /></td>';
        $result .= '          <tr>';
        $result .= '        </tbody>';
        $result .= '      </table>';
        $result .= '    </td>';
        $result .= '    <td class="center">';
        $result .= '      <div><strong>' . $this->language->get('text_shipping_price') . '</strong></div>';
        $result .= '      <div><input type="text" name="shipping_parcel_pro_type_id_' . $type_id . '_rule[' . $rule_counter . '][cost]" value="' . $rule['cost'] . '" size="5" class="form-control" style="width: 50%; padding:5px; display: inline-block; height: 30px;" /></div>';
        $result .= '    </td>';
        $result .= '    <td class="left"><a onclick="$(\'#shipping_parcel_pro_type_id_' . $type_id . '_rule' . $rule_counter . '\').remove();" class="btn btn-primary">' . $this->language->get('button_remove') . '</a></td>';
        $result .= '  </tr>';
        $result .= '</tbody>';

        return $result;
    }

    public function addRule_2($type_id, $rule = array(), $rule_counter = 0) {
        $this->language->load('shipping/parcel_pro');

        $this->load->model('localisation/tax_class');
        $tax_classes = $this->model_localisation_tax_class->getTaxClasses();

        $this->load->model('localisation/geo_zone');
        $geo_zones = $this->model_localisation_geo_zone->getGeoZones();

        $this->load->model('localisation/language');
        $languages = $this->model_localisation_language->getLanguages();

        $result = '<tbody id="shipping_parcel_pro_type_id_' . $type_id . '_rule' . $rule_counter . '">';
        $result .= '  <tr>';
        $result .= '    <td class="text-center">';
        $result .= '      <div><strong>' . $this->language->get('text_name') . '</strong></div>';
        foreach ($languages as $language) {
            $result .= '      <img src="language/' . $language['code'] . '/' . $language['code'] . '.png" /> <input type="text" name="shipping_parcel_pro_type_id_' . $type_id . '_rule[' . $rule_counter . '][name][' . $language['code'] . ']" value="' . $rule['name'][$language['code']] . '" size="25" class="form-control" style="width: 40%; padding:5px; display: inline-block; height: 30px;"/><br />';
        }
        $result .= '      <div class="spacer_1"><strong>' . $this->language->get('text_status') . '</strong></div>';
        $result .= '      <select name="shipping_parcel_pro_type_id_' . $type_id . '_rule[' . $rule_counter . '][status]">';
        if ($rule['status'] == '1') {
            $result .= '        <option value="1" selected="selected">' . $this->language->get('text_enabled') . '</option>';
            $result .= '        <option value="0">' . $this->language->get('text_disabled') . '</option>';
        } else {
            $result .= '        <option value="1" >' . $this->language->get('text_enabled') . '</option>';
            $result .= '        <option value="0" selected="selected">' . $this->language->get('text_disabled') . '</option>';
        }
        $result .= '      </select>';
        $result .= '    </td>';
        $result .= '    <td class="center">';
        $result .= '      <div class="well well-sm" style="height: 100px; overflow: auto;">';
        if (!isset($rule['geo_zones'])) {
            $rule['geo_zones'] = array();
        }
        $class = 'even';
        foreach ($geo_zones as $geo_zone) {
            $class = ($class == 'even' ? 'odd' : 'even');
            $result .= '        <div class="checkbox ' . $class . '" style="padding: 0; min-height: auto;">';
            $result .= '<label>';
            if (in_array($geo_zone['geo_zone_id'], $rule['geo_zones'])) {
                $result .= '          <input type="checkbox" name="shipping_parcel_pro_type_id_' . $type_id . '_rule[' . $rule_counter . '][geo_zones][]" value="' . $geo_zone['geo_zone_id'] . '" checked="checked" />';
            } else {
                $result .= '          <input type="checkbox" name="shipping_parcel_pro_type_id_' . $type_id . '_rule[' . $rule_counter . '][geo_zones][]" value="' . $geo_zone['geo_zone_id'] . '" />';
            }
            $result .= '</label>';
            $result .= str_replace("'", "&#39;", $geo_zone['name']);
            $result .= '        </div>';
        }
        $result .= '      </div>';
        $result .= '      <a onclick="$(this).parent().find(\':checkbox\').prop(\'checked\', true);">Select All</a> / <a onclick="$(this).parent().find(\':checkbox\').prop(\'checked\', false);">Unselect All</a>';
        $result .= '    </td>';
        $result .= '    <td class="center">';
        $result .= '      <table style="margin: 0 auto;">';
        $result .= '        <thead>';
        $result .= '          <tr>';
        $result .= '            <td class="left">&nbsp;</td>';
        $result .= '            <td class="center">' . $this->language->get('text_min') . '</td>';
        $result .= '            <td class="center">' . $this->language->get('text_max') . '</td>';
        $result .= '          </tr>';
        $result .= '        </thead>';
        $result .= '        <tbody>';
        $result .= '          <tr>';
        $result .= '            <td class="left">' . $this->language->get('entry_sub_total') . '</td>';
        $result .= '            <td class="center"><input type="text" name="shipping_parcel_pro_type_id_' . $type_id . '_rule[' . $rule_counter . '][total_min]" value="' . $rule['total_min'] . '" size="5" class="form-control" style="width: 75%; padding:5px; display: inline-block; height: 30px;" /></td>';
        $result .= '            <td class="center"><input type="text" name="shipping_parcel_pro_type_id_' . $type_id . '_rule[' . $rule_counter . '][total_max]" value="' . $rule['total_max'] . '" size="5" class="form-control" style="width: 75%; padding:5px; display: inline-block; height: 30px;" /></td>';
        $result .= '          </tr>';
        $result .= '          <tr>';
        $result .= '            <td class="left">' . $this->language->get('entry_weight') . '</td>';
        $result .= '            <td class="center"><input type="text" name="shipping_parcel_pro_type_id_' . $type_id . '_rule[' . $rule_counter . '][weight_min]" value="' . $rule['weight_min'] . '" size="5" class="form-control" style="width: 75%; padding:5px; display: inline-block; height: 30px;" /></td>';
        $result .= '            <td class="center"><input type="text" name="shipping_parcel_pro_type_id_' . $type_id . '_rule[' . $rule_counter . '][weight_max]" value="' . $rule['weight_max'] . '" size="5" class="form-control" style="width: 75%; padding:5px; display: inline-block; height: 30px;" /></td>';
        $result .= '          <tr>';
        $result .= '        </tbody>';
        $result .= '      </table>';
        $result .= '    </td>';
        $result .= '    <td class="center">';
        $result .= '      <table style="margin: 0 auto;">';
        $result .= '        <tbody>';
        $result .= '          <tr>';
        $result .= '            <td class="left">' . $this->language->get('entry_handtekening') . '</td>';
        $result .= '            <td class="center">';
        $result .= '              <select name="shipping_parcel_pro_type_id_' . $type_id . '_rule[' . $rule_counter . '][handtekening]">';
        if ($rule['handtekening'] == '1') {
            $result .= '                <option value="1" selected="selected">' . $this->language->get('text_enabled') . '</option>';
            $result .= '                <option value="0">' . $this->language->get('text_disabled') . '</option>';
        } else {
            $result .= '                <option value="1" >' . $this->language->get('text_enabled') . '</option>';
            $result .= '                <option value="0" selected="selected">' . $this->language->get('text_disabled') . '</option>';
        }
        $result .= '              </select>';
        $result .= '            </td>';
        $result .= '          </tr>';
        $result .= '          <tr>';
        $result .= '            <td class="left">' . $this->language->get('entry_avond') . '</td>';
        $result .= '            <td class="center">';
        $result .= '              <select name="shipping_parcel_pro_type_id_' . $type_id . '_rule[' . $rule_counter . '][avond]">';
        if ($rule['avond'] == '1') {
            $result .= '                <option value="1" selected="selected">' . $this->language->get('text_enabled') . '</option>';
            $result .= '                <option value="0">' . $this->language->get('text_disabled') . '</option>';
        } else {
            $result .= '                <option value="1" >' . $this->language->get('text_enabled') . '</option>';
            $result .= '                <option value="0" selected="selected">' . $this->language->get('text_disabled') . '</option>';
        }
        $result .= '              </select>';
        $result .= '            </td>';
        $result .= '          </tr>';
        $result .= '          <tr>';
        $result .= '            <td class="left">' . $this->language->get('entry_extrazeker') . '</td>';
        $result .= '            <td class="center">';
        $result .= '              <select name="shipping_parcel_pro_type_id_' . $type_id . '_rule[' . $rule_counter . '][extrazeker]">';
        if ($rule['extrazeker'] == '1') {
            $result .= '                <option value="1" selected="selected">' . $this->language->get('text_enabled') . '</option>';
            $result .= '                <option value="0">' . $this->language->get('text_disabled') . '</option>';
        } else {
            $result .= '                <option value="1" >' . $this->language->get('text_enabled') . '</option>';
            $result .= '                <option value="0" selected="selected">' . $this->language->get('text_disabled') . '</option>';
        }
        $result .= '              </select>';
        $result .= '            </td>';
        $result .= '          </tr>';
        $result .= '          <tr>';
        $result .= '            <td class="left">' . $this->language->get('entry_nietbijburen') . '</td>';
        $result .= '            <td class="center">';
        $result .= '              <select name="shipping_parcel_pro_type_id_' . $type_id . '_rule[' . $rule_counter . '][nietbijburen]">';
        if ($rule['nietbijburen'] == '1') {
            $result .= '                <option value="1" selected="selected">' . $this->language->get('text_enabled') . '</option>';
            $result .= '                <option value="0">' . $this->language->get('text_disabled') . '</option>';
        } else {
            $result .= '                <option value="1" >' . $this->language->get('text_enabled') . '</option>';
            $result .= '                <option value="0" selected="selected">' . $this->language->get('text_disabled') . '</option>';
        }
        $result .= '              </select>';
        $result .= '            </td>';
        $result .= '          </tr>';
        $result .= '          <tr>';
        $result .= '            <td class="left">' . $this->language->get('entry_brievenbuspakje') . '</td>';
        $result .= '            <td class="center">';
        $result .= '              <select name="shipping_parcel_pro_type_id_' . $type_id . '_rule[' . $rule_counter . '][brievenbuspakje]">';
        if ($rule['brievenbuspakje'] == '1') {
            $result .= '                <option value="1" selected="selected">' . $this->language->get('text_enabled') . '</option>';
            $result .= '                <option value="0">' . $this->language->get('text_disabled') . '</option>';
        } else {
            $result .= '                <option value="1" >' . $this->language->get('text_enabled') . '</option>';
            $result .= '                <option value="0" selected="selected">' . $this->language->get('text_disabled') . '</option>';
        }
        $result .= '              </select>';
        $result .= '            </td>';
        $result .= '          </tr>';
        $result .= '        </tbody>';
        $result .= '      </table>';
        $result .= '    </td>';
        $result .= '    <td class="center">';
        $result .= '      <div><strong>' . $this->language->get('text_shipping_price') . '</strong></div>';
        $result .= '      <div><input type="text" name="shipping_parcel_pro_type_id_' . $type_id . '_rule[' . $rule_counter . '][cost]" value="' . $rule['cost'] . '" size="5" class="form-control" style="width: 50%; padding:5px; display: inline-block; height: 30px;" /></div>';
        $result .= '    </td>';
        $result .= '    <td class="left"><a onclick="$(\'#shipping_parcel_pro_type_id_' . $type_id . '_rule' . $rule_counter . '\').remove();" class="button">' . $this->language->get('button_remove') . '</a></td>';
        $result .= '  </tr>';
        $result .= '</tbody>';

        return $result;
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/parcelpro/shipping/parcel_pro')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

}

?>
