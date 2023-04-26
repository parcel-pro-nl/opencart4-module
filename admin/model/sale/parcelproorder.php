<?php

namespace Opencart\Admin\Model\Extension\ParcelPro\Sale;

//==============================================================================
// Parel Pro Shipping v1.0.1
//
// Company: Parcel Pro
// Contact: info@parcelpro.nl
//==============================================================================

use Opencart\System\Engine\Model;

class ParcelProOrder extends Model
{

    public function getOrder($order_id)
    {
        $order_query = $this->db->query("SELECT *, (SELECT CONCAT(c.firstname, ' ', c.lastname) FROM " . DB_PREFIX . "customer c WHERE c.customer_id = o.customer_id) AS customer FROM `" . DB_PREFIX . "order` o WHERE o.order_id = '" . (int)$order_id . "'");

        if ($order_query->num_rows) {
            $country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$order_query->row['shipping_country_id'] . "'");

            if ($country_query->num_rows) {
                $country_iso_code_2 = $country_query->row['iso_code_2'];
            } else {
                $country_iso_code_2 = '';
            }

            return array(
                'order_id' => $order_query->row['order_id'],
                'invoice_no' => $order_query->row['invoice_no'],
                'invoice_prefix' => $order_query->row['invoice_prefix'],
                'email' => $order_query->row['email'],
                'firstname' => $order_query->row['firstname'],
                'lastname' => $order_query->row['lastname'],
                'telephone' => $order_query->row['telephone'],
                'payment_firstname' => $order_query->row['payment_firstname'],
                'payment_lastname' => $order_query->row['payment_lastname'],
                'payment_company' => $order_query->row['payment_company'],
                'payment_address_1' => $order_query->row['payment_address_1'],
                'payment_address_2' => $order_query->row['payment_address_2'],
                'payment_postcode' => $order_query->row['payment_postcode'],
                'payment_city' => $order_query->row['payment_city'],
                'shipping_firstname' => $order_query->row['shipping_firstname'],
                'shipping_lastname' => $order_query->row['shipping_lastname'],
                'shipping_company' => $order_query->row['shipping_company'],
                'shipping_address_1' => $order_query->row['shipping_address_1'],
                'shipping_address_2' => $order_query->row['shipping_address_2'],
                'shipping_postcode' => $order_query->row['shipping_postcode'],
                'shipping_city' => $order_query->row['shipping_city'],
                'shipping_country' => $order_query->row['shipping_country'],
                'shipping_country_iso_2' => $country_iso_code_2,
                'shipping_code' => $order_query->row['shipping_code'] ?? '',
                'total' => $order_query->row['total'],
                'su_pickup_point_id' => $order_query->row['su_pickup_point_id'],
                'su_pickup_point_address' => $order_query->row['su_pickup_point_address'],
                'su_order_id' => $order_query->row['su_order_id'],
                'su_label_printed' => $order_query->row['su_label_printed'],
                'su_barcode' => $order_query->row['su_barcode'],
                'su_weight' => $order_query->row['su_weight'],
                'su_colli' => $order_query->row['su_colli'],
                'date_added' => $order_query->row['date_added']
            );
        } else {
            return false;
        }
    }

    public function getOrders($data = array())
    {
        $this->updateWeight();

        $sql = "SELECT o.order_id, CONCAT(o.firstname, ' ', o.lastname) AS customer, (SELECT os.name FROM " . DB_PREFIX . "order_status os WHERE os.order_status_id = o.order_status_id AND os.language_id = '" . (int)$this->config->get('config_language_id') . "') AS status, o.date_added, o.shipping_code, o.su_order_id, o.su_url_tracking, su_url_label, o.su_barcode, o.su_barcodes, o.su_label_printed, o.su_colli, o.su_weight, o.su_date_added FROM `" . DB_PREFIX . "order` o";

        if (isset($data['filter_order_status_id']) && !is_null($data['filter_order_status_id'])) {
            $sql .= " WHERE o.shipping_code LIKE 'parcel_pro%' AND o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
        } else {
            $sql .= " WHERE o.shipping_code LIKE 'parcel_pro%' AND o.order_status_id > '0'";
        }

        if (!empty($data['filter_order_id'])) {
            $sql .= " AND o.order_id = '" . (int)$data['filter_order_id'] . "'";
        }

        if (!empty($data['filter_customer'])) {
            $sql .= " AND CONCAT(o.firstname, ' ', o.lastname) LIKE '%" . $this->db->escape($data['filter_customer']) . "%'";
        }

        if (!empty($data['filter_date_added'])) {
            $sql .= " AND DATE(o.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
        }

        if (!empty($data['filter_date_modified'])) {
            $sql .= " AND DATE(o.date_modified) = DATE('" . $this->db->escape($data['filter_date_modified']) . "')";
        }

        if (!empty($data['filter_total'])) {
            $sql .= " AND o.total = '" . (float)$data['filter_total'] . "'";
        }

        $sort_data = array(
            'o.order_id',
            'customer',
            'status',
            'o.date_added',
            'o.date_modified',
            'o.total'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY o.order_id";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getTotalOrders($data = array())
    {
        $sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order`";

        if (isset($data['filter_order_status_id']) && !is_null($data['filter_order_status_id'])) {
            $sql .= " WHERE shipping_code LIKE 'parcel_pro%' AND order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
        } else {
            $sql .= " WHERE shipping_code LIKE 'parcel_pro%' AND order_status_id > '0'";
        }

        if (!empty($data['filter_order_id'])) {
            $sql .= " AND order_id = '" . (int)$data['filter_order_id'] . "'";
        }

        if (!empty($data['filter_customer'])) {
            $sql .= " AND CONCAT(firstname, ' ', lastname) LIKE '%" . $this->db->escape($data['filter_customer']) . "%'";
        }

        if (!empty($data['filter_date_added'])) {
            $sql .= " AND DATE(date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
        }

        if (!empty($data['filter_date_modified'])) {
            $sql .= " AND DATE(o.date_modified) = DATE('" . $this->db->escape($data['filter_date_modified']) . "')";
        }

        if (!empty($data['filter_total'])) {
            $sql .= " AND total = '" . (float)$data['filter_total'] . "'";
        }

        $query = $this->db->query($sql);

        return $query->row['total'];
    }

    public function getTotalOrdersByOrderStatusId($order_status_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE order_status_id = '" . (int)$order_status_id . "' AND order_status_id > '0'");

        return $query->row['total'];
    }

    public function disableLabelPrint($order_id)
    {
        $this->db->query("UPDATE `" . DB_PREFIX . "order` SET su_label_printed = '1' WHERE order_id = '" . (int)$order_id . "'");
    }

    public function updateWeight()
    {
        $weight = 0;

        $this->load->model('sale/order');
        $query = $this->db->query("SELECT order_id FROM `" . DB_PREFIX . "order` WHERE su_weight IS NULL AND order_status_id >= 1");

        $orders = $query->rows;

        foreach ($orders as $order) {
            $order_products_query = $this->db->query("SELECT p.weight, wc.value, op.quantity FROM " . DB_PREFIX . "order_product op LEFT JOIN " . DB_PREFIX . "product p ON op.product_id = p.product_id LEFT JOIN " . DB_PREFIX . "weight_class wc ON wc.weight_class_id = p.weight_class_id WHERE op.order_id = '" . (int)$order['order_id'] . "'");
            $order_products = $order_products_query->rows;

            foreach ($order_products as $order_product) {
                $weight += ($order_product['weight'] / $order_product['value']) * $order_product['quantity'];
            }

            $this->db->query("UPDATE `" . DB_PREFIX . "order` SET su_weight = '" . (float)round($weight) . "' WHERE order_id = '" . (int)$order['order_id'] . "'");

            $weight = 0;
        }
    }

    public function updateColli($order_id, $colli)
    {
        $this->db->query("UPDATE `" . DB_PREFIX . "order` SET su_colli = '" . (int)($colli) . "' WHERE order_id = '" . (int)$order_id . "'");
    }

}

?>
