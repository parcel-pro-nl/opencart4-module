<?php

namespace Opencart\Admin\Controller\Extension\ParcelPro\Module;

use Opencart\System\Engine\Registry;
use Opencart\System\Library\TemplateBuffer;

class ParcelPro extends \Opencart\System\Engine\Controller
{
    private $error = array();

    public function __construct(Registry $registry)
    {
        parent::__construct($registry);
        require_once DIR_EXTENSION . 'parcelpro/system/library/TemplateBuffer.php';
    }

    public function install(): void
    {
        $this->checkdb();
        $this->load->model("setting/event");
        $this->model_setting_event->addEvent([
            'code' => 'parcelpro',
            'description' => '',
            'trigger' => 'catalog/model/checkout/order/addHistory/after',
            'action' => 'extension/parcelpro/module/parcelpro/post_order_add',
            'status' => 1,
            'sort_order' => 1
        ]);

        $this->model_setting_event->addEvent([
            'code' => 'parcelpro_add_buttons_order_list',
            'description' => '',
            'trigger' => 'admin/view/sale/order_list/before',
            'action' => 'extension/parcelpro/module/parcelpro|addParcelButtons',
            'status' => 1,
            'sort_order' => 1
        ]);

        $this->model_setting_event->addEvent([
            'code' => 'parcelpro_add_buttons_order',
            'description' => '',
            'trigger' => 'admin/view/sale/order/before',
            'action' => 'extension/parcelpro/module/parcelpro|addParcelButtonsToOrder',
            'status' => 1,
            'sort_order' => 1
        ]);

        // Enable module
        $this->load->model('setting/setting');

        $data['module_parcelpro_status'] = 1;
        $this->model_setting_setting->editSetting('module_parcelpro', $data);
    }

    public function uninstall()
    {
        // Disable module
        $this->load->model('setting/setting');
        $data['module_parcelpro_status'] = 0;
        $this->model_setting_setting->editSetting('module_parcelpro', $data);
        $this->load->model('setting/event');
        $this->model_setting_event->deleteEventByCode('parcelpro');
        $this->model_setting_event->deleteEventByCode('parcelpro_add_buttons_order_list');
        $this->model_setting_event->deleteEventByCode('parcelpro_add_buttons_order');

    }


