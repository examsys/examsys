<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Render package
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2015 onwards The University of Nottingham
 */

/**
 * Render helper class.
 * Interfaces with /vender/twig.
 */
class render
{
    /**
     * Twig object
     * @var twig
     */
    protected $twig;

    /**
     * Config object
     * @var config
     */
    protected $config;

    /**
     * Constructor
     * @param object $configObject - ExamSys configuration object
     * @param string|array $templatedir - path to templates or list of paths to search for template
     * @return void
     */
    public function __construct($configObject, $templatedir = null)
    {
        if (is_null($templatedir)) {
            $loader = new \Twig\Loader\FilesystemLoader(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'templates');
        } else {
            $loader = new \Twig\Loader\FilesystemLoader($templatedir);
        }
        $this->twig = new \Twig\Environment($loader, array(
            'cache' => false
        ));
        $this->config = $configObject;
    }

    /**
     * Render an arbitrary template file.
     *
     * @param array $data Data for the template
     * @param array $lang Language strings
     * @param string $template The template filename
     * @param string $additionaljs additional javascript required
     * @param string $additionalcss additional css required
     * @param string $path ExamSys root path
     * @param string $charset ExamSys display charset
     * @param string $language ExamSys display language
     */
    public function render(
        $data,
        $lang,
        $template,
        $additionaljs = '',
        $additionalcss = '',
        $path = null,
        $charset = null,
        $language = null
    ) {
        if (isset($lang['title'])) {
            $lang['title'] = page::title($lang['title']);
        }
        if (is_null($path)) {
            $path = $this->config->get('cfg_root_path');
        }
        if (is_null($charset)) {
            $charset = $this->config->get('cfg_page_charset');
        }
        if (is_null($language)) {
            $language = LangUtils::getLang($this->config->get('cfg_web_root'));
        }
        $data = array(
            'data' => $data,
            'lang' => $lang,
            'path' => $path,
            'charset' => $charset,
            'additionaljs' => $additionaljs,
            'additionalcss' => $additionalcss,
            'language' => $language,
        );
        echo $this->twig->render($template, $data);
    }

    /**
     * Render xml reponse.
     * @param string $template - template location
     * @param string $reponsename - response name
     * @param array $response - response data
     * @return $string xml response
     */
    public function render_xml($template, $reponsename, $response)
    {
        $data = array('name' => $reponsename, 'response' => $response);
        return $this->twig->render($template, $data);
    }

    /**
     * Render admin list page.
     * @param array $data - data to list
     * @param array $header - headers for data
     * @return void
     */
    public function render_admin_list($data, $header)
    {
        $data = array('data' => $data, 'header' => $header, 'path' => $this->config->get('cfg_root_path'));
        echo $this->twig->render('admin/list.html', $data);
    }

    /**
     * Render admin page header.
     * @param array $lang translations used in header
     * @param string $additionaljs additional javascript required
     * @param string $additionalcss additional css required
     * @return void
     */
    public function render_admin_header($lang, $additionaljs, $additionalcss)
    {
        $lang['title'] = page::title($lang['title']);
        $data = array(
            'lang' => $lang, 'additionaljs' => $additionaljs,
            'additionalcss' => $additionalcss,
            'charset' => $this->config->get('cfg_page_charset'),
            'path' => $this->config->get('cfg_root_path'),
            'language' => LangUtils::getLang($this->config->get('cfg_web_root')),
        );
        echo $this->twig->render('admin/header.html', $data);
    }

    /**
     * Render admin page content.
     * @param array $breadcrumb breadcrumb navigation
     * @param array $lang translations used in header
     * @param string $template - the content html template
     * @param array $searchdata - search data
     * @return void
     */
    public function render_admin_content($breadcrumb, $lang, $template = 'admin/content.html', $searchdata = array())
    {
        if (empty($searchdata)) {
            $data = array(
                'breadcrumb' => $breadcrumb,
                'lang' => $lang,
                'path' => $this->config->get('cfg_root_path'),
            );
        } else {
            $data = array(
                'breadcrumb' => $breadcrumb,
                'lang' => $lang,
                'path' => $this->config->get('cfg_root_path'),
                'from' => $searchdata['from'],
                'to' => $searchdata['to'],
                'total' => $searchdata['total'],
                'pageinfo' => $searchdata['pageinfo'],
            );
        }
        echo $this->twig->render($template, $data);
    }

    /**
     * Render admin page footer.
     * @param array $javascript additional javascript required
     * @return void
     */
    public function render_admin_footer($javascript = [])
    {
        $data = ['scripts' => $javascript, 'path' => $this->config->get('cfg_root_path')];
        echo $this->twig->render('admin/footer.html', $data);
    }

    /**
     * Render admin options pane.
     * @param string $script - action script to add to page
     * @param string $image - icon file to display
     * @param array $lang - array of language strings
     * @param string $toprightmenu menu link
     * @param string $template - options template to use
     * @return void
     */
    public function render_admin_options($script, $image, $lang, $toprightmenu, $template = 'admin/options.html')
    {
        $data = array('script' => $script, 'image' => $image, 'lang' => $lang, 'toprightmenu' => $toprightmenu);
        echo $this->twig->render($template, $data);
    }

    /**
     * Render admin update ExamSys pane.
     * @param array $plugins - array of plugins available
     * @param array $header - headers for data
     * @param string $action - the form action
     * @param array $lang - array of language strings
     * @return void
     */
    public function render_admin_update($plugins, $header, $action, $lang)
    {
        $data = array ('plugins' => $plugins, 'header' => $header, 'path' => $this->config->get('cfg_root_path'),
            'action' => $action, 'lang' => $lang);
        echo $this->twig->render('admin/update.html', $data);
    }

    /**
     * Render page breadcrumb from an associative array which keys must be the relative
     * path for a page and the values are the title to display.
     *
     * @param array $links
     * @return string
     */
    public function render_admin_navigation(array $links)
    {
        $path = $this->config->get('cfg_root_path');
        $current = count($links) > 0 ? array_pop($links) : '';

        $data = array (
            'path' => $path,
            'links' => $links,
            'current' => $current,
        );

        return $this->twig->render('admin/navigation.html', $data);
    }
}
