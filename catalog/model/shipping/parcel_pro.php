<?php

namespace Opencart\Catalog\Model\Extension\Parcelpro\Shipping;

use Opencart\System\Engine\Model;
use Opencart\System\Engine\Registry;
use Opencart\System\Library\ParcelPro as ParcelProInstance;

class ParcelPro extends Model {

    private $shipping_methode_heading  = 'Parcel Pro';

    public function __construct(Registry $registry)
    {
        parent::__construct($registry);
        require_once DIR_EXTENSION . 'parcelpro/system/library/ParcelPro.php';
    }


    function getQuote($address) {
        if (!$this->config->get('shipping_parcel_pro_status')) {
            return;
        }

        $language_id = isset($this->session->data['language']) ? $this->session->data['language'] : $this->config->get('config_language');

        $parcel_pro_heading =  $this->config->get('parcel_pro_heading');
        $this->shipping_methode_heading = $parcel_pro_heading[$language_id] ?? 'Parcel Pro';

        $sub_total = $this->cart->getSubTotal();

        $weight = $this->cart->getWeight();

        $customer_geozones = array();
        $geozones = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE country_id = '" . (int) $address['country_id'] . "' AND (zone_id = '0' OR zone_id = '" . (int) $address['zone_id'] . "')");

        foreach ($geozones->rows as $geozone) {
            $customer_geozones[] = $geozone['geo_zone_id'];
        }

        $quote_data = array();

        $options = array();

        $instance = new ParcelProInstance($this->registry);
        $options = $instance->push_types_filtered_primitive($options,false);

        // Options
        for ($i = 0; $i < count($options); $i++) {

            if ($this->config->get('shipping_parcel_pro_type_id_'.$options[$i].'_status') and is_array($this->config->get('shipping_parcel_pro_type_id_'.$options[$i].'_rule'))) {
                $quote_data = array_merge($quote_data, $this->getIdQuotes($options[$i], $language_id, $sub_total, $weight, $customer_geozones));
            }
        }


        if (is_array($quote_data) and ! empty($quote_data)) {
            $quote_data = $this->subValSort($quote_data, 'sort_order');
        }

        $method_data = array();

        if ($quote_data) {
            $method_data = array(
                'title' =>  $this->shipping_methode_heading,
                'quote' => $quote_data,
                'sort_order' => $this->config->get('shipping_parcel_pro_sort_order'),
                'error' => false
            );
        }

        return $method_data;
    }

    private function subValSort($array, $sort_key) {
        foreach ($array as $key => $value) {
            $helper[$key] = strtolower($value[$sort_key]);
        }

        asort($helper);

        foreach ($helper as $key => $value) {
            $sorted[$key] = $array[$key];
        }

        return $sorted;
    }

    private function getIdQuotes($id, $language_id, $sub_total, $weight, $customer_geozones) {
        $quote_data = array();

        foreach ($this->config->get('shipping_parcel_pro_type_id_' . $id . '_rule') as $key => $rule) {
            if ($rule['status']) {
                $rule_geozones = isset($rule['geo_zones']) ? $rule['geo_zones'] : array();
                $check_zone = $this->checkZone($rule_geozones, $customer_geozones);
                if (!$check_zone)
                    continue;

                $midtime_total = $sub_total;
                 if($this->config->get('shipping_parcel_pro_type_id_' . $id . '_tax_class_id')){
                    $midtime_total = $this->tax->calculate($midtime_total, $this->config->get('shipping_parcel_pro_type_id_' . $id . '_tax_class_id'), $this->config->get('config_tax'));
                 }

                $check_subtotal = $this->checkSubTotal($rule['total_min'], $rule['total_max'], $midtime_total);
                if (!$check_subtotal)
                    continue;

                $check_weight = $this->checkWeight($rule['weight_min'], $rule['weight_max'], $weight);
                if (!$check_weight)
                    continue;

                $quote_data['shipping_parcel_pro_type_id_' . $id . '_' . $key] = array(
                    'sort_order' => $this->config->get('shipping_parcel_pro_type_id_' . $id . '_sort_order'),
                    'code' => 'parcel_pro.shipping_parcel_pro_type_id_' . $id . '_' . $key,
                    'title' => !empty($rule['name'][$language_id]) ? $rule['name'][$language_id] :  $this->shipping_methode_heading,
                    'cost' => $rule['cost'],
                    'tax_class_id' => $this->config->get('shipping_parcel_pro_type_id_'.$id.'_tax_class_id'),
                    'text' => $this->currency->format($this->tax->calculate($rule['cost'], $this->config->get('shipping_parcel_pro_type_id_' . $id . '_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'])
                );
            }
        }

        return $quote_data;
    }

    private function checkZone($rule_geozones, $customer_geozones) {
        $status = false;

        foreach ($rule_geozones as $rule_geozone) {
            if (in_array($rule_geozone, $customer_geozones)) {
                $status = true;
            }
        }

        return $status;
    }

    private function checkSubTotal($total_min, $total_max, $sub_total) {
        $status = true;

        if ($total_min) {
            if ($sub_total < $total_min) {
                $status = false;
            }
        }

        if ($total_max) {
            if ($sub_total > $total_max) {
                $status = false;
            }
        }

        return $status;
    }

    private function checkWeight($weight_min, $weight_max, $weight) {
        $status = true;

        if ($weight_min) {
            if ($weight < $weight_min) {
                $status = false;
            }
        }

        if ($weight_max) {
            if ($weight > $weight_max) {
                $status = false;
            }
        }

        return $status;
    }

}

?>
