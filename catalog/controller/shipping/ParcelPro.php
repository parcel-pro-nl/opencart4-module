<?php

namespace Opencart\Catalog\Controller\Extension\ParcelPro\Shipping;

use Opencart\System\Engine\Controller;
use Opencart\System\Engine\Registry;
use Opencart\System\Library\TemplateBuffer;

class ParcelPro extends Controller
{
    private $pickupOptions = ['parcel_pro.shipping_parcel_pro_type_id_DFYParcelshop_0', 'parcel_pro.shipping_parcel_pro_type_id_3533_0'];

    public function __construct(Registry $registry)
    {
        parent::__construct($registry);
        require_once DIR_EXTENSION . 'parcelpro/system/library/TemplateBuffer.php';

    }

    // event handler for catalog/view/checkout/shipping_method/before
    public function addPickupPoints(&$route, &$data, &$template_code = null)
    {
        $gebruiker_id = $this->config->get('parcelpro_Id');
        $data['gebruiker_id'] = $gebruiker_id;

        if(isset($this->session->data['pickup_point_error'])){
            $data['pickup_point_error'] = $this->session->data['pickup_point_error'];

            unset($this->session->data['pickup_point_error']);
        }

        if(in_array($this->session->data['shipping_method'] ?? null, $this->pickupOptions) && isset($this->session->data['pickup_point']['company'])){
            $data['pp_firstname'] = $this->session->data['pickup_point']['firstname'] ?? null;
            $data['pp_lastname'] = $this->session->data['pickup_point']['lastname'] ?? null;
            $data['pp_company'] = $this->session->data['pickup_point']['company'] ?? null;
            $data['pp_address_1'] = $this->session->data['pickup_point']['address_1'] ?? null;
            $data['pp_address_2'] = $this->session->data['pickup_point']['address_2'] ?? null;
            $data['pp_postcode'] = $this->session->data['pickup_point']['postcode'] ?? null;
            $data['pp_city'] = $this->session->data['pickup_point']['city'] ?? null;

        }

        $template = new TemplateBuffer();
        $template_buffer = $template->getTemplateBuffer($route, $template_code, $this->config->get('config_theme'), $this->config->get('theme_default_directory'));

        $search = '</fieldset>';
        $replace = '</fieldset>
                   {% if(pickup_point_error) %}
                        <div class="alert alert-danger mt-3">{{ pickup_point_error }}</div>
                    {% endif %}
                      <div class="card mt-3" id="parcel-pro-pickup-point">
                      <div class="card-header">
                        Pickup point
                      </div>
                      <div class="card-body">
                        <h5 class="card-title">No pick-up location selected</h5>
                        <p class="card-text"></p>
                        <a href="#" id="parcel-pro-change-pickupt-point">Change pickup location</a>
                      </div>
                    </div>
                   
                      <input type="hidden" name="pp_gebruiker_id" value="{{ gebruiker_id }}">
                      <input type="hidden" name="pp_firstname" value="{{ pp_firstname }}">
                      <input type="hidden" name="pp_lastname" value="{{ pp_lastname }}">
                      <input type="hidden" name="pp_company" value="{{ pp_company }}">
                      <input type="hidden" name="pp_address_1" value="{{ pp_address_1 }}">
                      <input type="hidden" name="pp_address_2" value="{{ pp_address_2 }}">
                      <input type="hidden" name="pp_postcode" value="{{ pp_postcode }}">
                      <input type="hidden" name="pp_city" value="{{ pp_city }}">
                      <link href="extension/parcelpro/catalog/view/theme/default/stylesheet/parcelpro.css" rel="stylesheet">
                      <script src="extension/parcelpro/catalog/view/javascript/parcelpro.js" type="text/javascript"></script>
                      <div class="global-modal" id="modal">
                        <div class="overlay" id="global_overlay"></div>
                        <div class="global-modal_contents modal-transition">
                          <iframe class="global-frame" frameborder="0" scrolling="no" id="afhaalpunt_frame" src=""></iframe>
                        </div>
                      </div>';

        $template_buffer = str_replace($search, $replace, $template_buffer);

        $search = "$('#input-shipping-method').on('change', function () {";
        $replace = "$('#input-shipping-method').on('change', function () { 
                     const value = $(this).val().slice(0, -2).toLowerCase();
                     if (value === 'parcel_pro.shipping_parcel_pro_type_id_3533' || value === 'parcel_pro.shipping_parcel_pro_type_id_dfyparcelshop')
                     {
                        return;
                     }";

        $template_buffer = str_replace($search, $replace, $template_buffer);

        $template_code = $template_buffer;
    }

    // event handler for catalog/controller/checkout/shipping_method/save/before
    public function savePickupPoint(&$route, &$json)
    {
        if (in_array($this->request->post['shipping_method'], $this->pickupOptions) && !empty($this->request->post['pp_company'])) {
            $this->session->data['shipping_address']['zone'] = "";
            if ($this->request->post['pp_firstname'] != "") {
                $this->session->data['pickup_point']['firstname'] = $this->request->post['pp_firstname'];
            }
            if ($this->request->post['pp_lastname'] != "") {
                $this->session->data['pickup_point']['lastname'] = $this->request->post['pp_lastname'];
            }
            if ($this->request->post['pp_company'] != "") {
                $this->session->data['pickup_point']['company'] = $this->request->post['pp_company'];
            }
            if ($this->request->post['pp_address_1'] != "") {
                $this->session->data['pickup_point']['address_1'] = $this->request->post['pp_address_1'];
            }
            if ($this->request->post['pp_address_2'] != "") {
                $this->session->data['pickup_point']['address_2'] = $this->request->post['pp_address_2'];
            }
            if ($this->request->post['pp_postcode'] != "") {
                $this->session->data['pickup_point']['postcode'] = $this->request->post['pp_postcode'];
            }
            if ($this->request->post['pp_city'] != "") {
                $this->session->data['pickup_point']['city'] = $this->request->post['pp_city'];
            }
        }else{
            unset($this->session->data['pickup_point']);
        }
    }

    // event handler for catalog/controller/checkout/success/before
    public function savePickupPointToDatabase()
    {
        if(in_array($this->session->data['shipping_method'] ?? null, $this->pickupOptions) && !isset($this->session->data['pickup_point']['company'])){
            $this->session->data['pickup_point_error'] = 'Please choose a pick-up point';
            $this->response->redirect($this->url->link('checkout/checkout', 'language=' . $this->config->get('config_language')));

            return;
        }

        if(isset($this->session->data['order_id']) && in_array($this->session->data['shipping_method'] ?? null, $this->pickupOptions) && isset($this->session->data['pickup_point']['company'])){
            $order_id = $this->session->data['order_id'];
            $pickup_point_id = $this->session->data['pickup_point']['company'];
            $pickup_point_address = json_encode($this->session->data['pickup_point']);

            $sql = "UPDATE `" . DB_PREFIX . "order` SET su_pickup_point_id = '" . $pickup_point_id. "', su_pickup_point_address = '" . $pickup_point_address . "' WHERE order_id = " . (int)$order_id;
            $this->db->query($sql);

            unset($this->session->data['pickup_point']);
        }
    }
}
