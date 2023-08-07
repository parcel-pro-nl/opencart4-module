<?php

namespace Opencart\Admin\Controller\Extension\ParcelPro\Sale;

use Opencart\System\Engine\Controller;
use Opencart\System\Engine\Registry;
use Opencart\System\Library\ParcelPro as ParcelProInstance;

class ParcelPro extends Controller
{
    private $api_url = 'https://login.parcelpro.nl';
    private $webhook_url = 'https://login.parcelpro.nl/api/opencart/order-created.php';

    private $error = array();

    public function __construct(Registry $registry)
    {
        parent::__construct($registry);
        require_once DIR_EXTENSION . 'parcelpro/system/library/ParcelPro.php';
    }

    public function index()
    {
        $this->checkdb();
        $this->language->load('sale/pp_order');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('sale/pp_order');
    }

    public function submit_su()
    {
        $this->language->load('extension/parcelpro/sale/pp_order');
        $this->load->model('extension/parcelpro/sale/parcelproorder');

        if (isset($this->request->post['selected']) && ($this->validateSubmitSu())) {
            $errorMessages = array();
            $count_right = 0;
            $count_wrong = 0;
            $responseData = [];

            foreach ($this->request->post['selected'] as $order_id) {
                $error_order = false;

                $order_info = $this->model_extension_parcelpro_sale_parcelproorder->getOrder($order_id);


                if (!$order_info) {
                    if (isset($this->error['warning'])) {
                        $this->error['warning'] .= '<br / >No order info found for order: ' . $order_id;
                    } else {
                        $this->error['warning'] = 'No order info found for order: ' . $order_id;
                    }

                    $count_wrong++;
                    $error_order = true;
                }

                // Indien de order al is aangemeld (barcode in db) doorgaan.
                if ($order_info["su_barcode"] != '') {
                    if (isset($this->error['warning'])) {
                        $this->error['warning'] .= '<br/>Order ' . $order_id . ' has already been submitted';
                    } else {
                        $this->error['warning'] = 'Order ' . $order_id . ' has already been submitted';
                    }

                    continue;
                }


                $ParcelPro_API = ParcelProInstance::get_instance($this->registry);
                $data = $ParcelPro_API->format_order_data($order_info);

                //Check before submitting
                if (
                    !empty($data['shipping_firstname'])
                    && !empty($data['shipping_lastname'])
                    && !empty($data['shipping_address_1'])
                    && !empty($data['nummer'])
                    && !empty($data['shipping_postcode'])
                    && !empty($data['shipping_city'])
                    && !empty($data['shipping_country'])

                ) {
                    $submit_result = $ParcelPro_API->submitShipping($data);

                } else {
                    array_push($errorMessages, "ORDER ID:" . $order_id . ' ' . $this->language->get('text_missing_data'));
                    $submit_result = null;
                }

                if (!$submit_result) {
                    $count_wrong++;
                    $error_order = true;
                } else {
                    if (isset($submit_result['level'])) {
                        if ($submit_result['level'] = 'error') {
                            if (isset($this->error['warning'])) {
                                $this->error['warning'] .= '<br / >Parcel Pro error for order: ' . $order_id . ' | ErrorCode: ' . $submit_result['code'] . ' | Omschrijving: ' . $submit_result['omschrijving'];
                            } else {
                                $this->error['warning'] = 'Parcel Pro error for order: ' . $order_id . ' | ErrorCode: ' . $submit_result['code'] . ' | Omschrijving: ' . $submit_result['omschrijving'];
                            }

                            $count_wrong++;
                            $error_order = true;
                        }
                    }
                }

                if (!$error_order) {

                    $saving_data = array(
                        'su_order_id' => $submit_result['Id'],
                        'su_url_tracking' => $submit_result['TrackingUrl'],
                        'su_url_label' => $submit_result['LabelUrl'],
                        'su_barcode' => $submit_result['Barcode'] ?? '',
                        'su_barcodes' => '',
                        'su_colli' => 1,
                    );

                    $ParcelPro_API->saveSuData($order_id, $saving_data);

                    $responseData['successOrders'][] = $order_id;

                    $count_right++;
                }
            }

            if ($count_right) {
                if (isset($responseData['success'])) {
                    $responseData['success'] .= '<br / >' . $count_right . ' ' . $this->language->get('text_count_right');
                } else {
                    $responseData['success'] = $count_right . ' ' . $this->language->get('text_count_right');
                }
            }

            if ($count_wrong) {
                if (isset($this->error['warning'])) {
                    $this->error['warning'] .= '<br / >' . $count_wrong . ' ' . $this->language->get('text_count_wrong');
                } else {
                    $this->error['warning'] = $count_wrong . ' ' . $this->language->get('text_count_wrong');
                }
                for ($i = 0; $i < count($errorMessages); $i++) {
                    $this->error['warning'] .= '<br / >' . $errorMessages[$i];
                }
            } elseif (!isset($this->request->post['selected']) && ($this->validateSubmitSu())) {
                $this->error['warning'] = $this->language->get('error_nothing_selected');
            }
        } else {
            $this->error['warning'] = $this->language->get('error_nothing_selected');
        }

        $responseData['error'] = $this->error['warning'] ?? null;

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($responseData));
    }

    public function label()
    {
        $this->language->load('extension/parcelpro/sale/pp_order');
        $this->load->model('extension/parcelpro/sale/parcelproorder');

        $label_queue = array();
        $order_ids = array();

        if ((isset($this->request->post['selected']) or isset($this->request->get['order_id'])) && ($this->validateSubmitSu())) {
            if (isset($this->request->get['order_id']) and !isset($this->request->post['selected'])) {
                $this->request->post['selected'][] = $this->request->get['order_id'];
            }

            foreach ($this->request->post['selected'] as $order_id) {
                $order_info = $this->model_extension_parcelpro_sale_parcelproorder->getOrder($order_id);

                if ($order_info) {
                    $label_queue[] = $order_info['su_order_id'];
                    $order_ids[] = $order_id;
                }
            }

            if (!empty($label_queue)) {
                $label_url = $this->getLabelUrl($label_queue);

                if ($label_url) {
                    foreach ($order_ids as $order_id) {
                        $this->model_extension_parcelpro_sale_parcelproorder->disableLabelPrint($order_id);
                    }

                    header('Location: ' . $label_url);
                } else {
                    $this->error['warning'] = $this->language->get('error_action');
                    $this->response->redirect($this->url->link('sale/order', 'user_token=' . $this->session->data['user_token']));
                }
            }
        } else {
            $this->error['warning'] = $this->language->get('error_nothing_selected');
            $this->response->redirect($this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'], true));
        }
    }

    protected function getLabelUrl($label_queue = array())
    {
        $su_order_id = $label_queue['0'];
        if (is_null($su_order_id)) {
            return null;
        }
        $gebruiker_id = $this->config->get('parcelpro_Id');
        $apiKey = $this->config->get('parcelpro_ApiKey');
        $hmacSha256 = hash_hmac("sha256", $gebruiker_id . $su_order_id, $apiKey);
        $data = array(
            'GebruikerId' => $gebruiker_id,
            'ZendingId' => $su_order_id,
            'HmacSha256' => $hmacSha256
        );

        if (count($label_queue) >= 2) {
            $count = 0;

            foreach ($label_queue as $label) {
                $data['selected[' . $count . ']'] = $label;

                $count++;
            }
        }

        $queryData = http_build_query($data);

        $label_url = $this->api_url . '/api/label.php?' . $queryData . '&PrintPdf=true';

        return $label_url;
    }

    protected function validateSubmitSu()
    {
        if (!$this->user->hasPermission('modify', 'sale/order')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    private function checkdb()
    {
        $table = '`' . DB_PREFIX . 'order`';
        $column = '`su_date_added`';
        $sql = "DESC $table $column";

        $query = $this->db->query($sql);

        if (!$query->num_rows) {
            $sql = "alter table $table add column $column datetime NOT NULL after `date_modified`";

            $this->db->query($sql);
        }

        $table = '`' . DB_PREFIX . 'order`';
        $column = '`su_weight`';
        $sql = "DESC $table $column";

        $query = $this->db->query($sql);

        if (!$query->num_rows) {
            $sql = "alter table $table add column $column varchar(16) NULL after `date_modified`";

            $this->db->query($sql);
        }

        $table = '`' . DB_PREFIX . 'order`';
        $column = '`su_colli`';
        $sql = "DESC $table $column";

        $query = $this->db->query($sql);

        if (!$query->num_rows) {
            $sql = "alter table $table add column $column int(11) NOT NULL DEFAULT '1' after `date_modified`";

            $this->db->query($sql);
        }

        $table = '`' . DB_PREFIX . 'order`';
        $column = '`su_barcodes`';
        $sql = "DESC $table $column";

        $query = $this->db->query($sql);

        if (!$query->num_rows) {
            $sql = "alter table $table add column $column text NOT NULL after `date_modified`";

            $this->db->query($sql);
        }

        $table = '`' . DB_PREFIX . 'order`';
        $column = '`su_barcode`';
        $sql = "DESC $table $column";

        $query = $this->db->query($sql);

        if (!$query->num_rows) {
            $sql = "alter table $table add column $column varchar(128) NOT NULL after `date_modified`";

            $this->db->query($sql);
        }

        $table = '`' . DB_PREFIX . 'order`';
        $column = '`su_url_label`';
        $sql = "DESC $table $column";

        $query = $this->db->query($sql);

        if (!$query->num_rows) {
            $sql = "alter table $table add column $column varchar(255) NOT NULL after `date_modified`";

            $this->db->query($sql);
        }

        $table = '`' . DB_PREFIX . 'order`';
        $column = '`su_label_printed`';
        $sql = "DESC $table $column";

        $query = $this->db->query($sql);

        if (!$query->num_rows) {
            $sql = "alter table $table add column $column varchar(255) NOT NULL after `date_modified`";

            $this->db->query($sql);
        }

        $table = '`' . DB_PREFIX . 'order`';
        $column = '`su_url_tracking`';
        $sql = "DESC $table $column";

        $query = $this->db->query($sql);

        if (!$query->num_rows) {
            $sql = "alter table $table add column $column  varchar(255) NOT NULL after `date_modified`";

            $this->db->query($sql);
        }

        $table = '`' . DB_PREFIX . 'order`';
        $column = '`su_order_id`';
        $sql = "DESC $table $column";

        $query = $this->db->query($sql);

        if (!$query->num_rows) {
            $sql = "alter table $table add column $column int(11) NULL after `date_modified`";

            $this->db->query($sql);
        }
    }
}
