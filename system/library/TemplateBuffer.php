<?php

namespace Opencart\System\Library;

use Opencart\System\Engine\Controller;
use VQMod;

class TemplateBuffer
{
    // return template file contents as a string
    public function getTemplateBuffer($route, $event_template_buffer, $config_theme = null, $config_theme_directory = null)
    {
        // if there already is a modified template from view/*/before events use that one
        if ($event_template_buffer) {
            return $event_template_buffer;
        }

        // load the template file (possibly modified by ocmod and vqmod) into a string buffer
        if ($this->isAdmin()) {
            $dir_template = DIR_TEMPLATE;
        } else {
            if ($config_theme == 'basic') {
                $theme = $config_theme_directory;
            } else {
                $theme = $config_theme;
            }
            $dir_template = DIR_TEMPLATE . $theme;
        }
        $template_file = $dir_template . $route . '.twig';

        if (file_exists($template_file) && is_file($template_file)) {
            $template_file = $this->modCheck($template_file);
            return file_get_contents($template_file);
        }
        if ($this->isAdmin()) {
            trigger_error("Cannot find template file for route '$route'");
            exit;
        }
        $dir_template = DIR_TEMPLATE . 'default/template/';
        $template_file = $dir_template . $route . '.twig';
        if (file_exists($template_file) && is_file($template_file)) {
            $template_file = $this->modCheck($template_file);
            return file_get_contents($template_file);
        }
        trigger_error("Cannot find template file for route '$route'");
        exit;
    }


    protected function isAdmin()
    {
        return defined('DIR_CATALOG') ? true : false;
    }


    protected function modCheck($file)
    {
        // return a PHP file possibly modified by OpenCart's system/storage/modification,
        //   and then possibly modified by vqmod (see also https://github.com/vqmod/vqmod)

        // Use OpenCart's modified file is available
        $original_file = $file;
        if (defined('DIR_MODIFICATION')) {
            if ($this->startsWith($file, DIR_APPLICATION)) {
                if ($this->isAdmin()) {
                    if (file_exists(DIR_MODIFICATION . 'admin/' . substr($file, strlen(DIR_APPLICATION)))) {
                        $file = DIR_MODIFICATION . 'admin/' . substr($file, strlen(DIR_APPLICATION));
                    }
                } else {
                    if (file_exists(DIR_MODIFICATION . 'catalog/' . substr($file, strlen(DIR_APPLICATION)))) {
                        $file = DIR_MODIFICATION . 'catalog/' . substr($file, strlen(DIR_APPLICATION));
                    }
                }
            } else if ($this->startsWith($file, DIR_SYSTEM)) {
                if (file_exists(DIR_MODIFICATION . 'system/' . substr($file, strlen(DIR_SYSTEM)))) {
                    $file = DIR_MODIFICATION . 'system/' . substr($file, strlen(DIR_SYSTEM));
                }
            }
        }

        // Don't use VQmod 2.3.2 or earlier if available
        if (array_key_exists('vqmod', get_defined_vars())) {
            trigger_error("You are using an old VQMod version '2.3.2' or earlier, please upgrade your VQMod!");
            exit;
        }

        // Use modification through VQmod 2.4.0 or later if available
        if (class_exists('VQMod', false)) {
            if (VQMod::$directorySeparator) {
                if (strpos($file, 'vq2-') !== FALSE) {
                    return $file;
                }
                if (version_compare(VQMod::$_vqversion, '2.5.0', '<')) {
                    trigger_error("You are using an old VQMod version '" . VQMod::$_vqversion . "', please upgrade your VQMod!");
                    exit;
                }
                if ($original_file != $file) {
                    return VQMod::modCheck($file, $original_file);
                }
                return VQMod::modCheck($original_file);
            }
        }

        // no VQmod
        return $file;
    }


    protected function startsWith($haystack, $needle)
    {
        if (strlen($haystack) < strlen($needle)) {
            return false;
        }
        return (substr($haystack, 0, strlen($needle)) == $needle);
    }
}