    public function index()
    {
        $this->checkdb();
        $this->language->load('extension/parcelpro/module/parcelpro');

        $this->load->model('setting/setting');

        $this->document->setTitle($this->language->get('heading_title'));

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

            $this->model_setting_setting->editSetting('parcelpro', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension&type=module', 'user_token=' . $this->session->data['user_token'], 'SSL'));
        }

        $data['heading_title'] = $this->language->get('heading_title');

        $data['entry_Id'] = $this->language->get('entry_Id');
        $data['entry_ApiKey'] = $this->language->get('entry_ApiKey');
        $data['entry_Webhook'] = $this->language->get('entry_Webhook');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_module_add'] = $this->language->get('button_module_add');
        $data['button_remove'] = $this->language->get('button_remove');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link('extension/module', 'user_token=' . $this->session->data['user_token'], 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/parcelpro', 'user_token=' . $this->session->data['user_token'], 'SSL')
        );

        $data['action'] = $this->url->link('extension/parcelpro/module/parcelpro', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension&type=module', 'user_token=' . $this->session->data['user_token'], 'SSL');

        if (isset($this->request->post['parcelpro_Id'])) {
            $data['parcelpro_Id'] = $this->request->post['parcelpro_Id'];
        } else {
            $data['parcelpro_Id'] = $this->config->get('parcelpro_Id');
        }

        if (isset($this->request->post['parcelpro_ApiKey'])) {
            $data['parcelpro_ApiKey'] = $this->request->post['parcelpro_ApiKey'];
        } else {
            $data['parcelpro_ApiKey'] = $this->config->get('parcelpro_ApiKey');
        }

        if (isset($this->request->post['parcelpro_Webhook'])) {
            $data['parcelpro_Webhook'] = $this->request->post['parcelpro_Webhook'];
        } else {
            if (!($data['parcelpro_Webhook'] = $this->config->get('parcelpro_Webhook'))) {
                $data['parcelpro_Webhook'] = "https://login.parcelpro.nl/api/opencart/order-created.php";
            }
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
//        echo '<pre>';
//        var_dump($data);
//        exit;
        $this->response->setOutput($this->load->view('extension/parcelpro/module/parcelpro', $data));
    }

    private function checkdb()
    {
        $table = '`' . DB_PREFIX . 'order`';
        $column = '`su_date_added`';
        $sql = "DESC $table $column";

        $query = $this->db->query($sql);

        if (!$query->num_rows) {
            $sql = "alter table $table add column $column datetime NULL after `date_modified`";

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

    ////
    // event handler for admin/view/sale/order_list/before
    public function addParcelButtons(&$route, &$data, &$template_code = null)
    {
        $template = new TemplateBuffer();
        $template_buffer = $template->getTemplateBuffer($route, $template_code);

        $orders = [];

        $order_ids = array_column($data['orders'], 'order_id');
        $barcodes = $this->getBarcodes($order_ids);

        foreach ($data['orders'] as $order) {
            $order['su_barcode'] = $barcodes[$order['order_id']];

            $orders[] = $order;
        }

        $data['orders'] = $orders;

        $this->language->load('extension/parcelpro/sale/pp_order');

        $search = '<a href="{{ order.view }}" data-bs-toggle="tooltip" title="{{ button_view }}" class="btn btn-primary"><i class="fa-solid fa-eye"></i></a>';
        $replace = '<a href="{{ order.view }}" data-bs-toggle="tooltip" title="{{ button_view }}" class="btn btn-primary"><i class="fa-solid fa-eye"></i></a>
                    <button data-id="{{ order.order_id }}" 
                    data-action="' . $this->url->link('extension/parcelpro/sale/parcelpro|submit_su', 'user_token=' . $this->session->data['user_token']) . '" 
                    data-bs-toggle="tooltip" title="' . $this->language->get('button_submit_su') . '" 
                    class="btn btn-primary send-to-parcel-pro"
                    {% if order.su_barcode %} disabled {% endif %}>
                    <i class="fa-solid fa-truck"></i></button>
                    {% if order.su_barcode %} <a href="' . $this->url->link('extension/parcelpro/sale/parcelpro|label', 'user_token=' . $this->session->data['user_token'] . '&order_id={{ order.order_id }}') . '" class="btn btn-primary" data-bs-toggle="tooltip" title="Print Parcel Pro verzendlabel"><i class="fa-solid fa-file"></i></a> {% endif %}';

        $template_buffer = str_replace($search, $replace, $template_buffer);

        //javacript
        $search = '</form>';
        $replace = "</form>
             <script type='text/javascript'>
                   $('.send-to-parcel-pro').on('click', function (e){
                       e.preventDefault();
                       $(this).prop('disabled', true);
                       const url = $(this).attr('data-action');
                       const id = $(this).attr('data-id');
                       const element = this;
         
                       const data = {
                           selected: [id]
                       }
                                                                     
                       $.ajax({
                                url: url,
                                type: 'post',
                                data: data,
                                dataType: 'json',
                                beforeSend: function () {
                                $(element).prop('disabled', true).addClass('loading');
                            },
                            complete: function () {
                                $(element).removeClass('loading');
                            },
                            success: function (json) {
                                $('.alert-dismissible').remove();
                        
                                if (json['error']) {
                                    $(element).prop('disabled', false);
                                    $('#alert').prepend('<div class=\"alert alert-danger alert-dismissible\"><i class=\"fa-solid fa-circle-exclamation\"></i> ' + json['error'] + ' <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button></div>');
                       
                                }
                        
                                if (json['success']) {
                                    $('#alert').prepend('<div class=\"alert alert-success alert-dismissible\"><i class=\"fa-solid fa-check-circle\"></i> ' + json['success'] + ' <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button></div>');
                                }
                                
                                $('#order').load($('#form-order').attr('data-load'));

                            },
                                    error: function (xhr, ajaxOptions, thrownError) {
                               
                            }
                        });
                   })
            </script>";

        $template_buffer = str_replace($search, $replace, $template_buffer);


        $template_code = $template_buffer;

        return null;
    }

    // event handler for admin/view/sale/order/before
    public function addParcelButtonsToOrder(&$route, &$data, &$template_code = null)
    {
        $template = new TemplateBuffer();
        $template_buffer = $template->getTemplateBuffer($route, $template_code);

        $search = '<button type="button" data-bs-toggle="tooltip" title="{{ button_filter }}" onclick="$(\'#filter-order\').toggleClass(\'d-none\');" class="btn btn-light d-lg-none"><i class="fa-solid fa-filter"></i></button>';
        $replace = '<button type="submit" id="button-submit-parcelpro" form="form-order" 
                    data-action="' . $this->url->link('extension/parcelpro/sale/parcelpro|submit_su', 'user_token=' . $this->session->data['user_token']) . '" 
                    data-bs-toggle="tooltip" title="' . $this->language->get('button_submit_su') . '" 
                    class="btn btn-primary">
                    <i class="fa-solid fa-paper-plane"></i></button>
                    <button type="submit" id="button-label-parcelpro" form="form-order" formtarget="_blank"
                        formaction="' . $this->url->link('extension/parcelpro/sale/parcelpro|label', 'user_token=' . $this->session->data['user_token'] . '&order_id={{ order.order_id }}') . '" 
                    class="btn btn-primary" data-bs-toggle="tooltip" title="Print Parcel Pro verzendlabel"><i class="fa-solid fa-file"></i>
                    </button>
                    <button type="button" data-bs-toggle="tooltip" title="{{ button_filter }}" onclick="$(\'#filter-order\').toggleClass(\'d-none\');" class="btn btn-light d-lg-none"><i class="fa-solid fa-filter"></i></button>';

        $template_buffer = str_replace($search, $replace, $template_buffer);

        $search = '<script type="text/javascript">';
        $replace = "
            <script type=\"text/javascript\">
                $('#button-submit-parcelpro, #button-label-parcelpro').prop('disabled', true);
                
                $('input[name^=\'selected\']').on('change', function () {
                    $('#button-submit-parcelpro, #button-label-parcelpro').prop('disabled', true);
                    var selected = $('input[name^=\'selected\']:checked');
                
                    if (selected.length) {
                        $('#button-submit-parcelpro, #button-label-parcelpro').prop('disabled', false);
                    } 
                });
      
                
                $('#button-submit-parcelpro').on('click', function(e) {
                        e.preventDefault();
                            
                        const element = this;
                        const url = $(this).attr('data-action');
                        
                        $.ajax({
                            url: url,
                            type: 'post',
                            data: $('#form-order').serialize(),
                            dataType: 'json',
                            beforeSend: function () {
                                $(element).prop('disabled', true).addClass('loading');
                            },
                            complete: function () {
                                $(element).removeClass('loading');
                            },
                            success: function (json) {
                                $('.alert-dismissible').remove();
                    
                                if (json['error']) {
                                    $('#alert').prepend('<div class=\"alert alert-danger alert-dismissible\"><i class=\"fa-solid fa-circle-exclamation\"></i> ' + json['error'] + ' <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button></div>');
                                }
                    
                                if (json['success']) {
                                    $('#alert').prepend('<div class=\"alert alert-success alert-dismissible\"><i class=\"fa-solid fa-check-circle\"></i> ' + json['success'] + ' <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button></div>');
                                    
                                }
                                $('#order').load($('#form-order').attr('data-load'));
                            },
                            error: function (xhr, ajaxOptions, thrownError) {
                                console.log(thrownError);
                                console.log(xhr.statusText);
                                console.log(xhr.responseText)
                            }
                        })
                    })  
                </script>
            <script type=\"text/javascript\">";

        $template_buffer = str_replace($search, $replace, $template_buffer);

        $template_code = $template_buffer;

        return null;
    }

    public function getBarcodes($order_ids = [])
    {
        $order_query = $this->db->query("SELECT order_id, su_barcode FROM " . DB_PREFIX . "order WHERE order_id IN (" . implode(',', $order_ids) . ")");

        return array_column($order_query->rows, 'su_barcode', 'order_id');
    }


}
